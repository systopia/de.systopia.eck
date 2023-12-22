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

trait EckSaveTrait {

  /**
   * Override core function to save items using the appropriate entity type
   *
   * @param array<array<mixed>> $items
   *   Items already formatted by self::writeObjects
   * @return \CRM_Core_DAO[]
   *   Array of saved DAO records
   */
  protected function write(array $items) {
    $entityType = \CRM_Eck_BAO_Entity::getEntityType($this->getEntityName());

    foreach ($items as &$item) {
      $item['entity_type'] = $entityType;
    }

    return \CRM_Eck_BAO_Entity::writeRecords($items);
  }

}
