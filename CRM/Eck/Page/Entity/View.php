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

use Civi\Api4\EckEntityType;
use Civi\Api4\OptionValue;
use CRM_Eck_ExtensionUtil as E;

class CRM_Eck_Page_Entity_View extends CRM_Core_Page {

  /**
   * @var int
   *
   * The id of the entity we are processing.
   */
  public $_id;

  /**
   * @var string
   *
   * The ECK entity type name of the entity we are processing.
   */
  public string $_entityTypeName;

  /**
   * @var mixed[]
   *
   * The entity type of the entity we are processing.
   *
   */
  public array $_entityType;

  public function __construct($title = NULL, $mode = NULL) {
    parent::__construct($title, $mode);

    // Retrieve ECK entity type.
    $entity_type_name = CRM_Utils_Request::retrieve('type', 'String', $this);
    if (!is_string($entity_type_name)) {
      throw new CRM_Core_Exception('No ECK entity type given.');
    }
    $this->_entityTypeName = $entity_type_name;
    try {
      $this->_entityType = EckEntityType::get(FALSE)->addWhere('name', '=', $entity_type_name)->execute()->single();
    }
    catch (Exception $exception) {
      throw new CRM_Core_Exception(E::ts('Invalid ECK entity type.'), 0, [], $exception);
    }

    // Retrieve entity ID.
    $entity_id = CRM_Utils_Request::retrieve('id', 'Integer', $this);
    if (!is_int($entity_id)) {
      throw new CRM_Core_Exception('No entity ID given.');
    }
    $this->_id = $entity_id;
  }

  public function run(): void {
    $this->assign('entity_type', $this->_entityType);

    // Retrieve ECK entity using the API.
    $entity = \Civi\Api4\EckEntity::get($this->_entityTypeName)
      ->addWhere('id', '=', $this->_id)
      ->execute()
      ->single();
    // Retrieve fields.
    $fields = \Civi\Api4\EckEntity::getFields($this->_entityTypeName)
      ->addWhere('type', '=', 'Field')
      ->execute()
      ->indexBy('name');
    $this->assign('fields', $fields);

    // Set page title.
    CRM_Utils_System::setTitle($entity['title']);

    // Retrieve and build custom data view.
    $custom_group_tree = CRM_Core_BAO_CustomGroup::getTree(
      'Eck_' . $this->_entityTypeName,
      [],
      $this->_id,
      NULL,
      [$entity['subtype']],
      NULL,
      FALSE,
      NULL,
      FALSE,
      CRM_Core_Permission::VIEW
    );

    /*
     * $custom_group_tree contains the option IDs for option fields. Though
     * CRM_Core_BAO_CustomGroup::buildCustomDataView() needs the values, not the
     * IDs. Thus, we have to convert them otherwise no value is displayed for
     * the field.
     * @todo: Migrate to SearchKit.
     */
    $this->convertOptionIdsToValues($custom_group_tree);

    CRM_Core_BAO_CustomGroup::buildCustomDataView(
      $this,
      $custom_group_tree,
      FALSE,
      NULL,
      NULL,
      NULL,
      $this->_id
    );

    // Replace subtype value with its name.
    $subtypes = CRM_Eck_BAO_EckEntityType::getSubTypes($this->_entityTypeName);
    $entity['subtype'] = $subtypes[$entity['subtype']];

    $this->assign('entity', $entity);

    // Add to recent items
    if (FALSE === (bool) $this->_entityType['in_recent']) {
      \Civi\Api4\RecentItem::create()
        ->addValue('entity_type', 'Eck_' . $this->_entityTypeName)
        ->addValue('entity_id', $this->_id)
        ->execute();
    }

    parent::run();
  }

  /**
   * @param array<string,array{fields:array{customValue:array{data:mixed},serialize?:bool}}> $custom_group_tree
   *
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  private function convertOptionIdsToValues(array &$custom_group_tree): void {
    foreach ($custom_group_tree as $key => &$group) {
      if ('info' === $key) {
        continue;
      }

      foreach ($group['fields'] as &$field) {
        if (!isset($field['option_group_id'])) {
          continue;
        }

        foreach ($field['customValue'] as &$custom_value) {
          $serialize = (bool) $field['serialize'];
          $option_ids = $serialize
            ? CRM_Utils_Array::explodePadded($custom_value['data'])
            : (array) $custom_value['data'];

          if ([] !== $option_ids) {
            $option_values = OptionValue::get(FALSE)
              ->addSelect('value')
              ->addWhere('id', 'IN', $option_ids)
              ->execute()
              ->column('value');

            $custom_value['data'] = $serialize
              ? CRM_Utils_Array::implodePadded($option_values)
              : reset($option_values);
          }
        }
      }
    }
  }

}
