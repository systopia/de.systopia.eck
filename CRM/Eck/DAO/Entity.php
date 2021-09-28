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

  public function __construct($entityType) {
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
          'description' => E::ts('ID'),
          'required' => TRUE,
          'where' => static::getTableName() . '.id',
          'export' => TRUE,
          'table_name' => static::getTableName(),
          'entity' => static::$_entityType,
          'bao' => 'CRM_Eck_DAO_EntityType',
          'localizable' => 0,
          'add' => '4.3',
          'html' => [
            'type' => 'Number',
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

}
