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
        // check origin
//        if ($_SERVER['SERVER_NAME'] === $this->_getHelper()->getGlobalConfig('api_url')) {

        try{
            $api = Mage::getModel('integrai/api');
            $events = $api->request('/store/event');

            $success = [];
            $errors = [];
            $this->_getHelper()->log('Total de eventos a processar: ', count($events));

            foreach ($events as $event) {
                $eventId = $event['_id'];
                $payload = $event['payload'];

                try {
                    foreach($payload['models'] as $modelKey => $modelValue) {
                        $modelName = $modelValue['name'];
                        $modelRun = (bool)$modelValue['run'];

                        if ($modelRun) {
                            $modelArgs = $this->transformArgs($modelValue);
                            $modelMethods = $modelValue['methods'];

                            $model = call_user_func_array(array(Mage, "getModel"), $modelArgs);

                            foreach($modelMethods as $methodKey => $methodValue) {
                                $methodName = $methodValue['name'];
                                $methodRun = (bool)$methodValue['run'];

                                if($methodRun) {
                                    $methodArgs = $this->transformArgs($methodValue);
                                    $model = call_user_func_array(array($model, $methodName), $methodArgs);
                                }
                            }

                            $this->_models[$modelName] = $model;
                        }
                    }

                    array_push($success, $eventId);
                } catch (Exception $e) {
                    $this->_getHelper()->log('Erro ao processar o evento', $event);
                    $this->_getHelper()->log('Erro', $e->getMessage());

                    array_push($errors, $eventId);
                }
            }

            // Delete events with success
            if(count($success) > 0){
                $api->request('/store/event', 'DELETE', array(
                    'event_ids' => $success
                ));
            }

            $this->_getHelper()->log('Eventos processados: ', array(
                'success' => $success,
                'errors' => $errors
            ));

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

//        } else {
//            $this->_redirect("/");
//        }
    }

    private function getOtherModel($modelName) {
        return $this->_models[$modelName];
    }

    private function transformArgs($itemValue) {
        $newArgs = array();

        $args = isset($itemValue['args']) ? $itemValue['args'] : null;
        if(is_array($args)) {
            $argsFormatted = array_values($args);

            foreach($argsFormatted as $arg){
                if(is_array($arg) && $arg['otherModelName']) {
                    array_push($newArgs, $this->getOtherModel($arg['otherModelName']));
                } else {
                    array_push($newArgs, $arg);
                }
            }
        }

        return $newArgs;
    }
}