<?php

class Integrai_Core_Model_Carrier
    extends Mage_Shipping_Model_Carrier_Abstract
    implements Mage_Shipping_Model_Carrier_Interface {

    const QUOTE = 'QUOTE';

    protected $_code = 'integrai_shipping';

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    protected function _getApi()
    {
        return Mage::getModel('integrai/api');
    }

    public function collectRates(Mage_Shipping_Model_Rate_Request $request)
    {
        if ($this->_getHelper()->isEventEnabled(self::QUOTE)) {
            try{
                $params = $this->prepareParamsRequest($request);
                $services = $this->_getApi()->request('/quote/shipping', 'POST', $params);
                /** @var Mage_Shipping_Model_Rate_Result $result */
                $result = Mage::getModel('shipping/rate_result');
                foreach ($services as $service) {
                    $result->append($this->transformRate($service));
                }
                return $result;
            } catch (Exception $e) {
                $error = Mage::getModel('shipping/rate_result_error');
                $data  = [
                    'carrier'       => $this->_code,
                    'carrier_title' => $this->_getHelper()->getCarrierConfig('title'),
                    'error_message' => $e->getMessage(),
                ];
                $error->setData($data);
                return $error;
            }
        }
    }

    private function prepareParamsRequest(Mage_Shipping_Model_Rate_Request $request) {
        return array(
            "destination_zipcode" => $request->getDestPostcode(),
            "cart_total_price" => $request->getPackageValue(),
            "cart_total_quantity" => $request->getPackageQty(),
            "cart_total_weight" => $request->getPackageWeight(),
            "cart_total_height" => $request->getPackageHeight(),
            "cart_total_width" => $request->getPackageWidth(),
            "cart_total_length" => $request->getPackageDepth(),
            "items" => $this->prepareItems($request->getAllItems()),
        );
    }

    private function prepareItems(array $items)  {
        $packageItems = array();

        foreach ($items as $item) {
            if (!$this->validePackageItem($item)) {
                continue;
            }

            $attribute_width   = $this->_getHelper()->getConfigTable('SHIPPING', 'attribute_width');
            $attribute_height  = $this->_getHelper()->getConfigTable('SHIPPING', 'attribute_height');
            $attribute_length  = $this->_getHelper()->getConfigTable('SHIPPING', 'attribute_length');
            $width_default     = $this->_getHelper()->getConfigTable('SHIPPING', 'width_default');
            $height_default    = $this->_getHelper()->getConfigTable('SHIPPING', 'height_default');
            $length_default    = $this->_getHelper()->getConfigTable('SHIPPING', 'length_default');

            /** @var Mage_Sales_Model_Quote_Item $item */

            $width  = $this->extractData($item, $attribute_width) ?: $width_default;
            $height = $this->extractData($item, $attribute_height) ?: $height_default;
            $length = $this->extractData($item, $attribute_length) ?: $length_default;

            $packageItems[] = (object) array(
                "weight" => (float) $item->getWeight(),
                "width" =>  (float) $width,
                "height" => (float) $height,
                "length" => (float) $length,
                "quantity" => (int) max(1, $item->getQty()),
                "sku" => (string) $item->getSku(),
                "unit_price" => (float) $item->getBasePrice(),
                "product" => (object) $item->getProduct()->getData(),
            );
        }

        return $packageItems;
    }

    private function validePackageItem(Mage_Sales_Model_Quote_Item $item) {
        if ($item->getProduct()->isComposite()) {
            return false;
        }

        if ($item->getProduct()->isVirtual()) {
            return false;
        }

        return true;
    }

    private function extractData(Mage_Sales_Model_Quote_Item $item, $key)
    {
        if ($item->getData($key)) {
            return $item->getData($key);
        }

        if ($item->getProduct()->getData($key)) {
            return $item->getProduct()->getData($key);
        }

        $value = Mage::getResourceSingleton('catalog/product')->getAttributeRawValue(
            $item->getProductId(),
            $key,
            $item->getProduct()->getStore()
        );

        return $value ?: null;
    }

    protected function transformRate($service)
    {
        /** @var Mage_Shipping_Model_Rate_Result_Method $rate */
        $rate = Mage::getModel('shipping/rate_result_method');

        $deliveryText = str_replace('%s', $service['deliveryTime'], $service['deliveryText']);
        $methodTitle = '$methodTitle - $deliveryText';

        $rate->setCarrier($this->_code);
        $rate->setCarrierTitle($service['carrierTitle']);
        $rate->setMethod($service['methodCode']);
        $rate->setMethodTitle(strtr($methodTitle, array(
            '$methodTitle' => $service['methodTitle'],
            '$deliveryText' => $deliveryText
        )));
        $rate->setMethodDescription($service['methodDescription']);
        $rate->setPrice($service['price']);
        $rate->setCost($service['cost']);

        return $rate;
    }

    public function getAllowedMethods()
    {
        return array(
            self::$_code => $this->_getHelper()->getCarrierConfig('title'),
        );
    }

    public function isTrackingAvailable()
    {
        return true;
    }
}