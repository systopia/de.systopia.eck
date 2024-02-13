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

  protected $_subTypeValue;

  protected $_subType;

  protected $_customGroups = [];

  /**
   * {@inheritDoc}
   */
  public function preProcess() {
    $this->setAction(CRM_Utils_Request::retrieve('action', 'String', $this, FALSE) ?? 'add');
    Civi::resources()->addScriptFile('civicrm', 'js/jquery/jquery.crmIconPicker.js');

    if ($this->_action == CRM_Core_Action::ADD) {
      if (!($this->_entityType = CRM_Utils_Request::retrieve('type', 'String', $this))) {
        throw new Exception(E::ts('No ECK entity type given.'));
      }
      $this->setTitle(E::ts('Add Subtype'));
      $this->_subType = [
        'grouping' => $this->_entityType,
        'option_group_id' => 'eck_sub_types',
      ];
    }
    elseif ($this->_action == CRM_Core_Action::UPDATE || $this->_action == CRM_Core_Action::DELETE) {
      if (!($this->_subTypeValue = CRM_Utils_Request::retrieve('subtype', 'String', $this))) {
        throw new Exception(E::ts('No subtype given.'));
      }
      try {
        $this->_subType = civicrm_api3(
          'OptionValue',
          'getsingle',
          [
            'option_group_id' => 'eck_sub_types',
            'value' => $this->_subTypeValue,
          ]
        );
      }
      catch (Exception $exception) {
        throw new Exception(E::ts('Invalid subtype.'));
      }
      switch ($this->_action) {
        case CRM_Core_Action::UPDATE:
          $this->setTitle(E::ts('Edit Subtype <em>%1</em>', [1 => $this->_subType['label']]));
          $this->_customGroups = array_filter(
            CRM_Eck_BAO_EckEntityType::getCustomGroups($this->_subType['grouping']),
            function($custom_group) {
              return isset($custom_group['extends_entity_column_value'])
                && is_array($custom_group['extends_entity_column_value'])
                && in_array(
                  $this->_subType['value'],
                  $custom_group['extends_entity_column_value']
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
      throw new Exception(E::ts('Invalid action.'));
    }

    $this->assign('subType', $this->_subType);

    // Set redirect destination.
    $this->controller->_destination = CRM_Utils_System::url(
      'civicrm/admin/eck/entity-type',
      'reset=1&action=update&type=' . $this->_subType['grouping']
    );

  }

  public function buildQuickForm() {
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
      elseif (isset($this->_subType[$elementName])) {
        $this->getElement($elementName)->setValue($this->_subType[$elementName]);
      }
    }
  }

  public function postProcess() {
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
        CRM_Eck_BAO_EckEntityType::deleteSubType($this->_subType['value']);
        break;
    }

    parent::postProcess();
  }

  /**
   * Get the fields/elements defined in this form.
   *
   * @return array (string)
   */
  public function getRenderableElementNames() {
    // The _elements list includes some items which should not be
    // auto-rendered in the loop -- such as "qfKey" and "buttons".  These
    // items don't have labels.  We'll identify renderable by filtering on
    // the 'label'.
    $elementNames = [];
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
