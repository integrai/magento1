<?php
class Integrai_Core_Block_Adminhtml_Sales_Order_View_Info_Payment extends Mage_Core_Block_Template {

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function getPaymentResponse() {
        $order = $this->getOrder();
        $paymentAdditionalInformation = $order->getPayment()->getData('additional_information');
        $this->_getHelper()->log('info', $paymentAdditionalInformation);
        $this->_getHelper()->log('info 2', $order->getPayment()->getData());
        return isset($paymentAdditionalInformation['payment_response']) ? $paymentAdditionalInformation['payment_response'] : array();
    }

    private function getOrder() {
        if (is_null($this->order)) {
            if (Mage::registry('current_order')) {
                $order = Mage::registry('current_order');
            }
            elseif (Mage::registry('order')) {
                $order = Mage::registry('order');
            }
            else {
                $order = new Varien_Object();
            }
            $this->order = $order;
        }
        return $this->order;
    }
}