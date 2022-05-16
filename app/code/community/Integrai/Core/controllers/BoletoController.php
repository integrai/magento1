<?php

class Integrai_Core_BoletoController
    extends Mage_Core_Controller_Front_Action
{

    const BOLETO_URL = 'BOLETO_URL';

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function indexAction()
    {
        try{
            $order_id = $this->getRequest()->getParam('order_id');
            $is_duplicate = (bool)$this->getRequest()->getParam('is_duplicate');

            if (!$order_id) {
                throw new Exception('Informe o ID do pedido');
            }

            $this->_getHelper()->log('Buscando boleto url do pedido: ', $order_id);

            $api = Mage::getModel('integrai/api');
            $response = $api->sendEvent(self::BOLETO_URL, array(
                'orderId' => $order_id,
                'isDuplicate' => $is_duplicate
            ), false, true);

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        } catch (Throwable $e) {
            $this->error_handling($e);
        } catch (Exception $e) {
            $this->error_handling($e);
        }
    }

    private function error_handling($e) {
        $this->_getHelper()->log('Error ao buscar boleto', $e->getMessage());
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setHttpResponseCode(400)->setBody(Mage::helper('core')->jsonEncode(array(
            "boletoUrl" => null,
            "error" => $e->getMessage()
        )));
    }
}