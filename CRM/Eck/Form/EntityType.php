<?php

use CRM_Eck_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see https://docs.civicrm.org/dev/en/latest/framework/quickform/
 */
class CRM_Eck_Form_EntityType extends CRM_Core_Form {

  protected $_entityTypeName;

  protected $_entityType;

  protected $_customGroups = [];

  public function preProcess() {
    $this->setAction(CRM_Utils_Request::retrieve('action', 'String', $this, FALSE) ?? 'add');

    if (!($this->_entityTypeName = CRM_Utils_Request::retrieve('type', 'String', $this))) {
      throw new Exception(E::ts('No entity type given.'));
    }

    if ($this->_action == CRM_Core_Action::ADD) {
      $this->setTitle(E::ts('Add Entity Type'));
    }
    elseif ($this->_action == CRM_Core_Action::UPDATE || $this->_action == CRM_Core_Action::DELETE) {
      try {
        $this->_entityType = civicrm_api3('EckEntityType', 'getsingle', ['name' => $this->_entityTypeName]);
      }
      catch (Exception $exception) {
        throw new Exception(E::ts('Invalid entity type.'));
      }
      switch ($this->_action) {
        case CRM_Core_Action::UPDATE:
          $this->setTitle(E::ts('Edit Entity Type <em>%1</em>', [1 => $this->_entityType['label']]));

          // Retrieve custom groups for this entity type.
          $this->_customGroups = civicrm_api3(
            'CustomGroup',
            'get',
            ['extends' => $this->_entityTypeName],
            ['limit' => 0]
          )['values'];
          break;
        case CRM_Core_Action::DELETE:
          $this->setTitle(E::ts('Delete Entity Type <em>%1</em>', [1 => $this->_entityType['label']]));
          break;
      }
    }
    else {
      throw new Exception(E::ts('Invalid action.'));
    }

    // Set redirect destination.
    $this->controller->_destination = CRM_Utils_System::url('civicrm/admin/eck/entity-types', 'reset=1');
  }

  /**
   * {@inheritDoc}
   */
  public function buildQuickForm() {
    $entity_types = Civi::settings()->get('eck_entity_types');

    switch ($this->_action) {
      case CRM_Core_Action::UPDATE:
      case CRM_Core_Action::ADD:
        $submit_button_caption = E::ts('Save');
        foreach (CRM_Eck_DAO_EckEntityType::fields() as $field) {
          if ($field['name'] != 'id') {
            $this->addField($field['name'], [
              'entity' => 'EckEntityType',
              'name' => $field['name'],
              'action' => 'create',
              'context' => array_search($this->_action, CRM_Core_Action::$_names),
            ]);
          }
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
        break;
      case CRM_Core_Action::DELETE:
        // TODO: Build "delete" form.
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
    // TODO: Set current field values as element default values.
  }

  /**
   * {@inheritDoc}
   */
  public function postProcess() {
    switch ($this->getAction()) {
      case CRM_Core_Action::ADD:
      case CRM_Core_Action::UPDATE:
        $values = $this->exportValues();
        // TODO: Create or update entity type.
        $entity_type = civicrm_api3('EckEntityType', 'create', [
          'name' => $values['name'],
          'label' => $values['label'],
          'class_name' => $values['class_name'],
          'table_name' => $values['table_name']
        ]);

        // TODO: Create table if not exists.

        try {
          civicrm_api3('OptionValue', 'getsingle', [
            'option_group_id' => 'cg_extend_objects',
            'value' => $entity_type['name'],
            'name' => $entity_type['table_name'],
          ]);
        } catch (CiviCRM_API3_Exception $exception) {
          civicrm_api3('OptionValue', 'create', [
            'option_group_id' => 'cg_extend_objects',
            'label' => $entity_type['label'],
            'value' => $entity_type['name'],
            'name' => $entity_type['table_name'],
            'is_reserved' => 1,
          ]);
        }
      break;
      case CRM_Core_Action::DELETE:
        $this->_entityType->delete();
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
