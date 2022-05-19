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
use Civi\Core\Event\GenericHookEvent;
use Civi\API\Provider\ProviderInterface as API_ProviderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Entity implements API_ProviderInterface, EventSubscriberInterface {

  /**
   * @return callable[]
   */
  public static function getSubscribedEvents():array {
    return [
      'civi.api4.entityTypes' => ['onApi4EntityTypes', Events::W_EARLY],
      'civi.afform_admin.metadata' => 'afformEntityTypes',
      'civi.afform.get' => 'getEckAfforms',
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
        'paths' => [
          'browse' => "civicrm/eck/entity/list?reset=1&type={$entity_type['name']}&id=[id]",
          'view' => "civicrm/eck/entity?reset=1&action=view&type={$entity_type['name']}&id=[id]",
          'update' => "civicrm/eck/entity/edit/{$entity_type['name']}/[subtype:name]#?{$entity_type['entity_name']}=[id]",
        ],
        'class' => 'Civi\Api4\EckEntity',
        'icon' => $entity_type['icon'] ?? 'fa-cubes',
      ];
    }
  }

  /**
   * Make ECK entities available to Form Builder
   *
   * @param GenericHookEvent $e
   */
  public static function afformEntityTypes(GenericHookEvent $e) {
    foreach (\CRM_Eck_BAO_EckEntityType::getEntityTypes() as $entityType) {
      $e->entities[$entityType['entity_name']] = [
        'entity' => $entityType['entity_name'],
        'label' => $entityType['label'],
        'icon' => $entityType['icon'],
        'type' => 'primary',
        'defaults' => '{}',
      ];
    }
  }

  /**
   * Generates afforms for each ECK entity type and sub-type.
   *
   * @param \Civi\Core\Event\GenericHookEvent $event
   * @throws \API_Exception
   */
  public static function getEckAfforms($event) {
    // Early return if forms are not requested
    if ($event->getTypes && !in_array('form', $event->getTypes, TRUE)) {
      return;
    }

    $afforms =& $event->afforms;
    $getNames = $event->getNames;

    // Early return if this api call is fetching afforms by name and those names are not eck-related
    if (
      (!empty($getNames['name']) && !strstr(implode(' ', $getNames['name']), 'afformEck_'))
      || (!empty($getNames['module_name']) && !strstr(implode(' ', $getNames['module_name']), 'afformEck'))
      || (!empty($getNames['directive_name']) && !strstr(implode(' ', $getNames['directive_name']), 'afform-eck'))
    ) {
      return;
    }

    foreach (\CRM_Eck_BAO_EckEntityType::getEntityTypes() as $entityType) {
      foreach (\CRM_Eck_BAO_EckEntityType::getSubTypes($entityType['name'], FALSE) as $subType) {
        $name = 'afform' . $entityType['entity_name'] . '_' . $subType['name'];
        $item = [
          'name' => $name,
          'type' => 'form',
          'title' => $entityType['label'] . ' (' . $subType['label'] . ')',
          'description' => '',
          'is_dashlet' => FALSE,
          'is_public' => FALSE,
          'is_token' => FALSE,
          'permission' => 'access CiviCRM',
          'server_route' => "civicrm/eck/entity/edit/{$entityType['name']}/{$subType['name']}",
        ];
        if ($event->getLayout) {
          $fields = \civicrm_api4($entityType['entity_name'], 'getFields', [
            'values' => ['subtype' => $subType['value']],
            'select' => ['name'],
            'where' => [
              ['readonly', 'IS EMPTY'],
              ['input_type', 'IS NOT EMPTY'],
              // Don't allow subtype to be changed on the form, since this form is specific to subtype
              ['name', '!=', 'subtype'],
            ],
          ]);
          $item['layout'] = "<af-form ctrl=\"afform\">\n";
          $item['layout'] .= '  <af-entity type="' . $entityType['entity_name'] . '" name="' . $entityType['entity_name'] . '" label="' . $subType['label'] . '" actions="{create: true, update: true}" security="RBAC" url-autofill="1" data="{subtype: \'' . $subType['value'] . '\'}" />' . "\n";
          $item['layout'] .= '  <fieldset af-fieldset="' . $entityType['entity_name'] . '" class="af-container" af-title="' . $subType['label'] . '">' . "\n";
          foreach ($fields as $field) {
            $item['layout'] .= "    <af-field name=\"{$field['name']}\" />\n";
          }
          $item['layout'] .= "  </fieldset>\n";
          $item['layout'] .= '  <button class="af-button btn btn-primary" crm-icon="fa-check" ng-click="afform.submit()">' . E::ts('Submit') . '</button>' . "\n";
          $item['layout'] .= '</af-form>';
        }
        $afforms[$name] = $item;
      }
    }
  }

  /**
   * Not needed for APIv4
   */
  public function invoke($apiRequest) {
  }

}
