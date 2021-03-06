<?php

$installer = $this;
$installer->startSetup();

/*
 * Table INTEGRAI_CONFIG
 * */
$installer->run("DROP TABLE IF EXISTS {$this->getTable('integrai/config')};");
$configTable = $installer->getConnection()
    ->newTable($installer->getTable('integrai/config'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'id')
    ->addColumn('name', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'name')
    ->addColumn('values', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
    ), 'values')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
    ), 'created_at')
    ->addColumn('updated_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
    ), 'updated_at');

$installer->getConnection()->createTable($configTable);

/*
 * Table INTEGRAI_EVENTS
 * */
$installer->run("DROP TABLE IF EXISTS {$this->getTable('integrai/events')};");
$eventTable = $installer->getConnection()
    ->newTable($installer->getTable('integrai/events'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'id')
    ->addColumn('event', Varien_Db_Ddl_Table::TYPE_VARCHAR, null, array(
        'nullable'  => false,
    ), 'event')
    ->addColumn('payload', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
    ), 'payload')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
    ), 'created_at');

$installer->getConnection()->createTable($eventTable);

/*
 * Table INTEGRAI_PROCESS_EVENTS
 * */
$installer->run("DROP TABLE IF EXISTS {$this->getTable('integrai/process_events')};");
$processEventTable = $installer->getConnection()
    ->newTable($installer->getTable('integrai/process_events'))
    ->addColumn('id', Varien_Db_Ddl_Table::TYPE_INTEGER, null, array(
        'identity'  => true,
        'unsigned'  => true,
        'nullable'  => false,
        'primary'   => true,
    ), 'id')
    ->addColumn('event_id', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        'nullable'  => false,
    ), 'event_id')
    ->addColumn('event', Varien_Db_Ddl_Table::TYPE_VARCHAR, 100, array(
        'nullable'  => false,
    ), 'event')
    ->addColumn('payload', Varien_Db_Ddl_Table::TYPE_TEXT, null, array(
        'nullable'  => false,
    ), 'payload')
    ->addColumn('created_at', Varien_Db_Ddl_Table::TYPE_TIMESTAMP, null, array(
        'nullable'  => false,
    ), 'created_at')
    ->addIndex(
        $installer->getIdxName(
            'integrai/process_events',
            array('event_id'),
            Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE
        ),
        array('event_id'),
        array('type' => Varien_Db_Adapter_Interface::INDEX_TYPE_UNIQUE)
    );

$installer->getConnection()->createTable($processEventTable);

$installer->endSetup();
