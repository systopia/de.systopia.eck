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

class CRM_Eck_Page_EntityList extends CRM_Core_Page {

  public function run() {
    if (!$entity_type_name = CRM_Utils_Request::retrieve('type', 'String', $this)) {
      throw new CRM_Core_Exception('No ECK entity type given.');
    }

    try {
      $entity_type = EckEntityType::get(FALSE)->addWhere('name', '=', $entity_type_name)->execute()->single();
      $this->assign('entity_type', $entity_type);
    }
    catch (Exception $exception) {
      throw new Exception(E::ts('Invalid ECK entity type.'));
    }

    CRM_Utils_System::setTitle($entity_type['label']);

    $entities = civicrm_api3('Eck' . $entity_type_name, 'get', [], ['limit' => 0])['values'];
    $this->assign('entities', $entities);
    $fields = civicrm_api3('Eck' . $entity_type_name, 'getfields')['values'];
    $fields = array_filter($fields, function($key) {
      return strpos($key, 'custom_') !== 0;
    }, ARRAY_FILTER_USE_KEY);
    $this->assign('fields', $fields);

    parent::run();
  }

}
