<?php
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

}
