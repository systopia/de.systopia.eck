<?php
/*-------------------------------------------------------+
| CiviCRM Entity Construction Kit                        |
| Copyright (C) 2021 SYSTOPIA                            |
| Author: J. Schuppe (schuppe@systopia.de)               |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

use CRM_Eck_ExtensionUtil as E;

/**
 * @TODO: This file is mostly unneeded since the upgrade to EFv2
 * But the writeRecord and deleteRecord functions are still used.
 */
class CRM_Eck_DAO_Entity extends CRM_Core_DAO {
  const EXT = E::LONG_NAME;

  private static $_entityType;

  private static $_className;

  protected static $_tableName;

  public static $_log = TRUE;

  /**
   * Icon associated with this entity.
   *
   * @var string
   */
  public static $_icon = 'fa-cubes';

  /**
   * Paths for accessing this entity in the UI.
   *
   * @var string[]
   */
  protected static $_paths = [
    'browse' => 'civicrm/eck/entity/list/[eck_type]',
    'view' => 'civicrm/eck/entity?reset=1&type=[eck_type]&id=[id]&selectedChild=view',
//    'add' => 'civicrm/eck/entity/edit/[eck_type]/[eck_subtype]', // TODO: Is "eck_subtype" resolved correctly?
    'update' => 'civicrm/eck/entity?reset=1&type=[eck_type]&id=[id]&selectedChild=edit',
    'delete' => '', // TODO: Add path when UI is ready.
  ];

  /**
   * Unique entity ID.
   *
   * @var int
   */
  public $id;

  /**
   * The entity title.
   *
   * @var int
   */
  public $title;

  /**
   * The entity subtype.
   *
   * @var int
   */
  public $subtype;

  /**
   * @var string
   */
  public static $_labelField = 'title';

  /**
   * @param string $entityType
   *
   * @return \CRM_Eck_DAO_Entity
   *
   * @throws \Exception
   */
  public function __construct($entityType = NULL) {
    if (!isset($entityType)) {
      // TODO: We can't just throw an exception as this leads to errors
      //       everywhere, e.g. for get API actions on entities that reference
      //       at least one ECK entity.
      // throw new Exception('No ECK entity type given.');
    }
    self::$_entityType = $entityType;
    parent::__construct();
  }

  /**
   * {@inheritDoc}
   */
  public function initialize() {
    if (isset(self::$_entityType)) {
      self::$_className = 'CRM_Eck_DAO_' . self::$_entityType;
      self::$_tableName = _eck_get_table_name(self::$_entityType);
    }

    parent::initialize();
  }

  public static function getEntityTitle($plural = FALSE) {
    return E::ts('ECK Entity');
  }

