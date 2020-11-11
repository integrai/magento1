<?php

class Integrai_Core_Model_Payment_Boleto extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'integrai_boleto';
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;

    public function isAvailable($quote = null)
    {
        return true;
    }
}