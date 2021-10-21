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
use \Civi\Core\Event\GenericHookEvent;
use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CRM_Eck_BAO_EckEntityType extends CRM_Eck_DAO_EckEntityType implements EventSubscriberInterface {

  protected static $_entityTypes;

  public static function getEntityTypes() {
    if (!isset(static::$_entityTypes)) {
      $entity_types = civicrm_api3('EckEntityType', 'get', [], ['limit' => 0])['values'];
      static::$_entityTypes = array_combine(
        array_map(
          function ($name) {
            return 'Eck' . $name;
          },
          array_column($entity_types, 'name')
        ),
        $entity_types
      );
    }
    return static::$_entityTypes;
  }

  public static function getEntityTypeNames() {
    return array_column(self::getEntityTypes(), 'name');
  }

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
