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

namespace Civi\Eck\API;

use CRM_Eck_ExtensionUtil as E;
use Civi\API\Events;
use Civi\Api4\Event\CreateApi4RequestEvent;
use Civi\Core\Event\GenericHookEvent;
use Civi\API\Provider\ProviderInterface as API_ProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Entity implements API_ProviderInterface, EventSubscriberInterface {

  /**
   * @return callable[]
   */
  public static function getSubscribedEvents():array {
    return [
      // TODO: "civi.api4.createRequest" will be removed in CiviCRM Core, see
      //       https://github.com/civicrm/civicrm-core/pull/23311.
      //       The "civi.api4.entityTypes" event provides all necessary
      //       information.
      'civi.api4.createRequest' => [['onApi4CreateRequest', Events::W_EARLY]],
      'civi.api4.entityTypes' => [['onApi4EntityTypes', Events::W_EARLY]],
    ];
  }

  /**
   * Not needed for APIv4
   * @param int $version
   * @return array
   */
  public function getEntityNames($version) {
    return [];
  }

  /**
   * Not needed for APIv4
   * @param int $version
   * @param string $entity
   * @return array
   */
  public function getActionNames($version, $entity) {
    return [];
  }

  /**
   * Register each EckEntityType as an APIv4 entity.
   *
   * Callback for `civi.api4.entityTypes` event.
   *
   * @param GenericHookEvent $event
   */
  public function onApi4EntityTypes(GenericHookEvent $event) {
    foreach (\CRM_Eck_BAO_EckEntityType::getEntityTypes() as $entity_type) {
      $event->entities[$entity_type['entity_name']] = [
        'name' => $entity_type['entity_name'],
        'title' => $entity_type['label'],
        'title_plural' => $entity_type['label'],
        'description' => ts('Entity Construction Kit entity type %1', [1 => $entity_type['label']]),
        'primary_key' => ['id'],
        'type' => ['EckEntity'],
        'table_name' => $entity_type['table_name'],
        'class_args' => [$entity_type['name']],
        'label_field' => 'title',
        'searchable' => 'secondary',
        'paths' => [],
        'class' => 'Civi\Api4\EckEntity',
        'icon' => $entity_type['icon'] ?? 'fa-cubes',
      ];
    }
  }

  /**
   * Callback for `civi.api4.createRequest` event.
   *
   * @param CreateApi4RequestEvent $event
   *
   * @deprecated
   *   The "civi.api4.createRequest" event will be removed in CiviCRM Core, see
   *   https://github.com/civicrm/civicrm-core/pull/23311.
   *   The "civi.api4.entityTypes" event provides all necessary information.
   */
  public function onApi4CreateRequest(CreateApi4RequestEvent $event) {
    if (strpos($event->entityName, 'Eck_') === 0) {
      $entity_type = substr($event->entityName, strlen('Eck_'));
      if (
        in_array($entity_type, \CRM_Eck_BAO_EckEntityType::getEntityTypeNames())
      ) {
        $event->className = 'Civi\Api4\EckEntity';
        $event->args = [$entity_type];
      }
    }
  }

  /**
   * Not needed for APIv4
   */
  public function invoke($apiRequest) {
  }

}
