<?php
/**
 * Form block for boleto payment
 */
class Integrai_Core_Block_Payment_Boleto extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('integrai/payment/boleto.phtml');
    }

    protected function _prepareLayout()
    {
        $paymentModel = Mage::getSingleton('Integrai_Core_Model_Payment_Boleto');
        $this->setPaymentBoletoConfig($paymentModel->getPaymentBoletoConfig());
        $this->setCustomer(json_encode($paymentModel->getCustomer()));
    }
}