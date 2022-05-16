<?php

class Integrai_Core_EventController
    extends Mage_Core_Controller_Front_Action
{
    protected $_models = array();

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function indexAction()
    {
        try{
            if (!$this->_getHelper()->checkAuthorization($this->getRequest()->getHeader('Authorization'))) {
                return $this->getResponse()->setHttpResponseCode(401)->setBody(Mage::helper('core')->jsonEncode(array(
                    "error" => "Unauthorized"
                )));
            }

            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

            $body = json_decode($this->getRequest()->getRawBody(), true);
            $eventId = $body['eventId'];
            $event = $body['event'];
            $payload = $body['payload'];
            $isSync = (bool)$body['isSync'];

            if ($isSync) {
                $this->_getHelper()->log('Executando evento: ', $event);

                $response = Mage::getModel('integrai/processEvent')->process($payload);

                return $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
            } else {
                $this->_getHelper()->log('Salvando o evento: ', $event);

                Mage::getModel('integrai/processEvents')
                    ->setData(array(
                        'event_id' => $eventId,
                        'event' => $event,
                        'payload' => json_encode($payload),
                        'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                    ))
                    ->save();

                $this->getResponse()->setHeader('Content-type', 'application/json');
                $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                    "ok" => true,
                )));
            }
        } catch (Throwable $e) {
            $this->error_handling($e);
        } catch (Exception $e) {
            $this->error_handling($e);
        }
    }

    private function error_handling($e) {
        $this->_getHelper()->log('Error ao atualizar configs', $e->getMessage());
        $this->getResponse()->setHttpResponseCode(400)->setBody(Mage::helper('core')->jsonEncode(array(
            "ok" => false,
            "error" => $e->getMessage()
        )));
    }
}