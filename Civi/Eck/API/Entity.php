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
use Civi\Api4\EckEntity;
use Civi\API\Event\ResolveEvent;
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
      'civi.api4.createRequest' => [['onApi4CreateRequest', Events::W_EARLY]],
      'civi.api.resolve' => 'onApiResolve',
      'civi.api4.entityTypes' => [['onApi4EntityTypes', Events::W_EARLY]],
    ];
  }

  /**
   * @param int $version
   *   API version.
   * @return string[]
   */
  public function getEntityNames($version) {
    return array_column(\CRM_Eck_BAO_EckEntityType::getEntityTypes(), 'entity_name');
  }

  /**
   * @param int $version
   *   API version.
   * @param string $entity
   *   API entity.
   * @return array<string>
   */
  public function getActionNames($version, $entity) {
    $actions = [];
    if (in_array($entity, static::getEntityNames($version))) {
      $actions[] = 'get';
    }
    return $actions;
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
      ];
    }
  }

  /**
   * Callback for `civi.api4.createRequest` event.
   *
   * @param CreateApi4RequestEvent $event
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

  public function onApiResolve(ResolveEvent $event) {
    $apiRequest = $event->getApiRequest();
    if (
      strpos($apiRequest['entity'], 'Eck_') === 0 &&
      in_array($apiRequest['entity'], static::getEntityNames($apiRequest['version']))
    ) {
      $event->setApiProvider($this);
      $apiRequest = $event->getApiRequest();

      // TODO: Copied this from Civi\FormProcessor\API\FormProcessor - is this needed?
      if (
        strtolower($apiRequest['action']) == 'getfields'
        || strtolower($apiRequest['action']) == 'getoptions'
      ) {
        $event->stopPropagation();
      }
    }
  }

  /**
   * @param array $apiRequest
   *   The full description of the API request.
   * @return array
   *   structured response data (per civicrm_api3_create_success)
   * @see civicrm_api3_create_success
   * @throws \Exception
   */
  public function invoke($apiRequest) {
    switch (strtolower($apiRequest['action'])) {
      case 'get':
        return $this->invokeGet($apiRequest);
    }
  }

  public function invokeGet($apiRequest) {
    switch ($apiRequest['version']) {
      case 3:
        $bao_name = 'CRM_Eck_BAO_Entity';
        $entity = $apiRequest['entity'];
        $params = $apiRequest['params'];
        /**
         * Copied and adapted from _civicrm_api3_basic_get().
         * We need to always pass the ECK entity type into the DAO constructor
         * and static methods where objects are being instantiated.
         * Therefore, we need our own Api3SelectQuery class and override some
         * static methods in CRM_Eck_DAO_Entity.
         * @see \Civi\Eck\API\Api3SelectQuery
         * @see \CRM_Eck_DAO_Entity::getSelectWhereClause()
         */
        $entity = $entity ?: CRM_Core_DAO_AllCoreTables::getBriefName($bao_name);
        $options = _civicrm_api3_get_options_from_params($params);

        // Skip query if table doesn't exist yet due to pending upgrade
        if (!$bao_name::tableHasBeenAdded()) {
          \Civi::log()->warning("Could not read from {$entity} before table has been added. Upgrade required.", ['civi.tag' => 'upgrade_needed']);
          $result = [];
        }
        else {
          $query = new Api3SelectQuery($entity, $params['check_permissions'] ?? FALSE);
          $query->where = $params;
          if ($options['is_count']) {
            $query->select = ['count_rows'];
          }
          else {
            $query->select = array_keys(array_filter($options['return']));
            $query->orderBy = $options['sort'];
            $query->isFillUniqueFields = FALSE;
          }
          $query->limit = $options['limit'];
          $query->offset = $options['offset'];
          $result = $query->run();
        }
        $result = civicrm_api3_create_success($result, $params, $entity, 'get');
        break;

      case 4:
        $result = EckEntity::get($apiRequest['params']['check_permissions'], $apiRequest['entity']);
        break;
    }
    return $result;
  }

}
