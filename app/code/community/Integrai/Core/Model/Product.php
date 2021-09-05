<?php

class Integrai_Core_Model_Product {
    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function savePhotos($productId, $photos) {
        $product = Mage::getModel('catalog/product')->load($productId);

        if ($product->getId() && isset($photos) && count($photos) > 0) {
            $mediaApi = Mage::getModel('catalog/product_attribute_media_api');
            $items = $mediaApi->items($product->getId());

            if (count($items) > 0) {
                foreach($items as $item) {
                    $mediaApi->remove($product->getId(), $item['file']);
                }
                $product->save();
                $product = Mage::getModel('catalog/product')->load($productId);
            }

            $mediaFolder = Mage::getBaseDir('media') . DS . 'tmp' . DS . 'integrai' . DS;

            if (!is_dir($mediaFolder)) {
                mkdir($mediaFolder);
            }

            foreach($photos as $index => $photo) {
                $imageType = $index == 0 ? array('image', 'small_image', 'thumbnail') : array();
                $fileName = preg_replace('/\?.*/', '', basename($photo));
                $fileNamePath = $mediaFolder.$fileName;

                file_put_contents($fileNamePath, file_get_contents($photo));
                if (file_exists($fileNamePath)) {
                    $product->addImageToMediaGallery(
                        $fileNamePath,
                        $imageType,
                        false,
                        false,
                    );
                }
            }

            $product->save();
        }
    }
}