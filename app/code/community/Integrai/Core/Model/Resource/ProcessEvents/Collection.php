<?php

class Integrai_Core_Model_Resource_ProcessEvents_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract
{
    public function _construct()
    {
        $this->_init('integrai/processEvents');
    }
}