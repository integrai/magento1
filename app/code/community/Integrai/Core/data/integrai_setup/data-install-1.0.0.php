<?php

$configs = array(
    array(
        'name' => 'EVENTS_ENABLED',
        'values' => '[
          "SAVE_CUSTOMER",
          "CUSTOMER_BIRTHDAY",
          "NEWSLETTER_SUBSCRIBER",
          "ADD_PRODUCT_CART",
          "ABANDONED_CART",
          "NEW_ORDER",
          "SAVE_ORDER",
          "CANCEL_ORDER",
          "FINALIZE_CHECKOUT"
        ]',
        'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
        'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
    ),
    array(
        'name' => 'GLOBAL',
        'values' => '{
          "minutes_abandoned_cart_lifetime": 60,
          "api_url": "https://api.integrai.com.br/v1",
          "api_timeout_seconds": 3
        }',
        'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
        'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
    ),
    array(
        'name' => 'SHIPPING',
        'values' => '{
          "attribute_width": "width",
          "attribute_height": "height",
          "attribute_length": "length",
          "width_default": 11,
          "height_default": 2,
          "length_default": 16
        }',
        'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
        'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
    ),
);

foreach ($configs as $config) {
    Mage::getModel('integrai/config')
        ->setData($config)
        ->save();
}
