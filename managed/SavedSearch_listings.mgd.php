<?php
use CRM_Eck_ExtensionUtil as E;

// Auto-generate saved search and search display for each entity type
$searches = [];
foreach (CRM_Eck_BAO_EckEntityType::getEntityTypes() as $type) {
  $searches[] = [
    'name' => 'SavedSearch_listing:' . $type['name'],
    'entity' => 'SavedSearch',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'ECK_Listing_' . $type['name'],
        'label' => E::ts('ECK Listing: %1', [1 => $type['label']]),
        'form_values' => NULL,
        'search_custom_id' => NULL,
        'api_entity' => $type['entity_name'],
        'api_params' => [
          'version' => 4,
          'select' => [
            'title',
            'subtype:label',
            'created_date',
            'modified_date',
          ],
          'orderBy' => [],
          'where' => [],
          'groupBy' => [],
          'join' => [],
          'having' => [],
        ],
        'expires_date' => NULL,
        'description' => NULL,
        'mapping_id' => NULL,
      ],
      'match' => [
        'name',
      ],
    ],
  ];
  $searches[] = [
    'name' => 'SavedSearch_listing_display:' . $type['name'],
    'entity' => 'SearchDisplay',
    'cleanup' => 'always',
    'update' => 'unmodified',
    'params' => [
      'version' => 4,
      'values' => [
        'name' => 'ECK_Listing_Display' . $type['name'],
        'label' => E::ts('ECK Listing Display: %1', [1 => $type['label']]),
        'saved_search_id.name' => 'ECK_Listing_' . $type['name'],
        'type' => 'table',
        'settings' => [
          'actions' => TRUE,
          'limit' => 50,
          'classes' => [
            'table',
            'table-striped',
          ],
          'pager' => [
            'show_count' => TRUE,
            'expose_limit' => TRUE,
          ],
          'placeholder' => 5,
          'sort' => [],
          'columns' => [
            [
              'type' => 'field',
              'key' => 'title',
              'dataType' => 'String',
              'label' => E::ts('Title'),
              'sortable' => TRUE,
              'editable' => TRUE,
            ],
            [
              'type' => 'field',
              'key' => 'subtype:label',
              'dataType' => 'String',
              'label' => E::ts('Type'),
              'sortable' => TRUE,
              'icons' => [
                [
                  'field' => 'subtype:icon',
                  'side' => 'left',
                ],
                [
                  'icon' => $type['icon'],
                  'side' => 'left',
                  'if' => [
                    'subtype:icon',
                    'IS EMPTY',
                  ],
                ],
              ],
            ],
            [
              'type' => 'field',
              'key' => 'created_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Created'),
              'sortable' => TRUE,
              'rewrite' => E::ts('[created_date] by [created_id.display_name]'),
            ],
            [
              'type' => 'field',
              'key' => 'modified_date',
              'dataType' => 'Timestamp',
              'label' => E::ts('Modified'),
              'sortable' => TRUE,
              'rewrite' => E::ts('[modified_date] by [modified_id.display_name]'),
            ],
            [
              'type' => 'buttons',
              'alignment' => 'text-right',
              'size' => 'btn-xs',
              'links' => [
                [
                  'entity' => $type['entity_name'],
                  'action' => 'view',
                  'join' => '',
                  'icon' => 'fa-external-link',
                  'text' => E::ts('View'),
                  'style' => 'default',
                  'path' => '',
                  'condition' => [],
                ],
                [
                  'entity' => $type['entity_name'],
                  'action' => 'update',
                  'join' => '',
                  'target' => 'crm-popup',
                  'icon' => 'fa-pencil',
                  'text' => E::ts('Edit'),
                  'style' => 'default',
                  'path' => '',
                  'condition' => [],
                ],
              ],
            ],
          ],
        ],
        'acl_bypass' => FALSE,
      ],
      'match' => [
        'name',
        'saved_search_id',
      ],
    ],
  ];
}
return $searches;
