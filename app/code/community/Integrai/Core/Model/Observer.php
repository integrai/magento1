<?php

class Integrai_Core_Model_Observer
{
    /*
     * Tipos de Evento
     * */

    const SAVE_CUSTOMER = 'SAVE_CUSTOMER';
    const CUSTOMER_BIRTHDAY = 'CUSTOMER_BIRTHDAY';
    const NEWSLETTER_SUBSCRIBER = 'NEWSLETTER_SUBSCRIBER';
    const ADD_PRODUCT_CART = 'ADD_PRODUCT_CART';
    const ABANDONED_CART = 'ABANDONED_CART';
    const ABANDONED_CART_ITEM = 'ABANDONED_CART_ITEM';
    const NEW_ORDER = 'NEW_ORDER';
    const NEW_ORDER_ITEM = 'NEW_ORDER_ITEM';
    const SAVE_ORDER = 'SAVE_ORDER';
    const CANCEL_ORDER = 'CANCEL_ORDER';
    const CREATE_PRODUCT = 'CREATE_PRODUCT';
    const UPDATE_PRODUCT = 'UPDATE_PRODUCT';
    const DELETE_PRODUCT = 'DELETE_PRODUCT';

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    protected function _getApi()
    {
        return Mage::getModel('integrai/api');
    }

