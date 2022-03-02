<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Service\Spec\RequestSpec;

/**
 * Class ContactTypeCreationSpecProvider
 *
 * @package Civi\Api4\Service\Spec\Provider
 */
class EckEntitySpecProvider implements Generic\SpecProviderInterface {

  /**
   * @param \Civi\Api4\Service\Spec\RequestSpec $spec
   */
  public function modifySpec(RequestSpec $spec) {
    $spec->getFieldByName('subtype')->setOptionsCallback([$this, 'getSubTypes']);
  }

  /**
   * @param string $entity
   * @param string $action
   *
   * @return bool
   */
  public function applies($entity, $action) {
    return (bool) \CRM_Eck_BAO_Entity::getEntityType($entity);
  }

  /**
   * Callback function to get subtypes for this fields's entity type.
   *
   * @param \Civi\Api4\Service\Spec\FieldSpec $spec
   * @param array $values
   * @param bool|array $returnFormat
   * @param bool $checkPermissions
   * @return array|false
   */
  public static function getSubTypes($spec, $values, $returnFormat, $checkPermissions) {
    $entityType = \CRM_Eck_BAO_Entity::getEntityType($spec->getEntity());
    $options = \CRM_Eck_BAO_EckEntityType::getSubTypes($entityType, FALSE);
    foreach ($options as &$option) {
      $option['id'] = $option['value'];
    }
    return $options;
  }

}
