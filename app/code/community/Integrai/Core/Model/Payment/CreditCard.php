<?php

class Integrai_Core_Model_Payment_CreditCard extends Mage_Payment_Model_Method_Abstract
{
    protected $_code                    = 'integrai_creditcard';
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = false;
    protected $_canRefund               = false;
    protected $_canUseCheckout          = true;
    protected $_canVoid                 = true;
    protected $_isGateway               = true;
    protected $_formBlockType = 'integrai/payment_creditCard';

    const NEW_ORDER = 'NEW_ORDER';

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function isAvailable($quote = null)
    {
        $formOptions = $this->_getHelper()->getConfigTable('PAYMENT_CREDITCARD', 'formOptions', array());
        $gateways = isset($formOptions) && is_array($formOptions) ? $formOptions['gateways'] : array();
        return $this->_getHelper()->isEventEnabled(self::NEW_ORDER) && count($gateways) > 0;
    }

    public function getPaymentCreditCardConfig()
    {
        return json_encode($this->_getHelper()->getConfigTable('PAYMENT_CREDITCARD'));
    }

    public function getAmount()
    {
        $checkout = Mage::getSingleton('checkout/session');
        $quote = $checkout->getQuote();
        return (float) $quote->getBaseGrandTotal();
    }
}