<?php

class Integrai_Core_Helper_Data  {
    public function log($message, $array = null, $file = "integrai.log")
    {
        if (!is_null($array)) {
            $message .= " - " . json_encode($array);
        }

        Mage::log($message, null, $file, true);
    }

    public function getConfig($name, $group = 'general') {
        return Mage::getStoreConfigFlag('integrai_core/{$group}/{$name}');
    }

    public function isEnabled() {
        return $this->getConfig('enable');
    }

    public function isEventEnabled($eventName) {
        $config = Mage::getModel('integrai/config')->load('EVENTS_ENABLED', 'name');
        $events = json_decode($config->getData('values'), true);
        return $this->isEnabled() && in_array($eventName, $events);
    }

    public function getGlobalConfig($configName, $defaultValue = null) {
        $configs = Mage::getModel('integrai/config')->load('GLOBAL', 'name');
        $config = json_decode($configs->getData('values'), true);
        return $config[$configName] ? $config[$configName] : $defaultValue;
    }

    public function isLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
}
