<?php

class Integrai_Core_Model_Observer
{
    /*
     * Tipos de Evento
     * */

    const NEW_CUSTOMER = 'NEW_CUSTOMER';
    const NEWSLETTER_SUBSCRIBER = 'NEWSLETTER_SUBSCRIBER';
    const ADD_PRODUCT_CART = 'ADD_PRODUCT_CART';
    const NEW_ORDER = 'NEW_ORDER';
    const SAVE_ORDER = 'SAVE_ORDER';
    const CANCEL_ORDER = 'CANCEL_ORDER';
    const REFUND_INVOICE = 'REFUND_INVOICE';
    const FINALIZE_CHECKOUT = 'FINALIZE_CHECKOUT';

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
        if ($this->_getHelper()->isEventEnabled(self::NEW_CUSTOMER)) {
            /* @var Mage_Customer_Model_Customer $customer */
            $customer = $observer->getCustomer();
            return $this->_getApi()->sendEvent(self::NEW_CUSTOMER, $customer->getData());
        }
    }

    public function newsletterSubscriberSaveAfter(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::NEWSLETTER_SUBSCRIBER)) {
            /* @var Mage_Newsletter_Model_Subscriber $newsletter */
            $newsletter = $observer->getEvent()->getSubscriber();
            return $this->_getApi()->sendEvent(self::NEWSLETTER_SUBSCRIBER, $newsletter->getData());
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

    public function creditMemoRefundAfterSave(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::REFUND_INVOICE)) {
            $this->_getHelper()->log('creditMemoRefundAfterSave', $observer->getEvent()->getResut());
            return $this->_getApi()->sendEvent(self::REFUND_INVOICE, $observer->getEvent()->getResut());
        }
    }

    public function checkoutSubmitAllAfter(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::FINALIZE_CHECKOUT)) {
            /* @var Mage_Sales_Model_Order $order */
            $order = $observer->getOrder();
            return $this->_getApi()->sendEvent(self::FINALIZE_CHECKOUT, $order->getData());
        }
    }

//    public function paymentMethodIsActive(Varien_Event_Observer $observer)
//    {
//        if ($this->_getHelper()->isEventEnabled(self::FINALIZE_CHECKOUT)) {
//            $this->_getHelper()->log('paymentMethodIsActive', $observer->getEvent()->getName());
//        }
//    }
}
