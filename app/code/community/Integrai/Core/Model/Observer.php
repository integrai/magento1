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
    const NEW_ORDER = 'NEW_ORDER';
    const SAVE_ORDER = 'SAVE_ORDER';
    const CANCEL_ORDER = 'CANCEL_ORDER';

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
            return $this->_getApi()->sendEvent(self::SAVE_CUSTOMER, $customer->getData());
        }
    }

    public function newsletterSubscriberSaveAfter(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::NEWSLETTER_SUBSCRIBER)) {
            /* @var Mage_Newsletter_Model_Subscriber $newsletter */
            $subscriber = $observer->getEvent()->getSubscriber();
            if ($subscriber->getIsStatusChanged()) {
                return $this->_getApi()->sendEvent(self::NEWSLETTER_SUBSCRIBER, $subscriber->getData());
            }
        }
    }

    public function checkoutCartProductAddAfter(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::ADD_PRODUCT_CART) && $this->_getHelper()->isLoggedIn()) {
            $customer = Mage::getSingleton('customer/session')->getCustomer();

            $data = new Varien_Object();
            $data->setCustomer($customer->getData());
            $data->setItem($observer->getQuoteItem()->getData());

            return $this->_getApi()->sendEvent(self::ADD_PRODUCT_CART, $data->getData());
        }
    }

    public function salesOrderPlaceAfter(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::NEW_ORDER)) {
            /* @var Mage_Sales_Model_Order $order */
            $order = $observer->getOrder();

            $data = new Varien_Object();
            $data->setOrder($order->getData());
            $data->setCustomer($order->getCustomer()->getData());
            $data->setBillingAddress($order->getBillingAddress()->getData());
            $data->setShippingAddress($order->getShippingAddress()->getData());
            $data->setPayment($order->getPayment()->getData());
            $data->setShippingMethod($order->getShippingMethod());
            $data->setShippingMethodDetail($order->getShippingMethod(true));

            return $this->_getApi()->sendEvent(self::NEW_ORDER, $data->getData());
        }
    }

    public function salesOrderBeforeSave(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::SAVE_ORDER)) {
            /* @var Mage_Sales_Model_Order $order */
            $order = $observer->getOrder();
            return $this->_getApi()->sendEvent(self::SAVE_ORDER, $order->getData());
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
            $minutes = $this->_getHelper()->getGlobalConfig('minutes_abandoned_cart_lifetime', 60);
            $fromDate = date('Y-m-d H:i:s', strtotime('-'.$minutes. ' minutes'));
            $toDate = date('Y-m-d H:i:s', strtotime("now"));

            /* @var Mage_Sales_Model_Quote $quotes */
            $quotes = Mage::getModel('sales/quote')
                ->getCollection()
                ->addFieldToFilter('is_active', 1)
                ->addFieldToFilter('items_count', array('gt' => 0))
                ->addFieldToFilter('customer_email', array('notnull' => true))
                ->addFieldToFilter('created_at', array('from'=>$fromDate, 'to'=>$toDate))
                ->load();

            $abandonedCart = array();
            foreach ($quotes as $quote) {
                $data = new Varien_Object();
                $data->setQuote($quote->getData());
                $items = array_map(function($item) {
                    $newItem = new Varien_Object();
                    $newItem->addData($item->getData());
                    $newItem->setProduct($item->getProduct()->getData());
                    return $newItem->getData();
                }, $quote->getAllItems());
                $data->setItems($items);
                $data->setCustomer($quote->getCustomer()->getData());
                array_push($abandonedCart, $data->getData());
            }

            return $this->_getApi()->sendEvent(self::ABANDONED_CART, $abandonedCart);
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
}