    public function customerRegisterSuccess(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::SAVE_CUSTOMER)) {
            /* @var Mage_Customer_Model_Customer $customer */
            $customer = $observer->getCustomer();
            $document = preg_replace('/\D/', '', $customer['taxvat']);
            $customer['document_type'] = strlen($document) > 11 ? 'cnpj' : 'cpf';

            return $this->_getApi()->sendEvent(self::SAVE_CUSTOMER, $customer->getData());
        }
    }

    public function newsletterSubscriberSaveAfter(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::NEWSLETTER_SUBSCRIBER)) {
            /* @var Mage_Newsletter_Model_Subscriber $subscriber */
            $subscriber = $observer->getEvent()->getSubscriber();
            if ($subscriber->getIsStatusChanged()) {
                $newsletter = new Varien_Object();
                $newsletter->setData($subscriber->getData());

                $customer_id = $subscriber->getCustomerId();
                if ($customer_id) {
                    /* @var Mage_Customer_Model_Customer $customer */
                    $customer = Mage::getModel('customer/customer')->load($customer_id);
                    $newsletter->setData('subscriber_name', $customer->getName());
                }
                return $this->_getApi()->sendEvent(self::NEWSLETTER_SUBSCRIBER, $newsletter->getData());
            }
        }
    }

    public function checkoutCartProductAddAfter(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::ADD_PRODUCT_CART) && $this->_getHelper()->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            $quoteItem = $observer->getQuoteItem();

            $data = new Varien_Object();
            $data->setCustomer($customer->getData());

            $item = new Varien_Object();
            $item->setData($quoteItem->getData());

            $product = Mage::getModel('catalog/product')
                ->loadByAttribute('sku', $quoteItem->getData('sku'));
            $item->setPrice(floatval($product->getPrice()));
            $data->setItem($item->getData());

            return $this->_getApi()->sendEvent(self::ADD_PRODUCT_CART, $data->getData());
        }
    }

    public function salesOrderPlaceAfter(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::NEW_ORDER)) {
            /* @var Mage_Sales_Model_Order $order */
            $order = $observer->getOrder();
            $customer = $this->getCustomerInfo($order);

            $document = preg_replace('/\D/', '', $customer['taxvat']);
            $customer['document_type'] = strlen($document) > 11 ? 'cnpj' : 'cpf';

            $billing = array();
            if ($order->getBillingAddress()) {
                $billing = $this->getAddress($billing, $order->getBillingAddress());
            }

            $shipping = array();
            if ($order->getShippingAddress()) {
                $shipping = $this->getAddress($shipping, $order->getShippingAddress());
            }

            $items = array();
            foreach ($order->getAllVisibleItems() as $item) {
              $categoryIds = $item->getProduct()->getCategoryIds();
              $categories = array();

              foreach ($categoryIds as $categoryId) {
                $category = Mage::getModel('catalog/category')->load($categoryId);
                $categories += array($category->getData());
              }

              $item['categories'] = $categories;
              $items[] = $item->getData();
            }

            $order->setTotalItems(count($items));

            $data = new Varien_Object();
            $data->setOrder($order->getData());
            $data->setCustomer($customer);
            $data->setBillingAddress($billing);
            $data->setShippingAddress($shipping);
            $data->setPayment($order->getPayment()->getData());
            $data->setItems($items);
            $data->setShippingMethod($order->getShippingMethod());
            $data->setShippingMethodDetail($order->getShippingMethod(true));

            $this->_getApi()->sendEvent(self::NEW_ORDER, $data->getData());

            if ($this->_getHelper()->isEventEnabled(self::NEW_ORDER_ITEM)) {
                foreach ($items as $item) {
                    $item['order_id'] = $order->getIncrementId();
                    $item['customer'] = $customer;

                    $this->_getApi()->sendEvent(self::NEW_ORDER_ITEM, $item);
                }
            }
        }
    }

    public function salesOrderAfterSave(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::SAVE_ORDER)) {
            /* @var Mage_Sales_Model_Order $order */
            $order = $observer->getOrder();
            $customer = $this->getCustomerInfo($order);

            $document = preg_replace('/\D/', '', $customer['taxvat']);
            $customer['document_type'] = strlen($document) > 11 ? 'cnpj' : 'cpf';

            $billing = array();
            if ($order->getBillingAddress()) {
                $billing = $this->getAddress($billing, $order->getBillingAddress());
            }

            $shipping = array();
            if ($order->getShippingAddress()) {
                $shipping = $this->getAddress($shipping, $order->getShippingAddress());
            }

            $items = array();
            foreach ($order->getAllVisibleItems() as $item) {
                $items[] = $item->getData();
            }

            $data = new Varien_Object();
            $data->setOrder($order->getData());
            $data->setCustomer($customer);
            $data->setBillingAddress($billing);
            $data->setShippingAddress($shipping);
            $data->setPayment($order->getPayment()->getData());
            $data->setItems($items);
            $data->setShippingMethod($order->getShippingMethod());
            $data->setShippingMethodDetail($order->getShippingMethod(true));

            $this->_getHelper()->log("SAVE_ORDER ID", $observer->getOrder()->getEntityId());

            return $this->_getApi()->sendEvent(self::SAVE_ORDER, $data->getData());
        }
    }

    public function orderCancelAfter(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::CANCEL_ORDER)) {
            /* @var Mage_Sales_Model_Order $order */
            $order = $observer->getOrder();
            return $this->_getApi()->sendEvent(self::CANCEL_ORDER, $order->getData());
        }
    }

    public function abandonedCart() {
        if ($this->_getHelper()->isEventEnabled(self::ABANDONED_CART)) {
            $minutes = $this->_getHelper()->getGlobalConfig('minutesAbandonedCartLifetime', 60);
            $fromDate = date('Y-m-d H:i:s', strtotime('-'.$minutes. ' minutes'));
            $toDate = date('Y-m-d H:i:s', strtotime("now"));

            /* @var Mage_Sales_Model_Quote $quotes */
            $quotes = Mage::getModel('sales/quote')
                ->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('items_count', array('gt' => 0))
                ->addFieldToFilter('customer_email', array('notnull' => true))
                ->addFieldToFilter('updated_at', ['lteq' => $toDate])
                ->addFieldToFilter('updated_at', ['gteq' => $fromDate])
                ->load();

            if ($quotes->count() > 0) {
                foreach ($quotes as $quote) {
                    $customer = $quote->getCustomer();

                    $data = new Varien_Object();
                    $data->setCartId($quote->getId());
                    $data->setQuote($quote->getData());
                    $items = array();
                    foreach ($quote->getAllVisibleItems() as $item) {
                        $newItem = new Varien_Object();
                        $newItem->addData($item->getData());
                        $newItem->setCartId($quote->getId());
                        $newItem->setProduct($item->getProduct()->getData());

                        $items[] = $newItem->getData();
                    }
                    $data->setItems($items);
                    $data->setQuantity(count($items));
                    $data->setCustomer($customer->getData());
                    $data->setCreatedAt($quote->getData('created_at'));

                    $this->_getApi()->sendEvent(self::ABANDONED_CART, $data->getData());

                    if ($this->_getHelper()->isEventEnabled(self::ABANDONED_CART_ITEM)) {
                        foreach ($items as $item) {
                            $item['customer'] = $customer->getData();

                            $this->_getApi()->sendEvent(self::ABANDONED_CART_ITEM, $item->getData());
                        }
                    }
                }
            }
        }
    }

    public function customerBirthday() {
        if ($this->_getHelper()->isEventEnabled(self::CUSTOMER_BIRTHDAY)) {
            $customers = Mage::getModel("customer/customer")
                ->getCollection()
                ->addNameToSelect()
                ->addFieldToFilter('dob', array('like' => '%'.date("m").'-'.date("d").' 00:00:00'));

            return $this->_getApi()->sendEvent(self::CUSTOMER_BIRTHDAY, $customers->getData());
        }
    }

    protected function getAddress($oldAddress, $orderAddress) {
        $address = $orderAddress->getData();
        $address['street_1'] = $orderAddress->getStreet1();
        $address['street_2'] = $orderAddress->getStreet2();
        $address['street_3'] = $orderAddress->getStreet3();
        $address['street_4'] = $orderAddress->getStreet4();
        $address['region_code'] = $orderAddress->getRegionCode();
        return array_merge($oldAddress, $address);
    }

    protected function getCustomerInfo($order) {
        $customer = new Varien_Object();

        if ($order->getCustomerId()) {
            $customer->setData(Mage::getModel('customer/customer')->load($order->getCustomerId())->getData());
        } else {
            $checkout = Mage::getSingleton('checkout/session');
            $quote = $checkout->getQuote();
            $billing = $order->getBillingAddress()->getData();

            $this->_getHelper()->log('billing', $billing);

            $customer->setEntityId($quote->getCustomerId());
            $customer->setGroupId($quote->getCustomerGroupId());
            $customer->setFirstname($quote->getCustomerFirstname() ? $quote->getCustomerFirstname() : $billing['firstname']);
            $customer->setLastname($quote->getCustomerLastname() ? $quote->getCustomerLastname() : $billing['lastname']);
            $customer->setTaxvat($quote->getCustomerTaxvat() ? $quote->getCustomerTaxvat() : $billing['vat_id']);
            $customer->setEmail($quote->getCustomerEmail());
            $customer->setDob($quote->getCustomerDob());
            $customer->setGender($quote->getCustomerGender());
            $customer->setCreatedAt(date(DATE_ATOM));
        }

        return $customer->getData();
    }

    public function getSalesOrderViewInfo(Varien_Event_Observer $observer) {
        $block = $observer->getBlock();
        if (($block->getNameInLayout() == 'order_info') && ($child = $block->getChild('integrai.order.info.custom.block')) || ($child = $block->getChild('integrai.order.info.custom.payment'))) {
            $transport = $observer->getTransport();
            if ($transport) {
                $html = $transport->getHtml();
                $html .= $child->toHtml();
                $transport->setHtml($html);
            }
        }
    }

    public function createOrEditProduct(Varien_Event_Observer $observer) {
        $productEvent = $observer->getEvent()->getProduct();
        $event = $productEvent->isObjectNew() ? self::CREATE_PRODUCT : self::UPDATE_PRODUCT;
        $product = Mage::getModel('catalog/product')->load($productEvent->getId());

        if ($this->_getHelper()->isEventEnabled($event)) {
            $data = $this->enrichProductAttributes($product);
            $data['photos'] = $this->getProductPhotos($product);
            $data['categories'] = $this->getProductCategories($product);
            $data['stock_data'] = $product->getStockItem()->getData();

            if ($product->getTypeId() == 'configurable') {
                $variations = $childProducts = Mage::getModel('catalog/product_type_configurable')
                    ->getUsedProducts(null, $product);;

                $data['variations'] = array();
                foreach ($variations as $variation) {
                    $productVariation = Mage::getModel('catalog/product')->load($variation->getId());
                    $variationData = $this->enrichProductAttributes($productVariation);
                    $variationData['photos'] = $this->getProductPhotos($productVariation);
                    $variationData['categories'] = $this->getProductCategories($productVariation);
                    $variationData['stock_data'] = $productVariation->getStockItem()->getData();
                    array_push($data['variations'], $variationData);
                }
            }

            $this->_getHelper()->log("product", $data);

            return $this->_getApi()->sendEvent(self::CREATE_PRODUCT, $data);
        }
    }

    public function deleteProduct(Varien_Event_Observer $observer) {
        $productEvent = $observer->getEvent()->getProduct();

        if ($this->_getHelper()->isEventEnabled(self::DELETE_PRODUCT)) {
            return $this->_getApi()->sendEvent(self::DELETE_PRODUCT, $productEvent->getData());
        }
    }

    private function getProductPhotos($product) {
        $photos = array();
        $media_gallery = $product->getData('media_gallery');

        foreach ($product->getMediaGalleryImages() as $image) {
            array_push($photos, $image->getUrl());
        }

        return $photos;
    }

    private function enrichProductAttributes($product) {
        $data = $product->getData();
        $attributes = Mage::getResourceModel('catalog/product_attribute_collection');
        $attributes->addFieldToFilter('is_user_defined',1);
        $attributes->addFieldToFilter('entity_type_id', 4);
        $attributes->addFieldToFilter('frontend_input', array("select", "multiselect"));
        foreach($attributes as $attributeInfo)
        {
            if (isset($data[$attributeInfo->getAttributeCode()])) {
                $data[$attributeInfo->getAttributeCode()] = $product->getAttributeText($attributeInfo->getAttributeCode());
            }
        }
        return $data;
    }

    private function getProductCategories($product) {
        $categoriesList = array();

        $categoryIds = $product->getCategoryIds();

        if (is_array($categoryIds) && count($categoryIds) > 0) {
            $categories = Mage::getModel('catalog/category')
                ->getCollection()
                ->addAttributeToSelect('name')
                ->addAttributeToFilter('entity_id', array('in'=>$categoryIds));

            foreach ($categories as $category) {
                array_push($categoriesList, array(
                    "id" => $category->getId(),
                    "label" => $category->getName()
                ));
            }
        }

        return $categoriesList;
    }
}
