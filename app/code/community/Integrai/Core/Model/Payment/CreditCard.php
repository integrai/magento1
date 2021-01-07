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

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function isAvailable($quote = null)
    {
        return true;
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