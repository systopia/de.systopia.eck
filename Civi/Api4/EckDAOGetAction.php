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

use CRM_Eck_ExtensionUtil as E;
use Civi\Api4\Generic\Result;
use Civi\Api4\Query\Api4SelectQuery;

class EckDAOGetAction extends Generic\DAOGetAction {

  protected function getObjects(Result $result) {
    $getCount = in_array('row_count', $this->getSelect());
    $onlyCount = $this->getSelect() === ['row_count'];

    if (!$onlyCount) {
      $query = new \Civi\Eck\API\Api4SelectQuery($this);
      $rows = $query->run();
      // Always include ECK entity type.
      $rows = array_map(function($row) {
        return $row + ['entity_type' => \CRM_Eck_DAO_Entity::getEntityType($this->getEntityName())];
      }, $rows);
      \CRM_Utils_API_HTMLInputCoder::singleton()->decodeRows($rows);
      $result->exchangeArray($rows);
      // No need to fetch count if we got a result set below the limit
      if (!$this->getLimit() || count($rows) < $this->getLimit()) {
        $result->rowCount = count($rows) + $this->getOffset();
        $getCount = FALSE;
      }
    }
    if ($getCount) {
      $query = new Api4SelectQuery($this);
      $result->rowCount = $query->getCount();
    }
  }

}
