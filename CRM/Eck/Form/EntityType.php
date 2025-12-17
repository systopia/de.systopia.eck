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
use Civi\Api4\EckEntityType;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Eck_Form_EntityType extends CRM_Core_Form {

  protected ?string $_entityTypeName = NULL;

  /**
   * @var array<string, int|string>
   */
  protected array $_entityType = [];

  /**
   * @var array<int|string, array<string, mixed>>
   */
  protected array $_customGroups = [];

  /**
   * @var array<int|string, mixed>
   */
  protected array $_subTypes = [];

  /**
   * {@inheritDoc}
   */
  public function preProcess() {
    Civi::resources()->addScriptFile('civicrm', 'js/jquery/jquery.crmIconPicker.js');
    Civi::resources()->addScriptFile(E::LONG_NAME, 'js/entityTypeForm.js');

    /** @var int $action */
    $action = CRM_Utils_Request::retrieve('action', 'String', $this, FALSE) ?? CRM_Core_Action::map('add');
    $this->setAction($action);
    if ($this->_action == CRM_Core_Action::ADD) {
      $this->setTitle(E::ts('Add Entity Type'));
    }
    elseif ($this->_action === CRM_Core_Action::UPDATE || $this->_action === CRM_Core_Action::DELETE) {
      $entityTypeName = CRM_Utils_Request::retrieve('type', 'String', $this);
      if (!is_string($entityTypeName) || '' === $entityTypeName) {
        throw new CRM_Core_Exception('No ECK entity type given.');
      }
      $this->_entityTypeName = $entityTypeName;
      try {
        $this->_entityType = EckEntityType::get(FALSE)
          ->addWhere('name', '=', $this->_entityTypeName)
          ->execute()
          ->single();
      }
      catch (Exception $exception) {
        throw new CRM_Core_Exception('Invalid ECK entity type.', 0, [], $exception);
      }
      switch ($this->_action) {
        case CRM_Core_Action::UPDATE:
          $this->setTitle(E::ts('Edit Entity Type %1', [1 => $this->_entityType['label']]));

          // Retrieve custom groups for this entity type.
          $this->_subTypes = CRM_Eck_BAO_EckEntityType::getSubTypes($this->_entityTypeName);
          $this->_customGroups = array_filter(
            CRM_Eck_BAO_EckEntityType::getCustomGroups($this->_entityTypeName),
            function($custom_group) {
              return !isset($custom_group['extends_entity_column_value'])
                || !is_array($custom_group['extends_entity_column_value'])
                || [] === $custom_group['extends_entity_column_value'];
            }
          );
          break;

        case CRM_Core_Action::DELETE:
          $this->setTitle(E::ts('Delete Entity Type %1', [1 => $this->_entityType['label']]));
          break;
      }
    }
    else {
      throw new CRM_Core_Exception('Invalid action.');
    }

    $this->assign('entityType', $this->_entityType);

    $destination = CRM_Utils_System::url('civicrm/admin/eck/entity-types', 'reset=1', FALSE, NULL, FALSE);
    // Set redirect destination (for submit button).
    $this->controller->_destination = $destination;
    // Also set context (for cancel button)
    CRM_Core_Session::singleton()->pushUserContext($destination);
  }

  /**
   * {@inheritDoc}
   */
  public function buildQuickForm(): void {
    if (
      $this->_action == CRM_Core_Action::UPDATE
      || $this->_action == CRM_Core_Action::ADD
    ) {
      $submit_button_caption = E::ts('Save');

      foreach (['label', 'name', 'icon', 'in_recent'] as $fieldName) {
        $field = CRM_Eck_DAO_EckEntityType::getSupportedFields()[$fieldName] ?? NULL;
        if ($field) {
          $this->addField(
            $field['name'],
            [
              'entity' => 'EckEntityType',
              'name' => $field['name'],
              'action' => 'create',
              'class' => $field['name'] === 'icon' ? 'crm-icon-picker' : '',
              'context' => array_search(
                $this->_action,
                CRM_Core_Action::$_names,
                TRUE
              ),
            ],
            (bool) ($field['required'] ?? FALSE)
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
    }
    elseif ($this->_action == CRM_Core_Action::DELETE) {
      $submit_button_caption = E::ts('Delete');
    }
    else {
      throw new CRM_Core_Exception(E::ts('Invalid operation.'));
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
    $defaults = [];
    foreach ($this->getRenderableElementNames() as $elementName) {
      if (isset($values[$elementName])) {
        $this->getElement($elementName)->setValue($values[$elementName]);
        $defaults[$elementName] = $values[$elementName];
      }
      elseif (isset($this->_entityType[$elementName])) {
        $this->getElement($elementName)->setValue((string) $this->_entityType[$elementName]);
        $defaults[$elementName] = $this->_entityType[$elementName];
      }
    }
    return $defaults;
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
      $count = EckEntityType::get(FALSE)
        ->addSelect('row_count')
        ->addWhere('name', '=', $values['name'])
        ->execute()
        ->count();
      if (
        // case-insensitive checking according to API/database behavior.
        strtolower($values['name']) != strtolower((string) ($this->_entityType['name'] ?? ''))
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
    $values['in_recent'] = ($values['in_recent'] ?? FALSE) === '1';
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
    $elementNames = [];
    foreach ($this->_elements as $element) {
      /** @var HTML_QuickForm_element $element */
      $label = $element->getLabel();
      if ('' !== $label) {
        $elementNames[] = $element->getName();
      }
    }
    return $elementNames;
  }

}
