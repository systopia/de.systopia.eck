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
   * @var arraystringmixed
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
    if (!is_string($subtype_value = CRM_Utils_Request::retrieve('subtype', 'String', $this))) {
      $subtypes = \CRM_Eck_BAO_EckEntityType::getSubTypes($entity_type_name, FALSE);
      $subtype = $subtypes[$entity['subtype']];
      $subtype_value = $subtype['value'];
    }
    $this->assign('subtype', $subtype_value);
    $this->_subtype = $subtype_value;

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
    // hack lets suppress the form rendering for now
    self::$_template->assign('isForm', FALSE);
    return 'CRM/Eck/Page/Entity/Tab.tpl';
  }

}
