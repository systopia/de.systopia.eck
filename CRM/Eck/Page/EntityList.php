<?php
use CRM_Eck_ExtensionUtil as E;

class CRM_Eck_Page_EntityList extends CRM_Core_Page {

  public function run() {
    if (!$entity_type_name = CRM_Utils_Request::retrieve('type', 'String', $this)) {
      throw new CRM_Core_Exception('No entity type given.');
    }

    try {
      $entity_type = civicrm_api3('EckEntityType', 'getsingle', ['name' => $entity_type_name]);
    }
    catch (Exception $exception) {
      throw new Exception(E::ts('Invalid entity type.'));
    }

    // TODO: Use the API ($entity_type_name.get) when implemented.
    $params = [
      'type' => $entity_type_name,
    ];
    $details = [];
    $entities = CRM_Eck_DAO_Entity::commonRetrieveAll($entity_type_name, 'name', $entity_type_name, $details);

    CRM_Utils_System::setTitle($entity_type['label']);

    $entities = [];
    $entities = CRM_Eck_DAO_Entity::commonRetrieveAll($entity_type_name, 'id', NULL, $entities);
    $this->assign('entities', $entities);

    parent::run();
  }

}
