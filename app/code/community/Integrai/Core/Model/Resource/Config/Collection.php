<?php

class Integrai_Core_Model_Resource_Config_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('integrai/config');
    }
}