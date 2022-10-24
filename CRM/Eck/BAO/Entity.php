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

class CRM_Eck_BAO_Entity extends CRM_Eck_DAO_Entity {

  public static function getEntityType($entityName) {
    return strpos($entityName, 'Eck_') === 0 ? substr($entityName, strlen('Eck_')) : NULL;
  }

  /**
   * @param string $entityName
   * @param int $entityId
   * @return string
   */
  public static function getEntityIcon(string $entityName, int $entityId):?string {
    $default = self::$_icon;
    foreach (\CRM_Eck_BAO_EckEntityType::getEntityTypes() as $entity_type) {
      if ($entity_type['entity_name'] === $entityName) {
        $default = $entity_type['icon'] ?? $default;
      }
    }
    $record = civicrm_api4($entityName, 'get', [
      'checkPermissions' => FALSE,
      'select' => ['subtype:icon'],
      'where' => [['id', '=', $entityId]],
    ], 0);
    return $record['subtype:icon'] ?? $default;
  }

}
