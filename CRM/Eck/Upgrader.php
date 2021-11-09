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

/**
 * Collection of upgrade steps.
 */
class CRM_Eck_Upgrader extends CRM_Eck_Upgrader_Base {

  /**
   * Performs installation tasks.
   */
  public function install() {
    $customData = new CRM_Eck_CustomData(E::LONG_NAME);
    $customData->syncOptionGroup(E::path('resources/eck_sub_types.json'));
  }

}