  /**
   * @TODO: This is redundant with fields defined in EckEntityMetaProvider::getFields
   * @deprecated
   */
  public static function &fields() {
    // TODO: This is being called without the constructor being called
    //   beforehand, so this will not always work due to static variables not
    //   being set.
    //   A workaround for this has been developed by always calling the
    //   constructor for ECK entities in CiviCRM Core 6.6.0.
    //   @link https://github.com/civicrm/civicrm-core/pull/33263
    if (
      !isset(Civi::$statics[self::$_className]['fields'])
      || [] === Civi::$statics[self::$_className]['fields']
    ) {
      if (isset(self::$_entityType)) {
        Civi::$statics[self::$_className]['fields'] = [
          'id' => [
            'name' => 'id',
            'title' => E::ts('ID'),
            'type' => CRM_Utils_Type::T_INT,
            'description' => E::ts('The unique entity ID.'),
            'required' => TRUE,
            'where' => static::getTableName() . '.id',
            'export' => TRUE,
            'table_name' => static::getTableName(),
            'entity' => self::$_entityType,
            'bao' => 'CRM_Eck_DAO_Entity',
            'localizable' => 0,
            'readonly' => TRUE,
            'html' => [
              'type' => 'Number',
            ],
          ],
          'title' => [
            'name' => 'title',
            'title' => E::ts('Title'),
            'type' => CRM_Utils_Type::T_STRING,
            'description' => E::ts('The entity title.'),
            'required' => TRUE,
            'where' => static::getTableName() . '.title',
            'export' => TRUE,
            'table_name' => static::getTableName(),
            'entity' => self::$_entityType,
            'bao' => 'CRM_Eck_DAO_Entity',
            'localizable' => 1,
            'html' => [
              'type' => 'Text',
            ],
          ],
          'subtype' => [
            'name' => 'subtype',
            'title' => E::ts('Subtype'),
            'type' => CRM_Utils_Type::T_STRING,
            'description' => E::ts('The entity subtype.'),
            'required' => TRUE,
            'where' => static::getTableName() . '.subtype',
            'export' => TRUE,
            'table_name' => static::getTableName(),
            'entity' => self::$_entityType,
            'bao' => 'CRM_Eck_DAO_Entity',
            'localizable' => 0,
            'html' => [
              'type' => 'Text',
            ],
          ],
          'created_id' => [
            'name' => 'created_id',
            'type' => CRM_Utils_Type::T_INT,
            'title' => E::ts('Created By Contact ID'),
            'description' => E::ts('FK to contact table.'),
            'where' => 'civicrm_saved_search.created_id',
            'table_name' => static::getTableName(),
            'entity' => self::$_entityType,
            'bao' => 'CRM_Eck_DAO_Entity',
            'localizable' => 0,
            'readonly' => TRUE,
            'FKClassName' => 'CRM_Contact_DAO_Contact',
            'html' => [
              'type' => 'EntityRef',
              'label' => E::ts("Created By"),
            ],
          ],
          'modified_id' => [
            'name' => 'modified_id',
            'type' => CRM_Utils_Type::T_INT,
            'title' => E::ts('Modified By Contact ID'),
            'description' => E::ts('FK to contact table.'),
            'where' => 'civicrm_saved_search.modified_id',
            'table_name' => static::getTableName(),
            'entity' => self::$_entityType,
            'bao' => 'CRM_Eck_DAO_Entity',
            'localizable' => 0,
            'readonly' => TRUE,
            'FKClassName' => 'CRM_Contact_DAO_Contact',
            'html' => [
              'type' => 'EntityRef',
              'label' => E::ts("Modified By"),
            ],
          ],
          'created_date' => [
            'name' => 'created_date',
            'type' => CRM_Utils_Type::T_TIMESTAMP,
            'title' => E::ts('Created Date'),
            'description' => E::ts('When the record was created.'),
            'required' => TRUE,
            'where' => 'civicrm_saved_search.created_date',
            'default' => 'CURRENT_TIMESTAMP',
            'table_name' => static::getTableName(),
            'entity' => self::$_entityType,
            'bao' => 'CRM_Eck_DAO_Entity',
            'localizable' => 0,
            'readonly' => TRUE,
          ],
          'modified_date' => [
            'name' => 'modified_date',
            'type' => CRM_Utils_Type::T_TIMESTAMP,
            'title' => E::ts('Modified Date'),
            'description' => E::ts('When the record was last modified.'),
            'required' => TRUE,
            'where' => 'civicrm_saved_search.modified_date',
            'default' => 'CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
            'table_name' => static::getTableName(),
            'entity' => self::$_entityType,
            'bao' => 'CRM_Eck_DAO_Entity',
            'localizable' => 0,
            'readonly' => TRUE,
          ],
        ];
        CRM_Core_DAO_AllCoreTables::invoke(
          self::$_className,
          'fields_callback',
          Civi::$statics[self::$_className]['fields']
        );
      }
      else {
        Civi::$statics[self::$_className]['fields'] = [];
      }
    }
    return Civi::$statics[self::$_className]['fields'];
  }

  /**
   * {@inheritDoc}
   */
  public static function writeRecord(array $record): CRM_Core_DAO {
    $hook = empty($record['id']) ? 'create' : 'edit';

    \CRM_Utils_Hook::pre($hook, 'Eck_' . $record['entity_type'], $record['id'] ?? NULL, $record);
    $instance = new self($record['entity_type']);
    $instance->copyValues($record);
    $instance->save();

    // Store custom field values.
    if (!empty($record['custom']) && is_array($record['custom'])) {
      CRM_Core_BAO_CustomValueTable::store($record['custom'], $instance->tableName(), $instance->id);
    }

    \CRM_Utils_Hook::post($hook, 'Eck_' . $record['entity_type'], $instance->id, $instance);

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public static function deleteRecord(array $record) {
    if (empty($record['entity_type'])) {
      throw new CRM_Core_Exception("Eck entity type not specified.");
    }
    $entityName = 'Eck_' . $record['entity_type'];
    if (empty($record['id'])) {
      throw new CRM_Core_Exception("Cannot delete {$entityName} with no id.");
    }
    CRM_Utils_Type::validate($record['id'], 'Positive');

    CRM_Utils_Hook::pre('delete', $entityName, $record['id'], $record);
    $instance = new self($record['entity_type']);
    $instance->id = $record['id'];
    // Load complete object for the sake of hook_civicrm_post, below
    $instance->find(TRUE);
    if (!$instance || !$instance->delete()) {
      throw new CRM_Core_Exception("Could not delete {$entityName} id {$record['id']}");
    }
    // For other operations this hook is passed an incomplete object and hook listeners can load if needed.
    // But that's not possible with delete because it's gone from the database by the time this hook is called.
    // So in this case the object has been pre-loaded so hook listeners have access to the complete record.
    CRM_Utils_Hook::post('delete', $entityName, $record['id'], $instance);

    return $instance;
  }

}
