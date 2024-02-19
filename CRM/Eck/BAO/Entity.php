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
use Civi\Core\HookInterface;
use Civi\Core\Event\PostEvent;
use Civi\Api4\RecentItem;
use Civi\Eck\Permissions;

class CRM_Eck_BAO_Entity extends CRM_Eck_DAO_Entity implements HookInterface {

  public static function getEntityType($entityName): ?string {
    return strpos($entityName, 'Eck_') === 0 ? substr($entityName, strlen('Eck_')) : NULL;
  }

  /**
   * @param string $entityName
   * @param int|null $entityId
   * @return string
   */
  public static function getEntityIcon(string $entityName, int $entityId = NULL): ?string {
    $default = self::$_icon;
    foreach (\CRM_Eck_BAO_EckEntityType::getEntityTypes() as $entity_type) {
      if ($entity_type['entity_name'] === $entityName) {
        $default = $entity_type['icon'] ?? $default;
        break;
      }
    }
    if (!$entityId) {
      return $default;
    }
    $record = civicrm_api4($entityName, 'get', [
      'checkPermissions' => FALSE,
      'select' => ['subtype:icon'],
      'where' => [['id', '=', $entityId]],
    ], 0);
    return $record['subtype:icon'] ?? $default;
  }

  /**
   * Implements hook_civicrm_post().
   *
   * @see CRM_Utils_Hook::post()
   * @link https://docs.civicrm.org/dev/en/latest/hooks/hook_civicrm_post
   */
  public static function on_hook_civicrm_post(PostEvent $event): void {
    // Add the recently created Entity to the list of recently viewed items.
    if (
      strpos($event->entity, 'Eck_') === 0
      && in_array($event->action, ['create', 'edit'], TRUE)
      && (CRM_Eck_BAO_EckEntityType::getEntityType(substr($event->entity, 4))['in_recent'] ?? FALSE)
    ) {
      RecentItem::create()
        ->addValue('entity_type', $event->entity)
        ->addValue('entity_id', $event->id)
        ->execute();
    }
  }

  /**
   * Access callback for /civicrm/eck/entity and /civicrm/eck/entity/view
   * routes.
   *
   * @param array|int $args
   * @param string|null $op
   *
   * @return bool
   */
  public static function checkMenuAccess($args, ?string $op = 'and'): bool {
    // In order to not check nested paths (which are Afforms), we pass an access
    // argument of "checkMenuAccess" in the menu XML and check it here, as this
    // callback feels responsible only for the exact routes, not nested ones.
    if (in_array('checkMenuAccess', $args)) {
      $type = CRM_Utils_Request::retrieve('type', 'String', NULL, TRUE);
      $eckPermissions = [
        Permissions::ADMINISTER_ECK_ENTITIES,
        Permissions::VIEW_ANY_ECK_ENTITY,
        Permissions::getTypePermissionName(Permissions::ACTION_VIEW, $type),
      ];
      return CRM_Core_Permission::checkMenu($eckPermissions, 'or');
    }

    return CRM_Core_Permission::checkMenu($args, $op);
  }

}
