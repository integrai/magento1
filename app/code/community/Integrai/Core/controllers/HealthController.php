<?php

class Integrai_Core_HealthController
    extends Mage_Core_Controller_Front_Action
{
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

            $magentoVersion = Mage::getVersion();
            $moduleVersion = (array)Mage::getConfig()->getModuleConfig("Integrai_Core");
            $isRunningEventProcess = $this->_getHelper()->getConfigTable('PROCESS_EVENTS_RUNNING', null, 'RUNNING', false);

            $totalEventsToProcess = Mage::getModel('integrai/processEvents')
                ->getCollection()
                ->getSize();

            $totalUnsentEvent = Mage::getModel('integrai/events')
                ->getCollection()
                ->getSize();

            $data = array(
                'phpVersion' => phpversion(),
                'platform' => 'magento',
                'platformVersion' => $magentoVersion,
                'moduleVersion' => $moduleVersion['version'],
                'isRunningEventProcess' => $isRunningEventProcess === 'RUNNING',
                'totalEventsToProcess' => $totalEventsToProcess,
                'totalUnsentEvent' => $totalUnsentEvent
            );

            $this->_getHelper()->log('Health executado');

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($data));
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