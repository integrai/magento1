<?php

$configs = array(
    array(
        'name' => 'EVENTS_ENABLED',
        'values' => '[
          "NEW_CUSTOMER",
          "NEWSLETTER_SUBSCRIBER",
          "ADD_PRODUCT_CART",
          "NEW_ORDER",
          "SAVE_ORDER",
          "CANCEL_ORDER",
          "REFUND_INVOICE",
          "FINALIZE_CHECKOUT"
        ]',
        'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
        'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
    ),
);

foreach ($configs as $config) {
    Mage::getModel('integrai/config')
        ->setData($config)
        ->save();
}
