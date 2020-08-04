<?php

class Integrai_Core_Model_Resource_Events extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('integrai/events', 'id');
    }
}