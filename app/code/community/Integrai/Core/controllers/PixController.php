<?php

class Integrai_Core_PixController
    extends Mage_Core_Controller_Front_Action
{
    const PIX = 'PIX';

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function indexAction()
    {
        try{
            $order_id = $this->getRequest()->getParam('order_id');

            if (!$order_id) {
                throw new Exception('Informe o ID do pedido');
            }

            $this->_getHelper()->log('Buscando pix url do pedido: ', $order_id);

            $api = Mage::getModel('integrai/api');
            $response = $api->sendEvent(self::PIX, array(
                'orderId' => $order_id,
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
        $this->_getHelper()->log('Error ao buscar pix', $e->getMessage());
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setHttpResponseCode(400)->setBody(Mage::helper('core')->jsonEncode(array(
            "qrCode" => null,
            "qrCodeBase64" => null,
            "error" => $e->getMessage()
        )));
    }
}