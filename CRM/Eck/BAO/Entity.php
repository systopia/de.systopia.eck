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

  public static function getEntityType(string $entityName): ?string {
    return str_starts_with($entityName, 'Eck_') ? substr($entityName, strlen('Eck_')) : NULL;
  }

  /**
   * @param string $entityName
   * @param int|null $entityId
   * @return string
   */
  public static function getEntityIcon(string $entityName, ?int $entityId = NULL): string {
    $entityTypes = \CRM_Eck_BAO_EckEntityType::getEntityTypes();
    $default = $entityTypes[$entityName]['icon'] ?? self::$_icon;
    if (!isset($entityId)) {
      return $default;
    }
    $record = \Civi\Api4\EckEntity::get($entityTypes[$entityName]['name'], FALSE)
      ->addSelect('subtype:icon')
      ->addWhere('id', '=', $entityId)
      ->execute()
      ->first();
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
      str_starts_with($event->entity, 'Eck_')
      && in_array($event->action, ['create', 'edit'], TRUE)
      && (CRM_Eck_BAO_EckEntityType::getEntityType(substr($event->entity, 4))['in_recent'] ?? FALSE)
    ) {
      RecentItem::create(FALSE)
        ->addValue('entity_type', $event->entity)
        ->addValue('entity_id', $event->id)
        ->execute();
    }
  }

  /**
   * Access callback for /civicrm/eck/entity and /civicrm/eck/entity/view
   * routes.
   *
   * @param array<'checkMenuAccess'> $args
   * @param string|null $op
   *
   * @return bool
   */
  public static function checkMenuAccess(array $args, ?string $op = 'and'): bool {
    // In order to not check nested paths (which are Afforms), we pass an access
    // argument of "checkMenuAccess" in the menu XML and check it here, as this
    // callback feels responsible only for the exact routes, not nested ones.
    if (in_array('checkMenuAccess', $args, TRUE)) {
      $null = NULL;
      $type = CRM_Utils_Request::retrieve('type', 'String', $null);
      if (!is_string($type)) {
        throw new CRM_Core_Exception(E::ts('Error retrieving ECK entity type from request.'));
      }
      $eckPermissions = [
        Permissions::ADMINISTER_ECK_ENTITIES,
        Permissions::VIEW_ANY_ECK_ENTITY,
        Permissions::getTypePermissionName(Permissions::ACTION_VIEW, $type),
      ];
      return CRM_Core_Permission::checkMenu($eckPermissions, 'or');
    }

    return CRM_Core_Permission::checkMenu($args, $op ?? 'and');
  }

}
