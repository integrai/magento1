<?php
/**
 * Form block for pix payment
 */
class Integrai_Core_Block_Payment_Pix extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('integrai/payment/pix.phtml');
    }

    protected function _prepareLayout()
    {
        $paymentModel = Mage::getSingleton('Integrai_Core_Model_Payment_Pix');
        $this->setPaymentPixConfig($paymentModel->getPaymentPixConfig());
        $this->setCustomer(json_encode($paymentModel->getCustomer()));
    }
}