<?php

class Integrai_Core_ConfigController
    extends Mage_Core_Controller_Front_Action
{
    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function indexAction()
    {
        // check origin
//        if ($_SERVER['SERVER_NAME'] === $this->_getHelper()->getGlobalConfig('api_url')) {

        try{
            $this->_getHelper()->log('Buscando novas configurações...');
            $api = Mage::getModel('integrai/api');
            $response = $api->request('/config');
            $configs = json_decode($response, true);

            foreach ($configs as $config) {
                $configItem = Mage::getModel('integrai/config')
                    ->load($config['name'], 'name');
                if ($configItem->getId()) {
                    $configItem
                        ->setValues($config['values'])
                        ->setUpdatedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                        ->save();

                } else {
                    Mage::getModel('integrai/config')
                        ->setData($config)
                        ->setUpdatedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                        ->save();
                }
            }

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                "ok" => true,
            )));
        } catch (Exception $e) {
            $this->_getHelper()->log('Error ao atualizar configs', $e->getMessage());
            $this->_redirect("/");
        }

//        } else {
//            $this->_redirect("/");
//        }
    }
}