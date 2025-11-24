<?php

// Add each ECK type with `in_recent = true` as a recent item provider
$values = [];
foreach (CRM_Eck_BAO_EckEntityType::getEntityTypes() as $type) {
  if (!$type['in_recent']) {
    continue;
  }
  $values[] = [
    'name' => 'recent_items_providers:' . $type['name'],
    'entity' => 'OptionValue',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'recent_items_providers',
        'label' => $type['label'],
        'value' => $type['entity_name'],
        'name' => $type['entity_name'],
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
      'match' => [
        'name',
        'option_group_id',
      ],
    ],
  ];
}
return $values;
