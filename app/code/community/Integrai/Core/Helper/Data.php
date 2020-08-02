<?php

class Integrai_Core_Helper_Data  {
    public function log($message, $array = null, $file = "integrai.log")
    {
        if (!is_null($array)) {
            $message .= " - " . json_encode($array);
        }

        Mage::log($message, null, $file, true);
    }

    public function isEnabled() {
        return Mage::getStoreConfigFlag('integrai_core/general/enable');
    }

    public function isEventEnabled($eventName) {
        $config = Mage::getModel('integrai/config')
            ->load('EVENTS_ENABLED', 'name');
        $events = json_decode($config->getData('values'), true);
        return $this->isEnabled() && in_array($eventName, $events);
    }

    public function isLoggedIn() {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }
}
