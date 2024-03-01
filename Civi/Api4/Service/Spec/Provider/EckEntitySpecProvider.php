<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Service\Spec\RequestSpec;

/**
 * Class EckEntitySpecProvider
 *
 * @package Civi\Api4\Service\Spec\Provider
 */
class EckEntitySpecProvider implements Generic\SpecProviderInterface {

  /**
   * @param \Civi\Api4\Service\Spec\RequestSpec $spec
   */
  public function modifySpec(RequestSpec $spec) {
    if (NULL !== ($subTypeField = $spec->getFieldByName('subtype'))) {
      $subTypeField
        ->setSuffixes(['name', 'label', 'description', 'icon'])
        ->setOptionsCallback([$this, 'getSubTypes']);
    }
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
   * @param \Civi\Api4\Service\Spec\FieldSpec|array{'entity':string} $field
   * @param array<mixed> $values
   * @param bool|array<string> $returnFormat
   * @param bool $checkPermissions
   * @return array<int|string, array<string,mixed>>|false
   */
  public static function getSubTypes($field, $values, $returnFormat, $checkPermissions) {
    // TODO: After dropping support for 5.70 and below, $field will always be an array
    $entity = is_array($field) ? $field['entity'] : $field->getEntity();
    $entityType = \CRM_Eck_BAO_Entity::getEntityType($entity);
    $options = NULL !== $entityType ? \CRM_Eck_BAO_EckEntityType::getSubTypes($entityType, FALSE) : [];
    foreach ($options as &$option) {
      $option['id'] = $option['value'];
    }
    return $options;
  }

}
