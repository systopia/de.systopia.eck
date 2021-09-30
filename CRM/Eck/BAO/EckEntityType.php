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

class CRM_Eck_BAO_EckEntityType extends CRM_Eck_DAO_EckEntityType {

  /**
   * Create a new EckEntityType based on array-data
   *
   * @param array $params key-value pairs
   * @return CRM_Eck_DAO_EckEntityType|NULL
   *
  public static function create($params) {
    $className = 'CRM_Eck_DAO_EckEntityType';
    $entityName = 'EckEntityType';
    $hook = empty($params['id']) ? 'create' : 'edit';

    CRM_Utils_Hook::pre($hook, $entityName, CRM_Utils_Array::value('id', $params), $params);
    $instance = new $className();
    $instance->copyValues($params);
    $instance->save();
    CRM_Utils_Hook::post($hook, $entityName, $instance->id, $instance);

    return $instance;
  } */

  /**
   * Retrieves a list of sub types for the given entity type.
   *
   * @param string $entity_type_name
   *   The name of the entity type to retrieve a list of sub types for.
   *
   * @return array
   *   A list of sub types for the given entity type.
   */
  public static function getSubTypes($entity_type_name) {
    // TODO: Retrieve sub types for the given entity type.
    return [];
  }

  /**
   * Evaluate call of non-existing static method.
   *
   * The method to call must be prefixed with an existing ECK entity type,
   * separated by "." (period) in order to be executed.
   *
   * This is needed for resolving entity sub types for custom groups due to a
   * bogus implementation of calling methods, i.e. no arguments be passable via
   * a custom group's configuration.
   * Since sub types are dependent on the entity type, we pass that as part of
   * the static method name, which is being resolved here.
   * @see \CRM_Core_BAO_CustomGroup::getExtendedObjectTypes()
   *
   * @param $funName
   * @param $arguments
   *
   * @return mixed
   */
  public static function __callStatic($funName, $arguments) {
    $allowed_methods = [
      'getSubTypes',
    ];
    [$entity_type, $method] = explode('.', $funName);
    if (
      self::objectExists($entity_type, __CLASS__, TRUE)
      && method_exists(__CLASS__, $method)
      && in_array($method, $allowed_methods)
    ) {
      return self::$method($arguments);
    }
    else {
      trigger_error(
        'Call to undefined method '.__CLASS__.'::'.$funName.'()',
        E_USER_ERROR
      );
    }
  }

}
