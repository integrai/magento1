<?php

class Integrai_Core_BoletoController
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
            $order_id = trim($_GET['order_id']);
            $is_duplicate = (bool)$_GET['is_duplicate'];

            $this->_getHelper()->log('Buscando boleto url do pedido: ', $order_id);

            $api = Mage::getModel('integrai/api');
            $response_boleto = $api->request('/store/boleto', 'GET', null, array(
                'order_id' => $order_id,
                'is_duplicate' => $is_duplicate
            ));

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                'boleto_url' => $response_boleto['boleto_url']
            )));
        } catch (Exception $e) {
            $this->_getHelper()->log('Error ao buscar boleto', $e->getMessage());
            $this->getResponse()->setHttpResponseCode(400)->setBody(Mage::helper('core')->jsonEncode(array(
                "ok" => false,
                "error" => $e->getMessage()
            )));
            $this->_redirect("/");
        }

//        } else {
//            $this->_redirect("/");
//        }
    }
}