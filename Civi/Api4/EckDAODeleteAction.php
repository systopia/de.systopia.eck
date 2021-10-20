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

class EckDAODeleteAction extends Generic\DAODeleteAction {

  /**
   * @var string $entityType
   *   The ECK entity type of the entity to create.
   */
  protected $entityType;

  /**
   * @param $entityName
   * @param $actionName
   *
   * @return \EckDAOCreateAction
   *
   * @throws \API_Exception
   */
  public function __construct($entityName, $actionName) {
    parent::__construct($entityName, $actionName);
    $this->entityType = \CRM_Eck_DAO_Entity::getEntityType($entityName);
  }

}
