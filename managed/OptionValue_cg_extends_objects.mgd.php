<?php
// Auto-generate managed records for each entity type
$values = [];
foreach (CRM_Eck_BAO_EckEntityType::getEntityTypes() as $type) {
  // Synchronize cg_extend_objects option values.
  $values[] = [
    'name' => 'cg_extends:' . $type['name'],
    'entity' => 'OptionValue',
    'cleanup' => 'always',
    'update' => 'always',
    'params' => [
      'version' => 4,
      'values' => [
        'option_group_id.name' => 'cg_extend_objects',
        'label' => $type['label'],
        'value' => $type['entity_name'],
        'name' => $type['table_name'],
        'is_reserved' => TRUE,
        'is_active' => TRUE,
        'grouping' => 'subtype',
      ],
    ],
  ];
}
return $values;
