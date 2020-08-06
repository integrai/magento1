<?php

class Integrai_Core_Helper_Data  {
    public function log($message, $array = null, $level = Zend_Log::DEBUG, $file = "integrai.log")
    {
        if (!is_null($array)) {
            $message .= " - " . json_encode($array);
        }

        Mage::log($message, $level, $file, true);
    }

    public function getConfig($name, $group = 'general') {
        return Mage::getStoreConfig("integrai_core/{$group}/{$name}");
    }

    public function getCarrierConfig($name) {
        return Mage::getStoreConfig("carriers/integrai_shipping/{$name}");
    }

    public function getConfigTable($name, $configName = null, $defaultValue = null) {
        $config = Mage::getModel('integrai/config')->load($name, 'name');
        $values = json_decode($config->getData('values'), true);

        if ($configName) {
            return $values[$configName] ?: $defaultValue;
        }

        return $values;
    }

    public function isEnabled() {
        return $this->getConfig('enable');
    }

    public function isEventEnabled($eventName) {
        $events =  $this->getConfigTable('EVENTS_ENABLED');
        return $this->isEnabled() && in_array($eventName, $events);
    }

    public function getGlobalConfig($configName, $defaultValue = null) {
        return $this->getConfigTable('GLOBAL', $configName, $defaultValue);
    }

    public function isLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
}
