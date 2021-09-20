<?php
use CRM_Eck_ExtensionUtil as E;

class CRM_Eck_Page_EntityType extends CRM_Core_Page {

  public $entityType;

  public function run() {
    // Example: Set the page-title dynamically; alternatively, declare a static title in xml/Menu/*.xml
    CRM_Utils_System::setTitle(E::ts('EntityType'));

    parent::run();
  }

}
