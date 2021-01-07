<?php
/**
 * Form block for credit card payment
 */
class Integrai_Core_Block_Payment_CreditCard extends Mage_Payment_Block_Form
{
    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('integrai/payment/creditcard.phtml');
    }

    protected function _prepareLayout()
    {
        $paymentModel = Mage::getSingleton('Integrai_Core_Model_Payment_CreditCard');
        $this->setPaymentCreditCardConfig($paymentModel->getPaymentCreditCardConfig());
        $this->setAmount($paymentModel->getAmount());
    }
}