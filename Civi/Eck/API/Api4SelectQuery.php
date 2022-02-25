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

namespace Civi\Eck\API;

use CRM_Eck_ExtensionUtil as E;
use Civi\Api4\Utils\CoreUtil;

class Api4SelectQuery extends \Civi\Api4\Query\Api4SelectQuery {

  public function __construct($apiGet) {
    // Run the extended class' constructor
    parent::__construct($apiGet);
    // Overwrite the query property with the correct table name.
    $entity = $this->getEntity();
    $baoName = CoreUtil::getBAOFromApiName($entity);
    $bao = new $baoName(substr($entity, strlen('Eck_')));
    $tableName = $bao->tableName();
    $this->query = \CRM_Utils_SQL_Select::from($tableName . ' ' . self::MAIN_TABLE_ALIAS);
    // Add ACLs first to avoid redundant subclauses
    $this->query->where($this->getAclClause(self::MAIN_TABLE_ALIAS, $baoName));
  }

  public function getAclClause($tableAlias, $baoName, $stack = []) {
    if (!$this->getCheckPermissions()) {
      return [];
    }
    // Prevent (most) redundant acl sub clauses if they have already been applied to the main entity.
    // FIXME: Currently this only works 1 level deep, but tracking through multiple joins would increase complexity
    // and just doing it for the first join takes care of most acl clause deduping.
    if (count($stack) === 1 && in_array(reset($stack), $this->aclFields, TRUE)) {
      return [];
    }
    $clauses = $baoName::getSelectWhereClause($tableAlias, substr($this->getEntity(), strlen('Eck_')));
    if (!$stack) {
      // Track field clauses added to the main entity
      $this->aclFields = array_keys($clauses);
    }
    return array_filter($clauses);
  }

}
