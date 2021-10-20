<?php

namespace Civi\Api4;

use Civi\Api4\Generic\Result;
use Civi\Api4\Query\Api4SelectQuery;

class EckDAOCreateAction extends Generic\DAOCreateAction {

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
    $this->values += ['entity_type' => $this->entityType];
  }

  /**
   * {@inheritDoc}
   */
  public function setValues(array $values) {
    $this->values = $values + ['entity_type' => $this->entityType];
    return $this;
  }

}
