<?php
return [
  [
    'name' => 'OptionGroup_eck_sub_types',
    'entity' => 'OptionGroup',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'eck_sub_types',
        'title' => 'ECK Subtypes',
        'description' => 'Entity Construction Kit Entity Subtypes',
        'data_type' => NULL,
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'is_locked' => TRUE,
        'option_value_fields' => ['name', 'label', 'description', 'icon'],
      ],
    ],
    'match' => ['name'],
  ],
];
