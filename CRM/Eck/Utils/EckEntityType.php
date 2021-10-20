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

class CRM_Eck_Utils_EckEntityType {

  /**
   * Evaluate call of non-existing static method.
   *
   * The method to call must be prefixed with an existing ECK entity type,
   * separated by "." (period) in order to be executed.
   *
   * This is needed for resolving entity sub types for custom groups due to a
   * bogus implementation of calling methods, i.e. no arguments be passable via
   * a custom group's configuration.
   * Since sub types are dependent on the entity type, we pass that as part of
   * the static method name, which is being resolved here.
   * @see \CRM_Core_BAO_CustomGroup::getExtendedObjectTypes()
   *
   * @param $funName
   * @param $arguments
   *
   * @return mixed
   */
  public static function __callStatic($funName, $arguments) {
    $allowed_methods = [
      'getSubTypes',
    ];
    $method = explode('.', $funName);
    if (
      count($method) == 2
      && in_array($method[1], $allowed_methods)
      && method_exists('CRM_Eck_BAO_EckEntityType', $method[1])
      && \CRM_Eck_BAO_EckEntityType::objectExists($method[0], 'CRM_Eck_BAO_EckEntityType', TRUE)
    ) {
      return \CRM_Eck_BAO_EckEntityType::{$method[1]}($method[0]);
    }
    else {
      trigger_error(
        'Call to undefined method '.__CLASS__.'::'.$funName.'()',
        E_USER_ERROR
      );
    }
  }

}
