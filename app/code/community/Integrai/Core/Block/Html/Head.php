<?php

class Integrai_Core_Block_Html_Head extends Mage_Page_Block_Html_Head
{
    protected function _construct()
    {
        $this->setTemplate('page/html/head.phtml');
    }

    public function replaceItem($type, $name, $replace)
    {
        $key = $type.'/'.$name;
        if (isset($this->_data['items'][$key])) {
            $this->_data['items'][$key] = array(
                'type'   => $type,
                'name'   => $replace,
                'params' => $this->_data['items'][$key]['params'],
                'if'     => $this->_data['items'][$key]['if'],
                'cond'   => $this->_data['items'][$key]['cond']);
        }

        return $this;
    }
}
