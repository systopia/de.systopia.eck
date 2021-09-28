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
use \Civi\API\Event\ResolveEvent;
use Civi\API\Provider\ProviderInterface as API_ProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class EntityType implements API_ProviderInterface, EventSubscriberInterface {

  protected static $_entityTypes;

  public static function getSubscribedEvents() {
    return [
      'civi.api.resolve' => 'onApiResolve',
    ];
  }
  /**
   * @param int $version
   *   API version.
   * @return array<string>
   */
  public function getEntityNames($version) {
    return static::getEntityTypes();
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
    if (in_array($entity, static::getEntityTypes())) {
      $actions[] = 'get';
    }
    return $actions;
  }

  public static function getEntityTypes() {
    if (!isset(static::$_entityTypes)) {
      static::$_entityTypes = array_map(
        function($name) {
          return 'Eck' . $name;
        },
        array_column(
          civicrm_api3('EckEntityType', 'get', [], ['limit' => 0])['values'],
          'name'
        )
      );
    }
    return static::$_entityTypes;
  }

  public function onApiResolve(ResolveEvent $event) {
    if (in_array($event->getApiRequest()['entity'], static::getEntityTypes())) {
      $event->setApiProvider($this);
      $apiRequest = $event->getApiRequest();

      // TODO: Copied this from Civi\FormProcessor\API\FormProcessor - is this needed?
      if (strtolower($apiRequest['action']) == 'getfields' || strtolower($apiRequest['action']) == 'getoptions') {
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
        break;
    }
  }

  public function invokeGet($apiRequest) {
    // TODO: Implement.
  }

}
