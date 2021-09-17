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

/**
 *
 */
class CRM_Eck_DAO_Entity extends CRM_Core_DAO {

  public function initialize() {
    parent::initialize();

    // TODO: Dynamically set entity type name.
    static::$_entityType = 'EckFoobar';

    // TODO: Dynamically set entity class name.
    static::$_className = 'CRM_Eck_DAO_Foobar';

    // TODO: Dynamically set table name.
    static::$_tableName = 'civicrm_eck_foobar';

    // TODO: Dynamically set logging flag.
    static::$_log = TRUE;
  }

  /**
   * Return a mapping from field-name to the corresponding key (as used in fields()).
   *
   * @return array
   *   Array(string $name => string $uniqueName).
   */
  public static function &fieldKeys() {
    if (!isset(Civi::$statics[static::$_className]['fieldKeys'])) {
      Civi::$statics[static::$_className]['fieldKeys'] = array_flip(CRM_Utils_Array::collect('name', static::fields()));
    }
    return Civi::$statics[static::$_className]['fieldKeys'];
  }

  public static function &fields() {
    if (!isset(Civi::$statics[static::$_className]['fields'])) {
      Civi::$statics[static::$_className]['fields'] = [
        'id' => [
          'name' => 'id',
          'type' => CRM_Utils_Type::T_INT,
          'description' => E::ts('ID'),
          'required' => TRUE,
          'where' => static::getTableName() . '.id',
          'export' => TRUE,
          'table_name' => static::getTableName(),
          'entity' => static::$entityType,
          'bao' => 'CRM_Eck_DAO_Entity',
          'localizable' => 0,
          'add' => '4.3',
        ],
      ];
      // TODO: Dynamically define fields.

      CRM_Core_DAO_AllCoreTables::invoke(static::$_className, 'fields_callback', Civi::$statics[static::$_className]['fields']);
    }
    return Civi::$statics[static::$_className]['fields'];
  }

}
