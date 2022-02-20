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
 *
 */
class CRM_Eck_DAO_Entity extends CRM_Core_DAO {

  private static $_entityType;

  private static $_className;

  private static $_tableName;

  public static $_log = TRUE;

  /**
   * Paths for accessing this entity in the UI.
   *
   * @var string[]
   */
  protected static $_paths = [
    'browse' => 'civicrm/eck/entity/list?reset=1&type=[eck_type]&id=[id]',
    'view' => 'civicrm/eck/entity?reset=1&action=view&type=[eck_type]&id=[id]',
    'add' => '', // TODO: Add path when UI is ready.
    'update' => '', // TODO: Add path when UI is ready.
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
   * {@inheritdoc }
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
//      throw new Exception(E::ts('No ECK entity type given.'));
    }
    self::$_entityType = $entityType;
    parent::__construct();
  }

  /**
   * {@inheritDoc}
   */
  public function initialize() {
    if (self::$_entityType) {
      $entity_type = civicrm_api3('EckEntityType', 'getsingle', ['name' => self::$_entityType]);
      self::$_className = 'CRM_Eck_DAO_' . $entity_type['name'];
      self::$_tableName = 'civicrm_eck_' . strtolower($entity_type['name']);
    }

    parent::initialize();
  }

  /**
   * {@inheritDoc}
   */
  public static function getTableName() {
    return self::getLocaleTableName(self::$_tableName ?? NULL);
  }

  /**
   * {@inheritDoc}
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[self::$_className]['fieldKeys'])) {
      Civi::$statics[self::$_className]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', static::fields()));
    }
    return Civi::$statics[self::$_className]['fieldKeys'];
  }

  /**
   * {@inheritDoc}
   */
  public static function &fields() {
    // TODO: This is being called without the constructor being called
    //   beforehand, so this will not always work due to static variables not
    //   being set.
    if (!isset(Civi::$statics[self::$_className]['fields'])) {
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
          'add' => '4.3',
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
          'add' => '4.3',
          'html' => [
            'type' => 'Text',
          ],
        ],
        'subtype' => [
          'name' => 'subtype',
          'title' => E::ts('Subtype'),
          'type' => CRM_Utils_Type::T_STRING,
          'description' => E::ts('The entity subtype.'),
          'required' => FALSE,
          'where' => static::getTableName() . '.subtype',
          'export' => TRUE,
          'table_name' => static::getTableName(),
          'entity' => self::$_entityType,
          'bao' => 'CRM_Eck_DAO_Entity',
          'localizable' => 0,
          'add' => '4.3',
          'html' => [
            'type' => 'Select',
          ],
//          'pseudoconstant' => [
//            'callback' => 'CRM_Eck_Utils_EckEntityType::' . self::$_entityType . '.getSubTypes',
//          ],
        ],
      ];

      CRM_Core_DAO_AllCoreTables::invoke(
        self::$_className,
        'fields_callback',
        Civi::$statics[self::$_className]['fields']
      );
    }
    return Civi::$statics[self::$_className]['fields'];
  }

  /**
   * {@inheritDoc}
   */
  public static function commonRetrieve($entityType, &$params, &$defaults, $returnProperities = NULL) {
    $object = new self($entityType);
    $object->copyValues($params);

    // return only specific fields if returnproperties are sent
    if (!empty($returnProperities)) {
      $object->selectAdd();
      $object->selectAdd(implode(',', $returnProperities));
    }

    if ($object->find(TRUE)) {
      self::storeValues($object, $defaults);
      return $object;
    }
    return NULL;
  }

  /**
   * {@inheritDoc}
   */
  public static function commonRetrieveAll($entityType, $fieldIdName, $fieldId, &$details, $returnProperities = NULL) {
    $object = new self($entityType);
    $object->$fieldIdName = $fieldId;

    // return only specific fields if returnproperties are sent
    if (!empty($returnProperities)) {
      $object->selectAdd();
      $object->selectAdd('id');
      $object->selectAdd(implode(',', $returnProperities));
    }

    $object->find();
    while ($object->fetch()) {
      $defaults = [];
      self::storeValues($object, $defaults);
      $details[$object->id] = $defaults;
    }

    return $details;
  }

  /**
   * {@inheritDoc}
   */
  public static function getSelectWhereClause($tableAlias = NULL, $entity_type = NULL) {
    if (!isset($entity_type)) {
      // TODO: We can't just throw an exception as this leads to errors
      //       everywhere, e.g. for get API actions on entities that reference
      //       at least one ECK entity.
//      throw new Exception(E::ts('No ECK entity type given.'));
    }

    /**
     * Copied and adapted from CRM_Core_DAO::getSelectWhereClause().
     * We need to always pass the ECK entity type into the DAO constructor and
     * static methods where objects are being instantiated.
     * @see \CRM_Eck_DAO_Entity::getSelectWhereClause()
     */
    $bao = new static($entity_type);
    if ($tableAlias === NULL) {
      $tableAlias = $bao->tableName();
    }
    $clauses = [];
    foreach ((array) $bao->addSelectWhereClause() as $field => $vals) {
      $clauses[$field] = NULL;
      if ($vals) {
        $clauses[$field] = "(`$tableAlias`.`$field` IS NULL OR (`$tableAlias`.`$field` " . implode(" AND `$tableAlias`.`$field` ", (array) $vals) . '))';
      }
    }
    return $clauses;
  }

  /**
   * {@inheritDoc}
   */
  public static function writeRecord(array $record): CRM_Core_DAO {
    $hook = empty($record['id']) ? 'create' : 'edit';

    \CRM_Utils_Hook::pre($hook, 'Eck' . $record['entity_type'], $record['id'] ?? NULL, $record);
    $instance = new self($record['entity_type']);
    $instance->copyValues($record);
    $instance->save();
    \CRM_Utils_Hook::post($hook, 'Eck' . $record['entity_type'], $instance->id, $instance);

    // Store custom field values.
    if (!empty($record['custom']) && is_array($record['custom'])) {
      CRM_Core_BAO_CustomValueTable::store($record['custom'], $instance->tableName(), $instance->id);
    }

    return $instance;
  }

  /**
   * {@inheritDoc}
   */
  public static function deleteRecord(array $record) {
    $entityName = 'Eck' . $record['entity_type'];
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
