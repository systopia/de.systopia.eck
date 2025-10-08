<?php

if (!CRM_Extension_System::singleton()->getMapper()->isActiveModule('civirules')) {
  // CiviRules is not installed. Don't load class
  return;
}

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

  public function getExtraDataInputUrl($ruleId) {
    return $this->getFormattedExtraDataInputUrl('civicrm/civirule/form/trigger/post', $ruleId);
  }

  /**
   * Get various types of help text for the trigger:
   *   - triggerDescription: When choosing from a list of triggers, explains what the trigger does.
   *   - triggerDescriptionWithParams: When a trigger has been configured for a rule provides a
   *       user friendly description of the trigger and params (see $this->getTriggerDescription())
   *   - triggerParamsHelp (default): If the trigger has configurable params, show this help text when configuring
   * @param string $context
   *
   * @return string
   */
  public function getHelpText(string $context = 'triggerParamsHelp'): string {
    return parent::getHelpText($context);
  }

  /**
   * @param $op
   * @param $objectName
   * @param $objectId
   * @param $objectRef
   * @param $eventID
   *
   * @return void
   */
  public function triggerTrigger($op, $objectName, $objectId, $objectRef, $eventID) {
    // Check if this trigger is enabled for this op
    if (!str_contains($this->triggerParams['trigger_op'], $op)) {
      // \Civi::log()->debug('CiviRules ECK trigger: Trigger op not enabled: ' . $op);
      return;
    }

    parent::triggerTrigger($op, $objectName, $objectId, $objectRef, $eventID);
  }

}
