<?php

class Integrai_Core_PixController
    extends Mage_Core_Controller_Front_Action
{
    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function indexAction()
    {
        try{
            $order_id = $this->getRequest()->getParam('order_id');

            $this->_getHelper()->log('Buscando pix url do pedido: ', $order_id);

            $api = Mage::getModel('integrai/api');
            $response = $api->request('/store/pix', 'GET', null, array(
                'orderId' => $order_id,
            ));

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
        } catch (Exception $e) {
            $this->_getHelper()->log('Error ao buscar pix', $e->getMessage());
            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setHttpResponseCode(400)->setBody(Mage::helper('core')->jsonEncode(array(
                "qrCode" => null,
                "qrCodeBase64" => null,
                "error" => $e->getMessage()
            )));
        }
    }
}