<?php

class Integrai_Core_Model_Payment_Pix extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'integrai_pix';
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_formBlockType = 'integrai/payment_pix';

    const NEW_ORDER = 'NEW_ORDER';

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function isAvailable($quote = null)
    {
        $formOptions = $this->_getHelper()->getConfigTable('PAYMENT_PIX', 'formOptions', array());
        $gateways = isset($formOptions) && is_array($formOptions) ? $formOptions['gateways'] : array();
        return $this->_getHelper()->isEventEnabled(self::NEW_ORDER) && count($gateways) > 0;
    }

    public function getPaymentPixConfig()
    {
        return json_encode($this->_getHelper()->getConfigTable('PAYMENT_PIX'));
    }

    public function getCustomer()
    {
        /** @var Mage_Checkout_Model_Session $checkout */
        $checkout = Mage::getSingleton('checkout/session');
        $quote = $checkout->getQuote();
        $customer = $quote->getCustomer();

        if (!empty($customer->getId())) {
            $billing = $customer->getDefaultBillingAddress();

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

        $billing = $quote->getBillingAddress();

        return array(
            "name" => $quote->getCustomerFirstname(),
            "lastName" => $quote->getCustomerLastname(),
            "docNumber" => $quote->getCustomerTaxvat(),
            "addressStreet" => $billing->getStreet1(),
            "addressNumber" => $billing->getStreet2(),
            "addressCity" => $billing->getCity(),
            "addressState" => $billing->getRegionCode(),
            "addressZipCode" => $billing->getPostcode(),
        );
    }
}