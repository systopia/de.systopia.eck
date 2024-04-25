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
class CRM_Eck_Form_EckSubtype extends CRM_Core_Form {

  protected ?string $_entityType = NULL;

  protected ?string $_subTypeValue = NULL;

  /**
   * @var array<string, int|string>
   */
  protected ?array $_subType = NULL;

  /**
   * @var array<array<string, mixed>>
   */
  protected array $_customGroups = [];

  /**
   * {@inheritDoc}
   */
  public function preProcess(): void {
    Civi::resources()->addScriptFile('civicrm', 'js/jquery/jquery.crmIconPicker.js');

    /** @var int $action */
    $action = CRM_Utils_Request::retrieve('action', 'String', $this, FALSE) ?? CRM_Core_Action::map('add');
    $this->setAction($action);

    if ($this->_action == CRM_Core_Action::ADD) {
      $entityType = CRM_Utils_Request::retrieve('type', 'String', $this);
      if (!is_string($entityType) || '' === $entityType) {
        throw new CRM_Core_Exception('No ECK entity type given.');
      }
      $this->_entityType = $entityType;
      $this->setTitle(E::ts('Add Subtype'));
      $this->_subType = [
        'grouping' => $this->_entityType,
        'option_group_id' => 'eck_sub_types',
      ];
    }
    elseif ($this->_action == CRM_Core_Action::UPDATE || $this->_action == CRM_Core_Action::DELETE) {
      $subTypeValue = CRM_Utils_Request::retrieve('subtype', 'String', $this);
      if (!is_string($subTypeValue) || '' === $subTypeValue) {
        throw new CRM_Core_Exception('No subtype given.');
      }
      $this->_subTypeValue = $subTypeValue;
      try {
        /** @var array{value: int|string, label: string, grouping: string} $subType */
        $subType = civicrm_api3(
          'OptionValue',
          'getsingle',
          [
            'option_group_id' => 'eck_sub_types',
            'value' => $this->_subTypeValue,
          ]
        );
        $this->_subType = $subType;
      }
      catch (Exception $exception) {
        throw new CRM_Core_Exception('Invalid subtype.', 0, [], $exception);
      }
      switch ($this->_action) {
        case CRM_Core_Action::UPDATE:
          $this->setTitle(E::ts('Edit Subtype <em>%1</em>', [1 => $this->_subType['label']]));
          $this->_customGroups = array_filter(
            CRM_Eck_BAO_EckEntityType::getCustomGroups($this->_subType['grouping']),
            function($custom_group) {
              return isset($custom_group['extends_entity_column_value'])
                && is_array($custom_group['extends_entity_column_value'])
                && isset($this->_subType['value'])
                && in_array(
                  $this->_subType['value'],
                  $custom_group['extends_entity_column_value'],
                  TRUE
                );
            }
          );
          break;

        case CRM_Core_Action::DELETE:
          $this->setTitle(E::ts('Delete Subtype <em>%1</em>', [1 => $this->_subType['label']]));
          break;
      }
    }
    else {
      throw new CRM_Core_Exception('Invalid action.');
    }

    $this->assign('subType', $this->_subType);

    // Set redirect destination.
    $this->controller->_destination = CRM_Utils_System::url(
      'civicrm/admin/eck/entity-type',
      'reset=1&action=update&type=' . $this->_subType['grouping']
    );

  }

  public function buildQuickForm(): void {
    switch ($this->_action) {
      case CRM_Core_Action::UPDATE:
      case CRM_Core_Action::ADD:
        $submit_button_caption = E::ts('Save');

        $this->add(
          'text',
          // OptionValue.label
          'label',
          E::ts('Subtype name'),
          NULL,
          TRUE
        );
        $this->add(
          'text',
          'icon',
          E::ts('Icon'),
          ['class' => 'crm-icon-picker']
        );

        // Add links to custom groups.
        $this->assign(
          'customGroupAdminUrl',
          CRM_Utils_System::url('civicrm/admin/custom/group')
        );
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
        break;

      case CRM_Core_Action::DELETE:
        $submit_button_caption = E::ts('Delete');
        break;

      default:
        throw new CRM_Core_Exception('Invalid form action.');
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
        $defaults[$elementName] = $values[$elementName];
        $this->getElement($elementName)->setValue($values[$elementName]);
      }
      elseif (isset($this->_subType[$elementName])) {
        $defaults[$elementName] = $this->_subType[$elementName];
        $this->getElement($elementName)->setValue((string) $this->_subType[$elementName]);
      }
    }
    return $defaults;
  }

  /**
   * {@inheritDoc}
   */
  public function postProcess() {
    if (!isset($this->_subType)) {
      throw new CRM_Core_Exception('No ECK subtype given.');
    }
    switch ($this->getAction()) {
      case CRM_Core_Action::ADD:
      case CRM_Core_Action::UPDATE:
        $values = $this->exportValues(NULL, TRUE);
        // Update OptionValue name and label.
        civicrm_api3('OptionValue', 'create', array_merge($this->_subType, [
          'label' => $values['label'],
          'icon' => $values['icon'],
        ]));
        break;

      case CRM_Core_Action::DELETE:
        CRM_Eck_BAO_EckEntityType::deleteSubType((string) $this->_subType['value']);
        break;
    }

    parent::postProcess();
  }

  /**
   * Retrieves the fields/elements defined in this form.
   *
   * @return array<string>
   */
  public function getRenderableElementNames(): array {
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
