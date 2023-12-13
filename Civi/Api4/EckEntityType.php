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

namespace Civi\Api4;

use Civi\Api4\Generic\DAOEntity;
use Civi\Api4\Generic\Traits\ManagedEntity;
use CRM_Eck_ExtensionUtil as E;

/**
 * EckEntityType entity.
 *
 * Provided by the Entity Construction Kit extension.
 *
 * @package Civi\Api4
 */
class EckEntityType extends DAOEntity {

  use ManagedEntity;

}
