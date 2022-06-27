<?php
// Auto-generate managed records for each entity type
$values = [];
foreach (CRM_Eck_BAO_EckEntityType::getEntityTypes() as $type) {
  // Synchronize tag_used_for option values.
  $values[] = [
    'name' => 'tag_used_for:' . $type['name'],
    'entity' => 'OptionValue',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'tag_used_for',
        'label' => $type['label'],
        'value' => $type['table_name'],
        'name' => $type['entity_name'],
        'is_reserved' => TRUE,
        'is_active' => TRUE,
      ],
    ],
  ];
}
return $values;
