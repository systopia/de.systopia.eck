<?php
use CRM_Eck_ExtensionUtil as E;

class CRM_Eck_Page_EntityList extends CRM_Core_Page {

  public function run() {
    if (!$this->entityType = CRM_Utils_Request::retrieve('type', 'String', $this)) {
      throw new CRM_Core_Exception('No entity type given.');
    }
    if (!in_array($this->entityType, array_keys(Civi::settings()->get('eck_entity_types')))) {
      throw new CRM_Core_Exception('Invalid entity type.');
    }

    $entity_types = Civi::settings()->get('eck_entity_types');
    CRM_Utils_System::setTitle($entity_types[$this->entityType]['label']);

    $entities = [];
    $entities = CRM_Eck_DAO_EntityType::commonRetrieveAll($this->entityType, 'id', NULL, $entities);
    $this->assign('entities', $entities);

    parent::run();
  }

}
