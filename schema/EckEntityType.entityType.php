<?php
use CRM_Eck_ExtensionUtil as E;

return [
  'name' => 'EckEntityType',
  'table' => 'civicrm_eck_entity_type',
  'class' => 'CRM_Eck_DAO_EckEntityType',
  'getInfo' => fn() => [
    'title' => E::ts('ECK Entity Type'),
    'title_plural' => E::ts('ECK Entity Types'),
    'description' => E::ts('Custom CiviCRM entity types'),
    'log' => TRUE,
    'label_field' => 'label',
  ],
  'getPaths' => fn() => [
    'add' => 'civicrm/admin/eck/entity-type?reset=1&action=add',
    'update' => 'civicrm/admin/eck/entity-type?reset=1&action=update&type=[name]',
    'delete' => 'civicrm/admin/eck/entity-type?reset=1&action=delete&type=[name]',
  ],
  'getIndices' => fn() => [
    'UI_name' => [
      'fields' => [
        'name' => TRUE,
      ],
      'unique' => TRUE,
    ],
  ],
  'getFields' => fn() => [
    'id' => [
      'title' => E::ts('ID'),
      'sql_type' => 'int unsigned',
      'input_type' => 'Number',
      'required' => TRUE,
      'description' => E::ts('Unique EckEntityType ID'),
      'primary_key' => TRUE,
      'auto_increment' => TRUE,
    ],
    'name' => [
      'title' => E::ts('Name'),
      'sql_type' => 'varchar(52)',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('The entity type name, also used in the sql table name'),
    ],
    'label' => [
      'title' => E::ts('Label'),
      'sql_type' => 'text',
      'input_type' => 'Text',
      'required' => TRUE,
      'description' => E::ts('The entity type\'s human-readable name'),
    ],
    'icon' => [
      'title' => E::ts('Icon'),
      'sql_type' => 'varchar(255)',
      'input_type' => 'Text',
      'description' => E::ts('crm-i icon class'),
      'default' => NULL,
    ],
    'has_subtypes' => [
      'title' => E::ts('Enable Subtypes'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => E::ts('Does this entity support subtypes?'),
      'default' => FALSE,
    ],
    'in_recent' => [
      'title' => E::ts('In Recent Items'),
      'sql_type' => 'boolean',
      'input_type' => 'CheckBox',
      'required' => TRUE,
      'description' => E::ts('Does this entity type get added to the recent items list'),
      'default' => TRUE,
    ],
  ],
];
