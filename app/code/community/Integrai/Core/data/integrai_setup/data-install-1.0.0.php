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
          "minutesAbandonedCartLifetime": 60,
          "apiUrl": "https://api.integrai.com.br",
          "apiTimeoutSeconds": 15,
          "processEventsLimit": 50
        }',
        'created_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
        'updated_at' => strftime('%Y-%m-%d %H:%M:%S', time()),
    ),
    array(
        'name' => 'SHIPPING',
        'values' => '{
          "attributeWidth": "width",
          "attributeHeight": "height",
          "attributeLength": "length",
          "widthDefault": 11,
          "heightDefault": 2,
          "lengthDefault": 16
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
