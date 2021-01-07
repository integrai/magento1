<?php

class Integrai_Core_Model_Payment_Boleto extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'integrai_boleto';
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_formBlockType = 'integrai/payment_boleto';

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function isAvailable($quote = null)
    {
        return true;
    }

    public function getPaymentBoletoConfig()
    {
        return json_encode($this->_getHelper()->getConfigTable('PAYMENT_BOLETO'));
    }

    public function getCustomer()
    {
        /** @var Mage_Checkout_Model_Session $checkout */
        $checkout = Mage::getSingleton('checkout/session');
        $quote = $checkout->getQuote();
        $customer = $quote->getCustomer();
        $billing = $customer->getDefaultBillingAddress();

        $this->_getHelper()->log("CUSTOMER", array(
            'customer' => $customer->getData(),
            'billing' => $billing->getData(),
        ));

        return array(
            "name" => $customer->getFirstname(),
            "lastName" => $customer->getLastname(),
            "companyName" => $customer->getFirstname(),
            "docNumber" => $customer->getData('taxvat'),
            "addressStreet" => $billing->getStreet1(),
            "addressNumber" => $billing->getStreet2(),
            "addressCity" => $billing->getCity(),
            "addressState" => $billing->getRegionCode(),
            "addressZipCode" => $billing->getPostcode(),
        );
    }
}