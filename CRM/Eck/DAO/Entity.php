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

  public function __construct($entityType = NULL) {
    if (!isset($entityType)) {
      throw new Exception(E::ts('No ECK entity type given.'));
    }
    static::$_entityType = $entityType;
    parent::__construct();
  }

  /**
   * {@inheritDoc}
   */
  public function initialize() {
    $entity_type = civicrm_api3('EckEntityType', 'getsingle', ['name' => static::$_entityType]);
    static::$_className = 'CRM_Eck_DAO_' . $entity_type['name'];
    static::$_tableName = 'civicrm_eck_' . strtolower($entity_type['name']);

    parent::initialize();
  }

  /**
   * {@inheritDoc}
   */
  public static function getTableName() {
    return self::getLocaleTableName(static::$_tableName ?? NULL);
  }

  /**
   * {@inheritDoc}
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[static::$_className]['fieldKeys'])) {
      Civi::$statics[static::$_className]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', static::fields()));
    }
    return Civi::$statics[static::$_className]['fieldKeys'];
  }

  /**
   * {@inheritDoc}
   */
  public static function &fields() {
    if (!isset(Civi::$statics[static::$_className]['fields'])) {
      Civi::$statics[static::$_className]['fields'] = [
        'id' => [
          'name' => 'id',
          'title' => E::ts('ID'),
          'type' => CRM_Utils_Type::T_INT,
          'description' => E::ts('The unique entity ID.'),
          'required' => TRUE,
          'where' => static::getTableName() . '.id',
          'export' => TRUE,
          'table_name' => static::getTableName(),
          'entity' => static::$_entityType,
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
          'entity' => static::$_entityType,
          'bao' => 'CRM_Eck_DAO_Entity',
          'localizable' => 1,
          'add' => '4.3',
          'html' => [
            'type' => 'Text',
          ],
        ],
      ];
      // TODO: Dynamically define configurable "base property" fields (as
      //   opposed to custom fields) per entity type.

      CRM_Core_DAO_AllCoreTables::invoke(static::$_className, 'fields_callback', Civi::$statics[static::$_className]['fields']);
    }
    return Civi::$statics[static::$_className]['fields'];
  }

  /**
   * Fetch object based on array of properties.
   *
   * @param string $entityType
   *   Name of the ECK entity type.
   * @param array $params
   *   (reference ) an assoc array of name/value pairs.
   * @param array $defaults
   *   (reference ) an assoc array to hold the flattened values.
   * @param array $returnProperities
   *   An assoc array of fields that need to be returned, eg array( 'first_name', 'last_name').
   *
   * @return object
   *   an object of type referenced by daoName
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
   * Fetch object based on array of properties.
   *
   * @param string $entityType
   *   Name of the ECK entity type.
   * @param string $fieldIdName
   * @param int $fieldId
   * @param $details
   * @param array $returnProperities
   *   An assoc array of fields that need to be returned, eg array( 'first_name', 'last_name').
   *
   * @return object
   *   an object of type referenced by daoName
   */
  public static function commonRetrieveAll($entityType, $fieldIdName = 'id', $fieldId, &$details, $returnProperities = NULL) {
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
   * @param string $tableAlias
   * @param string $entity_type
   * @return array
   *
   * @see CRM_Core_DAO::getSelectWhereClause()
   */
  public static function getSelectWhereClause($tableAlias = NULL, $entity_type = NULL) {
    if (!isset($entity_type)) {
      throw new Exception(E::ts('No ECK entity type given.'));
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

  public static function getEntityType($entityName) {
    return strpos($entityName, 'Eck') === 0 ? substr($entityName, strlen('Eck')) : NULL;
  }

}
