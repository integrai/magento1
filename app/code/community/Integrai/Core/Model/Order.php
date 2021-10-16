<?php

class Integrai_Core_Model_Order {
    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function create($orderData, $customerId) {
        $store = Mage::app()->getDefaultStoreView();

        $quote = Mage::getModel('sales/quote');
        $quote->setStoreId($store->getStoreId());
        $quote->setCurrency($store->getBaseCurrencyCode());

        $customer = Mage::getModel('customer/customer')->load($customerId);
        $quote->assignCustomer($customer);

        foreach($orderData['items'] as $item) {
            $product = Mage::getModel('catalog/product')->load(Mage::getModel('catalog/product')->loadByAttribute('sku', $item['sku'])->getId());
            $product->setPrice($item['price']);
            $quote->addProduct(
                $product,
                intval($item['qty'])
            );
        }

        $quote->getBillingAddress()->addData(array(
            'firstname' => $orderData['billing_address']['firstname'],
            'lastname' => $orderData['billing_address']['lastname'],
            'street' => array(
                $orderData['billing_address']['address_street'],
                $orderData['billing_address']['address_number'],
                $orderData['billing_address']['address_complement'],
                $orderData['billing_address']['address_neighborhood']
            ),
            'city' => $orderData['billing_address']['address_city'],
            'country_id' => 'BR',
            'region' => $orderData['billing_address']['address_state_code'],
            'postcode' => $orderData['billing_address']['address_zipcode'],
            'telephone' => $orderData['billing_address']['telephone'],
            'save_in_address_book' => 1
        ));

        $quote->getShippingAddress()->addData(array(
            'firstname' => $orderData['shipping_address']['firstname'],
            'lastname' => $orderData['shipping_address']['lastname'],
            'street' => array(
                $orderData['shipping_address']['address_street'],
                $orderData['shipping_address']['address_number'],
                $orderData['shipping_address']['address_complement'],
                $orderData['shipping_address']['address_neighborhood']
            ),
            'city' => $orderData['shipping_address']['address_city'],
            'country_id' => 'BR',
            'region' => $orderData['shipping_address']['address_state_code'],
            'postcode' => $orderData['shipping_address']['address_zipcode'],
            'telephone' => $orderData['shipping_address']['telephone'],
            'save_in_address_book' => 1
        ));

        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)->collectShippingRates()->setShippingMethod('flatrate_flatrate');

        $quote->setPaymentMethod('integrai_marketplace');
        $quote->setInventoryProcessed(false);
        $quote->setTotalsCollectedFlag(false)->collectTotals();

        $quote->save();

        $quote->getPayment()->importData(['method' => 'integrai_marketplace']);

        $quote->collectTotals()->save();

        $service = Mage::getModel('sales/service_quote', $quote);
        $service->submitAll();
        $order = $service->getOrder();

        $order->setEmailSent(0);;

        $shippingDescription = $orderData['order']['shipping_carrier'] . ' - ' . $orderData['order']['shipping_method'];
        $shippingPrice = $orderData['order']['shipping_amount'];

        $order->setExtOrderId($orderData['order']['id']);
        $order->setShippingAmount($shippingPrice);
        $order->setBaseShippingAmount($shippingPrice);
        $order->setShippingDescription($shippingDescription);

        $order->setGrandTotal($order->getSubtotal() + $shippingPrice);
        $order->setBaseGrandTotal($order->getSubtotal() + $shippingPrice);
        $order->getPayment()->setAdditionalInformation(array(
            "payment_response" => array(
                "module_name" => $orderData['order']['marketplace'],
                "marketplace_id" => $orderData['order']['id'],
            )
        ));
        $order->save();

        return $order->getData();
    }
}