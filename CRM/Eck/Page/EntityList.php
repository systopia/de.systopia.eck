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

class CRM_Eck_Page_EntityList extends CRM_Core_Page {

  public function run() {
    if (!$entity_type_name = CRM_Utils_Request::retrieve('type', 'String', $this)) {
      throw new CRM_Core_Exception('No entity type given.');
    }

    try {
      $entity_type = civicrm_api3('EckEntityType', 'getsingle', ['name' => $entity_type_name]);
    }
    catch (Exception $exception) {
      throw new Exception(E::ts('Invalid entity type.'));
    }

    // TODO: Use the API ($entity_type_name.get) when implemented.
    $params = [
      'type' => $entity_type_name,
    ];
    $details = [];
    $entities = CRM_Eck_DAO_Entity::commonRetrieveAll($entity_type_name, 'name', $entity_type_name, $details);

    CRM_Utils_System::setTitle($entity_type['label']);

    $entities = [];
    $entities = CRM_Eck_DAO_Entity::commonRetrieveAll($entity_type_name, 'id', NULL, $entities);
    $this->assign('entities', $entities);

    parent::run();
  }

}
