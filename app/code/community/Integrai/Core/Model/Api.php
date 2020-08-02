<?php

class Integrai_Core_Model_Api {

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function sendEvent($eventName, $payload) {
        $this->_getHelper()->log($eventName, $payload);

        $this->_backupEvent($eventName, $payload);
    }

    private function _backupEvent($eventName, $payload) {
        $this->_getHelper()->log('Grava no banco para mandar depois, caso esteja fora');
    }
}