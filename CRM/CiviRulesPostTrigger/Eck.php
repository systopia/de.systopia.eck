<?php

class CRM_CiviRulesPostTrigger_Eck extends CRM_Civirules_Trigger_Post {

  /**
   * Returns an array of entities on which the trigger reacts
   *
   * @return CRM_Civirules_TriggerData_EntityDefinition
   */
  protected function reactOnEntity() {
    return new CRM_Civirules_TriggerData_EntityDefinition($this->objectName, $this->objectName, $this->getDaoClassName(), $this->objectName);
  }

  /**
   * Return the name of the DAO Class. If a dao class does not exist return an empty value
   *
   * @return string
   */
  protected function getDaoClassName() {
    return 'CRM_Eck_DAO_Entity';
  }

  public function alterTriggerData(CRM_Civirules_TriggerData_TriggerData &$triggerData) {
    $entityData = $triggerData->getEntityData($triggerData->getEntity());
    $triggerData->setContactId($entityData['modified_id']);
    parent::alterTriggerData($triggerData);
  }
}
