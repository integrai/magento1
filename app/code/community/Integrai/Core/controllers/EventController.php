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
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);

            $api = Mage::getModel('integrai/api');

            $batchId = isset($_GET['batchId']) ? trim($_GET['batchId']) : "";
            $events = $api->request(
                '/store/event',
                'GET',
                null,
                array("batchId" => $batchId)
            );

            $this->_getHelper()->log('Total de eventos carregados: ', count($events));

            if (count($events) > 0) {
                $eventIds = array_map(function ($event) {
                    return $event['_id'];
                }, $events);

                $actualEvents = Mage::getModel('integrai/processEvents')
                    ->getCollection()
                    ->addFieldToFilter(
                        'event_id',
                        array('in' => $eventIds)
                    )
                    ->load();

                $actualEventIds = array();
                foreach ($actualEvents as $actualEvent) {
                    $actualEventIds[] = $actualEvent->getData('event_id');
                }

                $data = array();
                foreach ($events as $event) {
                    $eventId = $event['_id'];

                    if (!in_array($eventId, $actualEventIds)) {
                        $data[] = array(
                            'event_id' => $eventId,
                            'event' => $event['event'],
                            'payload' => json_encode($event['payload']),
                            'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
                        );
                    }
                }

                $this->_getHelper()->log('Total de eventos agendado para processar: ', count($data));

                if (count($data) > 0) {
                    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                    $connection->insertMultiple('integrai_process_events', $data);
                }
            }

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                "ok" => true,
            )));
        } catch (Exception $e) {
            $this->_getHelper()->log('Error ao atualizar configs', $e->getMessage());
            $this->getResponse()->setHttpResponseCode(400)->setBody(Mage::helper('core')->jsonEncode(array(
                "ok" => false,
                "error" => $e->getMessage()
            )));
        }
    }
}