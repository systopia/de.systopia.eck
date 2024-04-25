<?php
/*-------------------------------------------------------+
| CiviCRM Entity Construction Kit                        |
| Copyright (C) 2024 SYSTOPIA                            |
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

namespace Civi\Api4;

use CRM_Eck_ExtensionUtil as E;

class EckDAOGetAction extends Generic\DAOGetAction {

  /**
   * {@inheritDoc}
   */
  public function setDefaultWhereClause(): void {
    if (NULL !== $this->_itemsToGet('id')) {
      $fields = $this->entityFields();
      foreach ($fields as $field) {
        if (
          // Exclude default value filters for "created_date" and "modified_date" fields.
          isset($field['default_value'])
          && !$this->_whereContains($field['name'])
          && !in_array($field['name'], ['created_date', 'modified_date'], TRUE)
        ) {
          $this->addWhere($field['name'], '=', $field['default_value']);
        }
      }
    }
  }

}
