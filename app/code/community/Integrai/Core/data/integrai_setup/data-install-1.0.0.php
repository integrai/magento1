<?php

$configs = array(
    array(
        'name' => 'EVENTS_ENABLED',
        'values' => '[]',
        'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
        'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
    ),
    array(
        'name' => 'GLOBAL',
        'values' => '{
          "minutes_abandoned_cart_lifetime": 60,
          "api_url": "https://api.integrai.com.br",
          "api_timeout_seconds": 15,
          "process_events_limit": 50
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
    array(
        'name' => 'SCRIPTS',
        'values' => '[]',
        'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
        'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
    ),
);

foreach ($configs as $config) {
    Mage::getModel('integrai/config')
        ->setData($config)
        ->save();
}
