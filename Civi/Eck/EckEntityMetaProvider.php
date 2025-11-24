<?php

namespace Civi\Eck;

use Civi\Schema\SqlEntityMetadata;
use CRM_Eck_ExtensionUtil as E;

class EckEntityMetaProvider extends SqlEntityMetadata {

  /**
   * @var array<string, string>|null
   */
  private $eckDefn;

  /**
   * @param string $propertyName
   * @return mixed|null
   */
  public function getProperty(string $propertyName) {
    $staticProps = [
      'log' => TRUE,
      'label_field' => 'title',
    ];
    if (isset($staticProps[$propertyName])) {
      return $staticProps[$propertyName];
    }
    $entity_type = $this->getEckDefn();
    if ($propertyName === 'paths') {
      return [
        'browse' => "civicrm/eck/entity/list/{$entity_type['name']}",
        'view' => "civicrm/eck/entity?reset=1&type={$entity_type['name']}&id=[id]",
        'update' => "civicrm/eck/entity/edit/{$entity_type['name']}/[subtype]#?Eck_{$entity_type['name']}=[id]",
        'add' => "civicrm/eck/entity/edit/{$entity_type['name']}/[subtype]",
      ];
    }
    $entityProps = [
      'name' => 'Eck_' . $entity_type['name'],
      'title' => $entity_type['label'],
      'title_plural' => $entity_type['label'],
      'description' => E::ts('Entity Construction Kit entity type %1', [1 => $entity_type['label']]),
      'icon' => strlen($entity_type['icon']) > 0 ? $entity_type['icon'] : 'fa-cubes',
      'table' => _eck_get_table_name($entity_type['name']),
    ];
    return $entityProps[$propertyName] ?? NULL;
  }

  /**
   * @return array<string, array<string, mixed>>
   */
  public function getFields(): array {
    $fields = [
      'id' => [
        'title' => ts('ID'),
        'sql_type' => 'int unsigned',
        'input_type' => 'Number',
        'required' => TRUE,
        'description' => E::ts('The unique entity ID.'),
        'usage' => [
          'import',
          'export',
        ],
        'primary_key' => TRUE,
        'auto_increment' => TRUE,
      ],
      'title' => [
        'title' => E::ts('Title'),
        'sql_type' => 'text',
        'description' => E::ts('The entity title.'),
        'required' => TRUE,
        'input_type' => 'Text',
        'usage' => [
          'import',
          'export',
        ],
      ],
      'subtype' => [
        'title' => E::ts('Subtype'),
        'sql_type' => 'text',
        'description' => E::ts('The entity subtype.'),
        'required' => TRUE,
        'input_type' => 'Text',
        'pseudoconstant' => [
          'callback' => [__CLASS__, 'getSubtypeOptions'],
          'suffixes' => ['name', 'label', 'description', 'icon'],
        ],
        'usage' => [
          'import',
          'export',
        ],
      ],
      'created_id' => [
        'title' => ts('Created By Contact ID'),
        'sql_type' => 'int unsigned',
        'input_type' => 'EntityRef',
        'description' => ts('FK to contact table.'),
        'default_callback' => ['CRM_Core_Session', 'getLoggedInContactID'],
        'input_attrs' => [
          'label' => ts('Created By'),
        ],
        'usage' => [
          'import',
          'export',
        ],
        'entity_reference' => [
          'entity' => 'Contact',
          'key' => 'id',
          'on_delete' => 'SET NULL',
        ],
      ],
      'modified_id' => [
        'title' => ts('Modified By Contact ID'),
        'sql_type' => 'int unsigned',
        'input_type' => NULL,
        'readonly' => TRUE,
        'description' => ts('FK to contact table.'),
        'default_callback' => ['CRM_Core_Session', 'getLoggedInContactID'],
        'input_attrs' => [
          'label' => ts('Modified By'),
        ],
        'usage' => [
          'import',
          'export',
        ],
        'entity_reference' => [
          'entity' => 'Contact',
          'key' => 'id',
          'on_delete' => 'SET NULL',
        ],
      ],
      'created_date' => [
        'title' => ts('Created Date'),
        'sql_type' => 'timestamp',
        'input_type' => 'Select Date',
        'readonly' => TRUE,
        'description' => ts('When was the contact was created.'),
        'default' => NULL,
        'usage' => [
          'export',
        ],
        'input_attrs' => [
          'format_type' => 'activityDateTime',
          'label' => ts('Created Date'),
        ],
      ],
      'modified_date' => [
        'title' => ts('Modified Date'),
        'sql_type' => 'timestamp',
        'input_type' => 'Select Date',
        'readonly' => TRUE,
        'description' => ts('When was the contact (or closely related entity) was created or modified or deleted.'),
        'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
        'usage' => [
          'export',
        ],
        'input_attrs' => [
          'format_type' => 'activityDateTime',
          'label' => ts('Modified Date'),
        ],
      ],
    ];
    return $fields;
  }

  /**
   * @return array<string, string>
   */
  private function getEckDefn(): array {
    // Load and cache eck item
    if (!isset($this->eckDefn)) {
      /** @phpstan-var \CRM_Core_DAO $query */
      $query = \CRM_Core_DAO::executeQuery(
        'SELECT * FROM `civicrm_eck_entity_type` WHERE `name` = %1;',
        // Substr is used to strip the `Eck_` prefix
        [1 => [substr($this->entityName, 4), 'String']]
      );
      $this->eckDefn = $query->fetchAll()[0];
    }
    return $this->eckDefn;
  }

  /**
   * @param string $fieldName
   * @param array<string, string> $params
   * @return array<mixed>
   */
  public static function getSubtypeOptions(string $fieldName, array $params): array {
    $entityType = \CRM_Eck_BAO_Entity::getEntityType($params['entity']);
    $options = isset($entityType) ? \CRM_Eck_BAO_EckEntityType::getSubTypes($entityType, FALSE) : [];
    foreach ($options as &$option) {
      $option['id'] = $option['value'];
      unset($option['value']);
    }
    return array_values($options);
  }

}
