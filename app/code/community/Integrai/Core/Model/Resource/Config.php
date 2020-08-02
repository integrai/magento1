<?php

class Integrai_Core_Model_Resource_Config extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('integrai/config', 'id');
    }
}