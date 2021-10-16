<?php

class Integrai_Core_Model_ProcessEvent {
    protected $_models = array();

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function process($payload) {
        $model = null;

        foreach($payload['models'] as $modelKey => $modelValue) {
            $modelName = $modelValue['name'];
            $modelRun = (bool)$modelValue['run'];

            if ($modelRun) {
                $modelArgs = $this->transformArgs($modelValue);
                $modelMethods = $modelValue['methods'];

                $model = call_user_func_array(array(Mage, "getModel"), $modelArgs);
                $model = $this->runMethods($model, $modelMethods);

                $this->_models[$modelName] = $model;
            }
        }

        return $model;
    }

    private function runMethods($model, $modelMethods) {
        foreach($modelMethods as $methodKey => $methodValue) {
            $methodName = $methodValue['name'];
            $methodRun = (bool)$methodValue['run'];
            $methodCheckReturnType = isset($methodValue['checkReturnType']) ? $methodValue['checkReturnType'] : null;

            if($methodRun && $model) {
                $methodArgs = $this->transformArgs($methodValue);
                $model = call_user_func_array(array($model, $methodName), $methodArgs);

                if ($methodCheckReturnType) {
                    $types = (array) $methodCheckReturnType['types'];
                    $errorMessage = $methodCheckReturnType['errorMessage'];
                    if (!in_array(gettype($model), $types)) {
                        throw new Exception($errorMessage);
                    }
                }
            }
        }

        return $model;
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
                if(is_array($arg) && isset($arg['otherModelName'])) {
                    $model = $this->getOtherModel($arg['otherModelName']);

                    if (isset($arg['otherModelMethods'])) {
                        array_push($newArgs, $this->runMethods($model, $arg['otherModelMethods']));
                    } else {
                        array_push($newArgs, $model);
                    }
                } else {
                    array_push($newArgs, $arg);
                }
            }
        }

        return $newArgs;
    }

}