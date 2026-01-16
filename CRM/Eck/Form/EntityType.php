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
use Civi\Eck\Utils;

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
          $subtypesExist =
            $this->_entityType['has_subtypes']
            && (bool) \CRM_Eck_BAO_EckEntityType::getSubTypes($this->_entityTypeName);
          $this->assign('subtypesExist', $subtypesExist);

          $this->setTitle(E::ts('Edit Entity Type %1', [1 => $this->_entityType['label']]));
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

      $fields = \Civi::entity('EckEntityType')->getSupportedFields();

      foreach (['label', 'name', 'icon'] as $fieldName) {
        $field = $fields[$fieldName] ?? NULL;
        if ($field) {
          $this->addField(
            $fieldName,
            [
              'entity' => 'EckEntityType',
              'name' => $fieldName,
              'action' => 'create',
              'class' => $fieldName === 'icon' ? 'crm-icon-picker' : '',
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
      // TODO: After bumping Civi compat to 6.9+ this can be done with metadata & the addField array above
      $this->addToggle('in_recent', $fields['in_recent']['title']);
    }
    if ($this->_action === CRM_Core_Action::UPDATE) {
      $this->getElement('name')->freeze();

      $this->addToggle('has_subtypes', $fields['has_subtypes']['title'], [
        'on' => E::ts('Enabled'),
        'off' => E::ts('Disabled'),
      ]);

      // Embed search displays for subtypes and custom fields
      Civi::service('angularjs.loader')->addModules(['crmSearchDisplayTable']);
      $subtypeFilter = ['grouping' => ['=' => $this->_entityTypeName]];
      $this->assign('subtypeDisplay', Utils::searchDisplayMarkup('ECK_Subtypes', 'ECK_Subtypes', $subtypeFilter));
      $groupFilter = ['extends' => 'Eck_' . $this->_entityTypeName];
      $this->assign('groupDisplay', Utils::searchDisplayMarkup('ECK_Custom_Groups', 'ECK_Custom_Groups', $groupFilter));
    }
    elseif ($this->_action == CRM_Core_Action::DELETE) {
      $submit_button_caption = E::ts('Delete');
    }
    elseif ($this->_action !== CRM_Core_Action::ADD) {
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
   * TODO: Remove this shim after bumping Civi compat to 6.9+
   *
   * @param string $name
   * @param string $title
   * @param array $attributes
   * @param bool $required
   * @return HTML_QuickForm_Element
   */
  public function addToggle(string $name, string $title, array $attributes = [], bool $required = FALSE) {
    if (is_callable('CRM_Core_Form::addToggle')) {
      return parent::addToggle($name, $title, $attributes, $required);
    }
    return $this->add('advcheckbox', $name, $title, NULL, $required);
  }

  /**
   * {@inheritDoc}
   */
  public function setDefaultValues() {
    $defaults = $this->_entityType;
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
