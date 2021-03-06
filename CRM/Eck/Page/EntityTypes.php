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

class CRM_Eck_Page_EntityTypes extends CRM_Core_Page {

  public function run() {
    CRM_Utils_System::setTitle(E::ts('ECK Entity Types'));

    $entity_types = (array) civicrm_api4('EckEntityType', 'get', ['checkPermissions' => FALSE]);

    foreach ($entity_types as &$entity_type) {
      $entity_type['sub_types'] = CRM_Eck_BAO_EckEntityType::getSubTypes($entity_type['name']);
    }
    $this->assign('entity_types', $entity_types);
    CRM_Core_Session::singleton()->pushUserContext(CRM_Utils_System::url('civicrm/admin/eck/entity-types', 'reset=1'));

    parent::run();
  }

}
