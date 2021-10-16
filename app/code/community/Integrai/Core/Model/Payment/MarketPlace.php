<?php

class Integrai_Core_Model_Payment_MarketPlace extends Mage_Payment_Model_Method_Abstract
{
    protected $_code = 'integrai_marketplace';
    protected $_isInitializeNeeded      = true;
    protected $_canUseInternal          = false;
    protected $_canUseForMultishipping  = false;

    public function isAvailable($quote = null)
    {
        return true;
    }
}