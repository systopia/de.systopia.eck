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

class CRM_Eck_Page_Entity extends CRM_Core_Page {

  /**
   * @var int
   *
   * The id of the entity we are processing.
   */
  public $_id;

  /**
   * @var array<string, mixed>
   *
   * The entity type of the entity we are processing.
   */
  public $_entityType;

  /**
   * The entity subtype of the entity we are processing.
   *
   * @var int
   */
  public $_subtype;

  /**
   * {@inheritDoc}
   */
  public function run(): void {
    // Retrieve ECK entity type.
    if (!is_string($entity_type_name = CRM_Utils_Request::retrieve('type', 'String', $this))) {
      throw new CRM_Core_Exception('No ECK entity type given.');
    }
    try {
      $entity_type = EckEntityType::get(FALSE)->addWhere('name', '=', $entity_type_name)->execute()->single();
      $entity_type['table_name'] = (new CRM_Eck_BAO_Entity($entity_type_name))->tableName();
      $this->assign('entity_type', $entity_type);
      $this->_entityType = $entity_type;
    }
    catch (Exception $exception) {
      throw new CRM_Core_Exception(E::ts('Invalid ECK entity type.'), 0, [], $exception);
    }

    // Retrieve ECK entity using the API.
    if (!is_int($entity_id = CRM_Utils_Request::retrieve('id', 'Integer', $this))) {
      throw new CRM_Core_Exception('No entity ID given.');
    }
    $this->_id = $entity_id;
    $entity = civicrm_api4('Eck_' . $entity_type_name, 'get', [
      'where' => [['id', '=', $entity_id]],
    ])->single();

    // Retrieve ECK entity subtype.
    $subtype_value = CRM_Utils_Request::retrieve('subtype', 'String', $this);
    if (!is_string($subtype_value)) {
      $subtypes = \CRM_Eck_BAO_EckEntityType::getSubTypes($entity_type_name, FALSE);
      $subtype = $subtypes[$entity['subtype']];
      $subtype_value = $subtype['value'];
    }
    if (!is_numeric($subtype_value)) {
      throw new CRM_Core_Exception('Invalid ECK value for parameter "subtype".');
    }
    $this->_subtype = (int) $subtype_value;
    $this->assign('subtype', $this->_subtype);

    // Set page title.
    CRM_Utils_System::setTitle($entity['title']);

    $this->assign('entity', $entity);

    CRM_Eck_Page_Entity_TabHeader::build($this);

    Civi::resources()->addScriptFile(E::LONG_NAME, 'js/entityTabs.js');

    parent::run();
  }

  /**
   * {@inheritDoc}
   */
  public function getTemplateFileName() {
    return 'CRM/Eck/Page/Entity/Tab.tpl';
  }

}
