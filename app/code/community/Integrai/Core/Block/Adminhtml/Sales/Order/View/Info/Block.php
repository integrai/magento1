<?php
class Integrai_Core_Block_Adminhtml_Sales_Order_View_Info_Block extends Mage_Core_Block_Template {

    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function getDangerAlert() {
        return $this->_getHelper()->getConfigTable('GLOBAL', 'dangerAlert', null);
    }
}