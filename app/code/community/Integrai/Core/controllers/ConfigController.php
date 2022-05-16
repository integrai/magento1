<?php

class Integrai_Core_ConfigController
    extends Mage_Core_Controller_Front_Action
{
    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function indexAction()
    {
        try{
            if (!$this->_getHelper()->checkAuthorization($this->getRequest()->getHeader('Authorization'))) {
                return $this->getResponse()->setHttpResponseCode(401)->setBody(Mage::helper('core')->jsonEncode(array(
                    "error" => "Unauthorized"
                )));
            }

            $this->_getHelper()->log('Salvando novas configurações...');

            $configs = $data = json_decode($this->getRequest()->getRawBody(), true);

            foreach ($configs as $config) {
                $configItem = Mage::getModel('integrai/config')
                    ->load($config['name'], 'name');
                if ($configItem->getId()) {
                    $configItem
                        ->setValues($config['values'])
                        ->setUpdatedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                        ->save();

                } else {
                    Mage::getModel('integrai/config')
                        ->setData($config)
                        ->setUpdatedAt(strftime('%Y-%m-%d %H:%M:%S', time()))
                        ->save();
                }
            }

            $this->getResponse()->setHeader('Content-type', 'application/json');
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode(array(
                "ok" => true,
            )));
        } catch (Throwable $e) {
            $this->error_handling($e);
        } catch (Exception $e) {
            $this->error_handling($e);
        }
    }

    private function error_handling($e) {
        $this->_getHelper()->log('Error ao atualizar configs', $e->getMessage());
        $this->getResponse()->setHttpResponseCode(400)->setBody(Mage::helper('core')->jsonEncode(array(
            "ok" => false,
            "error" => $e->getMessage()
        )));
    }

    public function attributesAction() {
        if (!$this->_getHelper()->checkAuthorization($this->getRequest()->getHeader('Authorization'))) {
            return $this->getResponse()->setHttpResponseCode(401)->setBody(Mage::helper('core')->jsonEncode(array(
                "error" => "Unauthorized"
            )));
        }

        $attributes = Mage::getSingleton('eav/config')
            ->getEntityType(Mage_Catalog_Model_Product::ENTITY)
            ->getAttributeCollection()
            ->setOrder('frontend_label','ASC');

        $options = array();

        /** @var  Mage_Eav_Model_Config $attribute */
        foreach ($attributes as $attribute) {
            $label = $attribute->getStoreLabel() ?: $attribute->getFrontendLabel();

            if ($label) {
                $values = [];

                if ($attribute->getFrontendInput() === "select") {
                    foreach ($attribute->getSource()->getAllOptions() as $option) {
                        if ($option['value'] && $option['label']) {
                            $values[] = $option['label'];
                        }
                    }
                }

                $options[] = array(
                    "code" => $attribute->getAttributeCode(),
                    "label" => $label,
                    "values" => $values,
                );
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($options));
    }

    public function categoriesAction() {
        if (!$this->_getHelper()->checkAuthorization($this->getRequest()->getHeader('Authorization'))) {
            return $this->getResponse()->setHttpResponseCode(401)->setBody(Mage::helper('core')->jsonEncode(array(
                "error" => "Unauthorized"
            )));
        }

        $categories = $this->transformCategory(1);
        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($categories));
    }

    private function transformCategory($parentId) {
        $categories = Mage::getModel('catalog/category')
            ->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToFilter('parent_id', $parentId);

        $options = array();
        foreach ($categories as $category) {
            $item = array(
                "id" => $category->getId(),
                "label" => $category->getName(),
            );

            if ($category->hasChildren()) {
                $item['children'] = $this->transformCategory($category->getId());
            }

            $options[] = $item;
        }

        return $options;
    }
}