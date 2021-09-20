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
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('Entity Types'));

    Civi::settings()->set('eck_entity_types', [
      'foobar' => [
        'name' => 'EckFoobar',
        'label' => 'Foo Bar Entity Type',
        'class_name' => 'CRM_Eck_DAO_Foobar',
        'table_name' => 'civicrm_eck_foobar',
        'log' => TRUE,
      ],
      'foo' => [
        'name' => 'EckFoo',
        'label' => 'Foo Entity Type',
        'class_name' => 'CRM_Eck_DAO_Foo',
        'table_name' => 'civicrm_eck_foo',
        'log' => TRUE,
      ],
    ]);

    $entity_types = Civi::settings()->get('eck_entity_types');

    foreach ($entity_types as $entity_type) {
      try {
        civicrm_api3('OptionValue', 'getsingle', array(
          'option_group_id' => 'cg_extend_objects',
          'value' => $entity_type['name'],
          'name' => $entity_type['table_name'],
        ));
      }
      catch (CiviCRM_API3_Exception $exception) {
        civicrm_api3('OptionValue', 'create', array(
          'option_group_id' => 'cg_extend_objects',
          'label' => $entity_type['label'],
          'value' => $entity_type['name'],
          'name' => $entity_type['table_name'],
          'is_reserved' => 1,
        ));
      }
    }

    $retrieved_table_name = CRM_Core_DAO_AllCoreTables::getTableForEntityName('EckFoobar');

    $this->assign('entity_types', $entity_types);

    parent::run();
  }

}
