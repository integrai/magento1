<?php

class Integrai_Core_Model_Api {
    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function request($body = array(), $params = array()) {
        $curl = curl_init();

        $url = $this->getApiUrl();

        if (isset($params) && count($params) > 0) {
            $url = $url . '&' . http_build_query($params);
        }

        $curl_options = array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_VERBOSE => 1,
            CURLOPT_TIMEOUT => $this->_getHelper()->getGlobalConfig('apiTimeoutSeconds', 2),
            CURLOPT_HTTPHEADER => $this->getHeaders(),
            CURLOPT_URL => $url,
            CURLOPT_CUSTOMREQUEST =>  'POST',
            CURLOPT_POST => true
        );

        if (!is_null($body) && count($body) > 0) {
            $curl_options[CURLOPT_POSTFIELDS] = json_encode($body);
        }

        curl_setopt_array($curl, $curl_options);

        $response = json_decode(curl_exec($curl), true);
        $info = curl_getinfo($curl);
        $response_error = isset($response['error']) ? $response['error'] : "Ocorreu um erro, tente novamente";

        if(!in_array(array(200, 201, 204), $info['http_code'])) {
            $this->_getHelper()->log("HTTP ERROR", array(
                'code' => curl_errno($curl),
                'error' => curl_error($curl),
                'response' => $response,
                'info' => $info,
                'headers' => $this->getHeaders(),
                'body' => $body,
            ), Zend_Log::ERR);

            throw new Exception($response_error);
        }

        curl_close($curl);
        return $response;
    }

    private function getApiUrl() {
        return $this->_getHelper()->getGlobalConfig('apiUrl');
    }

    private function getHeaders() {
        return array(
            "Content-Type: application/json",
            "Accept: application/json",
        );
    }

    public function sendEvent($eventName, $payload, $resend = false, $isSync = false) {
        try{
            $response = $this->request(array(
                'partnerEvent' => $eventName,
                'payload' => $payload,
            ), array( 'isSync' => $isSync ));
            $this->_getHelper()->log($eventName, 'Enviado com sucesso');
            return $response;
        } catch (Throwable $e) {
            $this->error_handling($e, $resend, $eventName, $payload);
        } catch (Exception $e) {
            $this->error_handling($e, $resend, $eventName, $payload);
        }
    }

    private function error_handling($e, $resend, $eventName, $payload) {
        if(!$resend) {
            $this->_backupEvent($eventName, $payload);
        } else {
            throw new Exception($e);
        }
    }

    private function _backupEvent($eventName, $payload) {
        $this->_getHelper()->log("Gravando no banco para mandar depois", $eventName, Zend_Log::INFO);

        return Mage::getModel('integrai/events')
            ->setData(array(
                'event' => $eventName,
                'payload' => json_encode($payload),
                'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
            ))
            ->save();
    }

    public function resendBackupEvents() {
        if ($this->_getHelper()->isEnabled()) {
            $eventsModel =  Mage::getModel('integrai/events');

            $events = $eventsModel->getCollection();

            foreach ($events as $event) {
                $eventName = $event->getData('event');
                $payload = json_decode($event->getData('payload'), true);
                try{
                    $this->sendEvent($eventName, $payload, true);
                    $this->_getHelper()->log('DELETE');
                    $event->delete();
                } catch (Throwable $e) {
                    $this->_getHelper()->log('Error ao reenviar o evento', $eventName, Zend_Log::ERR);
                } catch (Exception $e) {
                    $this->_getHelper()->log('Error ao reenviar o evento', $eventName, Zend_Log::ERR);
                }
            }
        }
    }

    public function processEvents() {
        if ($this->_getHelper()->isEnabled()) {
            $this->_getHelper()->log('Iniciando processamento dos eventos...');

            $limit = $this->_getHelper()->getConfigTable('GLOBAL', 'processEventsLimit', 50);
            $timeout = $this->_getHelper()->getConfigTable('GLOBAL', 'processEventsTimeoutHours', 1);
            $isRunning = $this->_getHelper()->getConfigTable('PROCESS_EVENTS_RUNNING', null, 'NOT_RUNNING', false);
            $lastRunning = $this->_getHelper()->getConfigTable('LAST_PROCESS_EVENTS_RUN', null, null, false);
            $now = date('Y-m-d H:i:s');
            $dateDiff = date_diff(date_create($now), date_create($lastRunning));
            $interval = $dateDiff->format('%h');

            if ($isRunning === 'RUNNING' && $lastRunning && $interval < $timeout) {
                $this->_getHelper()->log('Já existe um processo rodando');
            } else {
                $this->_getHelper()->updateConfig('PROCESS_EVENTS_RUNNING', 'RUNNING');
                $this->_getHelper()->updateConfig('LAST_PROCESS_EVENTS_RUN', $now);

                $events = Mage::getModel('integrai/processEvents')
                    ->getCollection()
                    ->setPageSize($limit)
                    ->setCurPage(1);

                $this->_getHelper()->log('Total de eventos a processar: ', $events->getSize());

                $success = [];
                $errors = [];
                $eventIds = [];

                foreach ($events as $event) {
                    $eventIds[] = $event->getData('id');

                    $eventId = $event->getData('event_id');
                    $payload = json_decode($event->getData('payload'), true);

                    try {
                        if(!isset($payload) || !isset($payload['models']) || !is_array($payload['models'])) {
                            throw new \Exception('Evento sem payload');
                        }

                        Mage::getModel('integrai/processEvent')->process($payload);

                        array_push($success, $eventId);
                    } catch (Throwable $e) {
                        array_push($errors, $this->error_handling_process_events($e, $event, $eventId));
                    } catch (Exception $e) {
                        array_push($errors, $this->error_handling_process_events($e, $event, $eventId));
                    }
                }

                // Delete events with success
                if (count($success) > 0 || count($errors) > 0) {
                    $eventIdsRemove = implode(', ', $eventIds);
                    $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
                    $connection->delete('integrai_process_events', "id in ($eventIdsRemove)");

                    $this->_getHelper()->log('Eventos processados: ', array(
                        'success' => $success,
                        'errors' => $errors
                    ));
                }

                $this->_getHelper()->updateConfig('PROCESS_EVENTS_RUNNING', 'NOT_RUNNING');
            }
        }
    }

    private function error_handling_process_events($e, $event, $eventId) {
        $this->_getHelper()->log('Erro ao processar o evento', $event->getData());
        $this->_getHelper()->log('Erro', $e->getMessage());

        if ($eventId) {
            return array(
                "eventId" => $eventId,
                "error" => $e->getMessage()
            );
        }
    }
}