<?php

class Integrai_Core_Model_Product {
    protected function _getHelper()
    {
        return Mage::helper('integrai');
    }

    public function savePhotos($productId, $photos) {
        $this->_getHelper()->log('photos product', $photos);

        $product = Mage::getModel('catalog/product')->load($productId);

        if ($product->getId() && isset($photos) && count($photos) > 0) {
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