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

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Eck_Form_EntityType extends CRM_Core_Form {

  protected $_entityTypeName;

  protected $_entityType = [];

  protected $_customGroups = [];

  protected $_subTypes = [];

  /**
   * {@inheritDoc}
   */
  public function preProcess() {
    $this->setAction(CRM_Utils_Request::retrieve('action', 'String', $this, FALSE) ?? 'add');

    if ($this->_action == CRM_Core_Action::ADD) {
      $this->setTitle(E::ts('Add Entity Type'));
    }
    elseif ($this->_action == CRM_Core_Action::UPDATE || $this->_action == CRM_Core_Action::DELETE) {
      if (!($this->_entityTypeName = CRM_Utils_Request::retrieve('type', 'String', $this))) {
        throw new Exception(E::ts('No ECK entity type given.'));
      }
      try {
        $this->_entityType = civicrm_api3(
          'EckEntityType',
          'getsingle',
          ['name' => $this->_entityTypeName]
        );
      }
      catch (Exception $exception) {
        throw new Exception(E::ts('Invalid ECK entity type.'));
      }
      switch ($this->_action) {
        case CRM_Core_Action::UPDATE:
          $this->setTitle(E::ts('Edit Entity Type <em>%1</em>', [1 => $this->_entityType['label']]));

          // Retrieve custom groups for this entity type.
          $this->_subTypes = CRM_Eck_BAO_EckEntityType::getSubTypes($this->_entityTypeName);
          $this->_customGroups = array_filter(
            CRM_Eck_BAO_EckEntityType::getCustomGroups($this->_entityTypeName),
            function($custom_group) {
              return empty($custom_group['extends_entity_column_value']);
            }
          );
          break;
        case CRM_Core_Action::DELETE:
          $this->setTitle(E::ts('Delete Entity Type <em>%1</em>', [1 => $this->_entityType['label']]));
          break;
      }
    }
    else {
      throw new Exception(E::ts('Invalid action.'));
    }

    $this->assign('entityType', $this->_entityType);

    // Set redirect destination.
    $this->controller->_destination = CRM_Utils_System::url('civicrm/admin/eck/entity-types', 'reset=1');
  }

  /**
   * {@inheritDoc}
   */
  public function buildQuickForm() {
    switch ($this->_action) {
      case CRM_Core_Action::UPDATE:
      case CRM_Core_Action::ADD:
        $submit_button_caption = E::ts('Save');

        foreach (CRM_Eck_DAO_EckEntityType::fields() as $field) {
          if ($field['name'] != 'id') {
            $this->addField(
              $field['name'],
              [
                'entity' => 'EckEntityType',
                'name' => $field['name'],
                'action' => 'create',
                'context' => array_search(
                  $this->_action,
                  CRM_Core_Action::$_names
                ),
              ],
              $field['required']
            );
          }
        }
        if ($this->_action === CRM_Core_Action::UPDATE) {
          $this->getElement('name')->freeze();
        }

        // Add links to custom groups.
        $this->assign('customGroupAdminUrl', CRM_Utils_System::url('civicrm/admin/custom/group'));
        foreach ($this->_customGroups as &$custom_group) {
          $custom_group['browse_url'] = CRM_Utils_System::url(
            'civicrm/admin/custom/group/field',
            [
              'action' => CRM_Core_Action::BROWSE,
              'gid' => $custom_group['id'],
            ]
          );
        }
        $this->assign('customGroups', $this->_customGroups);
        $this->assign('subTypes', $this->_subTypes);
        break;
      case CRM_Core_Action::DELETE:
        $submit_button_caption = E::ts('Delete');
        break;
      default:
        throw new Exception(E::ts('Invalid operation.'));
    }

    $this->addButtons(
      [
        [
          'type' => 'submit',
          'name' => $submit_button_caption,
          'isDefault' => TRUE,
        ],
        [
          'type' => 'cancel',
          'name' => E::ts('Cancel'),
        ],
      ]
    );

    // export form elements
    $this->assign('elementNames', $this->getRenderableElementNames());
    parent::buildQuickForm();
  }

  /**
   * {@inheritDoc}
   */
  public function setDefaultValues() {
    // Set current field values as element default values.
    $values = $this->exportValues();
    foreach ($this->getRenderableElementNames() as $elementName) {
      if (isset($values[$elementName])) {
        $this->getElement($elementName)->setValue($values[$elementName]);
      }
      elseif (isset($this->_entityType[$elementName])) {
        $this->getElement($elementName)->setValue($this->_entityType[$elementName]);
      }
    }
  }

  /**
   * {@inheritDoc}
   */
  public function validate() {
    parent::validate();

    $values = $this->exportValues();

    // Note: Since each field can only have one error, the most significant
    // error is to be set the latest, overwriting previous error messages for
    // the same element.

    if ($this->getAction() == CRM_Core_Action::UPDATE || $this->getAction() == CRM_Core_Action::ADD) {
      // Enforce PascalCase formatting.
      if (ucfirst($this->_submitValues['name']) !== $this->_submitValues['name']) {
        $this->_errors['name'] = E::ts('The entity type name must be in PascalCase (at least first letter uppercase).');
      }

      // Do not allow duplicate entity type names.
      $count = civicrm_api3('EckEntityType', 'getcount', [
        'name' => $values['name']
      ]);
      if (
        // case-insensitive checking according to API/database behavior.
        strtolower($values['name']) != strtolower($this->_entityType['name'] ?? NULL)
        && $count > 0
      ) {
        $this->_errors['name'] = E::ts('An entity type with this name already exists.');
      }
    }

    return (0 == count($this->_errors));
  }

  /**
   * {@inheritDoc}
   */
  public function postProcess() {
    $values = $this->exportValues(NULL, TRUE);
    switch ($this->getAction()) {
      case CRM_Core_Action::ADD:
        \Civi\Api4\EckEntityType::create()
          ->setValues($values)
          ->execute();
        break;

      case CRM_Core_Action::UPDATE:
        \Civi\Api4\EckEntityType::update()
          ->setValues($values)
          ->addWhere('id', '=', $this->_entityType['id'])
          ->execute();
        break;

      case CRM_Core_Action::DELETE:
        \Civi\Api4\EckEntityType::delete()
          ->addWhere('id', '=', $this->_entityType['id'])
          ->execute();
        break;
    }

    parent::postProcess();
  }

  /**
   * Retrieves fields/elements defined in this form.
   *
   * @return string[]
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = array();
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_Element $element */
      $label = $element->getLabel();
      if (!empty($label)) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
