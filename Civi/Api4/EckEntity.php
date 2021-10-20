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
use Civi\Api4\Generic\BasicReplaceAction;
use Civi\Api4\Generic\CheckAccessAction;
use Civi\Api4\Generic\DAOCreateAction;
use Civi\Api4\Generic\DAODeleteAction;
use Civi\Api4\Generic\DAOGetAction;
use Civi\Api4\Generic\DAOGetFieldsAction;
use Civi\Api4\Generic\DAOSaveAction;
use Civi\Api4\Generic\DAOUpdateAction;
use Civi\Api4\Action\GetActions;

/**
 * EckEntityType entity.
 *
 * Provided by the Entity Construction Kit extension.
 *
 * @package Civi\Api4
 */
class EckEntity extends Generic\DAOEntity {

  /**
   * {@inheritDoc}
   */
  public static function getFields($checkPermissions = TRUE) {
    [,$entity_type] = func_get_args();
    return (new DAOGetFieldsAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  public static function get($checkPermissions = TRUE) {
    [,$entity_type] = func_get_args();
    return (new EckDAOGetAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  public static function save($checkPermissions = TRUE) {
    [,$entity_type] = func_get_args();
    return (new DAOSaveAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  public static function create($checkPermissions = TRUE) {
    [,$entity_type] = func_get_args();
    return (new EckDAOCreateAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  public static function update($checkPermissions = TRUE) {
    [,$entity_type] = func_get_args();
    return (new EckDAOUpdateAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  public static function delete($checkPermissions = TRUE) {
    [,$entity_type] = func_get_args();
    return (new DAODeleteAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  public static function replace($checkPermissions = TRUE) {
    [,$entity_type] = func_get_args();
    return (new BasicReplaceAction('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  public static function getActions($checkPermissions = TRUE) {
    [,$entity_type] = func_get_args();
    return (new GetActions('Eck' . $entity_type, __FUNCTION__))
      ->setCheckPermissions($checkPermissions);
  }

  public static function checkAccess() {
    [,$entity_type] = func_get_args();
    return new CheckAccessAction('Eck' . $entity_type, __FUNCTION__);
  }

  protected static function getEntityTitle($plural = FALSE) {
    [,$entity_type] = func_get_args();
    $dao = \CRM_Core_DAO_AllCoreTables::getFullName($entity_type);
    return $dao ? $dao::getEntityTitle($plural) : ($plural ? \CRM_Utils_String::pluralize($entity_type) : $entity_type);
  }


}
