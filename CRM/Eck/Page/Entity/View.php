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
   *
   * @var int
   */
  public $_id;

  /**
   * @var array
   *
   * The entity type of the entity we are processing.
   *
   * @var int
   */
  public $_entityType;

  public function run() {

    // Retrieve ECK entity type.
    if (!$entity_type_name = CRM_Utils_Request::retrieve('type', 'String', $this)) {
      throw new CRM_Core_Exception('No ECK entity type given.');
    }
    try {
      $entity_type = EckEntityType::get(FALSE)->addWhere('name', '=', $entity_type_name)->execute()->single();
      $this->assign('entity_type', $entity_type);
      $this->_entityType = $entity_type;
    }
    catch (Exception $exception) {
      throw new Exception(E::ts('Invalid ECK entity type.'));
    }

    // Retrieve ECK entity using the API.
    if (!$entity_id = CRM_Utils_Request::retrieve('id', 'Integer', $this)) {
      throw new CRM_Core_Exception('No entity ID given.');
    }
    $this->_id = $entity_id;
    $entity = civicrm_api4('Eck_' . $entity_type_name, 'get', [
      'where' => [['id', '=', $entity_id]],
    ])->single();
    // Retrieve fields.
    $fields = civicrm_api4('Eck_' . $entity_type_name, 'getfields', [
      'where' => [['type', '=', 'Field']],
    ], 'name');
    $this->assign('fields', $fields);

    // Set page title.
    CRM_Utils_System::setTitle($entity['title']);

    // Retrieve and build custom data view.
    $custom_group_tree = CRM_Core_BAO_CustomGroup::getTree(
      'Eck_' . $entity_type_name,
      [],
      $entity_id,
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
    $custom_group_tree = $this->convertOptionIdsToValues($custom_group_tree);

    CRM_Core_BAO_CustomGroup::buildCustomDataView(
      $this,
      $custom_group_tree,
      FALSE,
      NULL,
      NULL,
      NULL,
      $entity_id
    );

    // Replace subtype value with its name.
    $subtypes = CRM_Eck_BAO_EckEntityType::getSubTypes($entity_type_name);
    $entity['subtype'] = $subtypes[$entity['subtype']];

    $this->assign('entity', $entity);

    // Add to recent items
    if (!empty($entity_type['in_recent'])) {
      \Civi\Api4\RecentItem::create()
        ->addValue('entity_type', 'Eck_' . $entity_type_name)
        ->addValue('entity_id', $entity_id)
        ->execute();
    }

    parent::run();
  }

  private function convertOptionIdsToValues(array $custom_group_tree): array {
    foreach ($custom_group_tree as $key => &$group) {
      if ('info' === $key) {
        continue;
      }

      foreach ($group['fields'] as &$field) {
        if (!isset($field['option_group_id'])) {
          continue;
        }

        foreach ($field['customValue'] as &$custom_value) {
          $option_ids = empty($field['serialize']) ? (array) $custom_value['data']
            : CRM_Utils_Array::explodePadded($custom_value['data']);

          if ([] !== $option_ids) {
            $option_values = OptionValue::get(FALSE)
              ->addSelect('value')
              ->addWhere('id', 'IN', $option_ids)
              ->execute()
              ->column('value');

            if (empty($field['serialize'])) {
              $custom_value['data'] = reset($option_values);
            }
            else {
              $custom_value['data'] = CRM_Utils_Array::implodePadded($option_values);
            }
          }
        }
      }
    }

    return $custom_group_tree;
  }

}
