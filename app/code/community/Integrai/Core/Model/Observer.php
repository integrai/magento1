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
            $this->_getHelper()->log('salesOrderPlaceAfter', $observer->getEvent()->getResut());
            return $this->_getApi()->sendEvent(self::NEW_ORDER, $observer->getEvent()->getResut());
        }
    }

    public function salesOrderBeforeSave(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::SAVE_ORDER)) {
            $this->_getHelper()->log('salesOrderBeforeSave', $observer->getEvent()->getResut());
            return $this->_getApi()->sendEvent(self::SAVE_ORDER, $observer->getEvent()->getResut());
        }
    }

    public function orderCancelAfter(Varien_Event_Observer $observer)
    {
        if ($this->_getHelper()->isEventEnabled(self::CANCEL_ORDER)) {
            $this->_getHelper()->log('orderCancelAfter', $observer->getEvent()->getResut());
            return $this->_getApi()->sendEvent(self::CANCEL_ORDER, $observer->getEvent()->getResut());
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
            $this->_getHelper()->log('checkoutSubmitAllAfter', $observer->getEvent()->getResut());
            return $this->_getApi()->sendEvent(self::FINALIZE_CHECKOUT, $observer->getEvent()->getResut());
        }
    }

//    public function paymentMethodIsActive(Varien_Event_Observer $observer)
//    {
//        if ($this->_getHelper()->isEventEnabled(self::FINALIZE_CHECKOUT)) {
//            $this->_getHelper()->log('paymentMethodIsActive', $observer->getEvent()->getName());
//        }
//    }
}
