<?php
/*-------------------------------------------------------+
| CiviCRM Entity Construction Kit                        |
| Copyright (C) 2022 SYSTOPIA                            |
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

use CRM_Eck_ExtensionUtil as E;

class CRM_Eck_Page_Entity_Tag extends CRM_Core_Page {

  /**
   * Called when action is browse.
   */
  public function browse() {
    $entity_types = array_column(CRM_Eck_BAO_EckEntityType::getEntityTypes(), NULL, 'name');

    $controller = new CRM_Core_Controller_Simple('CRM_Tag_Form_Tag', ts('Entity Tags'), $this->_action);
    $controller->setEmbedded(TRUE);
    $controller->reset();
    $controller->set('entityTable', $entity_types[$this->_entityType]['table_name']);
    $controller->set('entityID', $this->_entityId);
    $controller->process();
    $controller->run();
  }

  public function preProcess() {
    $this->_entityType = CRM_Utils_Request::retrieve('type', 'String', $this, TRUE);
    $this->_entityId = CRM_Utils_Request::retrieve('id', 'Positive', $this, TRUE);
    $this->assign('entityType', $this->_entityType);
    $this->assign('entityId', $this->_entityId);

    /**
     * TODO:
     *   Check permission for editing ECK entity tags.
     *   @see CRM_Contact_Page_View::checkUserPermission()
     *   For now, allow editing for all users.
     */
    $this->assign('permission', 'edit');

    $this->_action = CRM_Utils_Request::retrieve('action', 'String', $this, FALSE, 'browse');
    $this->assign('action', $this->_action);
  }

  /**
   * the main function that is called when the page loads
   * it decides the which action has to be taken for the page.
   *
   * @return null
   */
  public function run() {
    $this->preProcess();

    $this->browse();

    return parent::run();
  }

}
