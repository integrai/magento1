<?php

class Integrai_Core_Helper_Data extends Mage_Core_Helper_Abstract  {
    public function log($message, $array = null, $level = Zend_Log::DEBUG, $file = "integrai.log")
    {
        if (!is_null($array)) {
            $message .= " - " . json_encode($array, JSON_PRETTY_PRINT);
        }

        Mage::log($message, $level, $file, true);
    }

    public function getConfig($name, $group = 'general') {
        return Mage::getStoreConfig("integrai_core/{$group}/{$name}");
    }

    public function getCarrierConfig($name) {
        return Mage::getStoreConfig("carriers/integrai_shipping/{$name}");
    }

    public function getConfigTable($name, $configName = null, $defaultValue = null, $parseJson = true) {
        $config = Mage::getModel('integrai/config')->load($name, 'name');

        if ($parseJson) {
            $values = json_decode($config->getData('values'), true);
        } else {
            $values = $config->getData('values');
        }

        if ($configName) {
            return $values[$configName] ?: $defaultValue;
        }

        return $values;
    }

    public function updateConfig($name, $value) {
        $config = Mage::getModel('integrai/config')->load($name, 'name');

        $config->setName($name)
            ->setValues($value)
            ->setUpdatedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
            ->save();
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

    public function pageSuccessTemplate() {
        $lastOrder = Mage::getModel('sales/order')
            ->loadByIncrementId(Mage::getSingleton('checkout/session')->getLastRealOrderId());
        $paymentMethod = $lastOrder->getPayment()->getMethod();

        return stripos($paymentMethod, 'integrai') === false ? 'checkout/success.phtml' : 'integrai/checkout/success.phtml';
    }

    public function checkAuthorization($hash) {
        $apiKey = $this->getConfig('api_key');
        $secretKey = $this->getConfig('secret_key');
        $token = base64_encode("{$apiKey}:{$secretKey}");
        return $token === str_replace('Basic ', '', $hash);
    }
}
