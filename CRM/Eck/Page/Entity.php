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

class CRM_Eck_Page_Entity extends CRM_Core_Page {

  public function run() {

    // TODO: Get ECK entity type from request query.
    if (!$entity_type_name = CRM_Utils_Request::retrieve('type', 'String', $this)) {
      throw new CRM_Core_Exception('No entity type given.');
    }
    try {
      $entity_type = civicrm_api3('EckEntityType', 'getsingle', ['name' => $entity_type_name]);
      $this->assign('entity_type', $entity_type);
    }
    catch (Exception $exception) {
      throw new Exception(E::ts('Invalid entity type.'));
    }

    // TODO: Get ECK entity ID from request query.
    if (!$entity_id = CRM_Utils_Request::retrieve('id', 'Integer', $this)) {
      throw new CRM_Core_Exception('No entity ID given.');
    }

    // TODO: Get ECK entity using the API.
    $entity = civicrm_api3('Eck' . $entity_type['name'], 'getsingle', ['id' => $entity_id]);

    // TODO: Get fields for subtype of the entity.
    $fields = civicrm_api3('Eck' . $entity_type['name'], 'getfields', ['subtype' => $entity['subtype']]);
    $this->assign('fields', $fields);

    // TODO: Set page title.
    CRM_Utils_System::setTitle($entity['title']);

    // TODO: Assign entity properties as template variables.
    $this->assign('entity', $entity);

    parent::run();
  }

}
