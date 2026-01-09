<?php
use CRM_Eck_ExtensionUtil as E;

return [
  [
    'name' => 'SavedSearch_ECK_Subtypes',
    'entity' => 'SavedSearch',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'ECK_Subtypes',
        'label' => E::ts('ECK Subtypes'),
        'api_entity' => 'OptionValue',
        'api_params' => [
          'version' => 4,
          'select' => ['label', 'description', 'is_active', 'grouping'],
          'orderBy' => [],
          'where' => [
            [
              'option_group_id:name',
              '=',
              'eck_sub_types',
            ],
          ],
          'groupBy' => [],
          'join' => [],
          'having' => [],
        ],
      ],
      'match' => ['name'],
    ],
  ],
  [
    'name' => 'SavedSearch_ECK_Subtypes_SearchDisplay_ECK_Subtypes',
    'entity' => 'SearchDisplay',
    'cleanup' => 'unused',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'ECK_Subtypes',
        'label' => E::ts('ECK Subtypes'),
        'saved_search_id.name' => 'ECK_Subtypes',
        'type' => 'table',
        'settings' => [
          'description' => '',
          'sort' => [],
          'limit' => 20,
          'pager' => [
            'show_count' => FALSE,
            'expose_limit' => FALSE,
            'hide_single' => TRUE,
          ],
          'placeholder' => 5,
          'columns' => [
            [
              'type' => 'field',
              'key' => 'label',
              'label' => E::ts('Subtype'),
              'sortable' => TRUE,
              'icons' => [
                [
                  'field' => 'icon',
                  'side' => 'left',
                ],
              ],
            ],
            [
              'type' => 'field',
              'key' => 'description',
              'label' => E::ts('Description'),
              'sortable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'is_active',
              'label' => E::ts('Enabled'),
              'sortable' => TRUE,
            ],
            [
              'size' => 'btn-xs',
              'links' => [
                [
                  'entity' => 'OptionValue',
                  'action' => 'update',
                  'join' => '',
                  'target' => 'crm-popup',
                  'icon' => 'fa-pencil',
                  'text' => E::ts('Edit'),
                  'style' => 'default',
                  'path' => '',
                  'task' => '',
                  'conditions' => [],
                ],
                [
                  'entity' => 'OptionValue',
                  'action' => 'delete',
                  'join' => '',
                  'target' => 'crm-popup',
                  'icon' => 'fa-trash',
                  'text' => E::ts('Delete'),
                  'style' => 'danger',
                  'path' => '',
                  'task' => '',
                  'conditions' => [],
                ],
              ],
              'type' => 'buttons',
              'alignment' => 'text-right',
            ],
          ],
          'actions' => FALSE,
          'classes' => ['table', 'table-striped', 'table-bordered'],
          'cssRules' => [
            ['disabled', 'is_active', '=', FALSE],
          ],
          'draggable' => 'weight',
          'toolbar' => [
            [
              'path' => 'civicrm/admin/eck/subtype?reset=1&action=add&type=[grouping]',
              'icon' => 'fa-plus',
              'text' => E::ts('Add Subtype'),
              'style' => 'default',
              'conditions' => [],
              'task' => '',
              'entity' => '',
              'action' => '',
              'join' => '',
              'target' => '',
            ],
          ],
          'button' => NULL,
        ],
      ],
      'match' => [
        'saved_search_id',
        'name',
      ],
    ],
  ],
];
