<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Service\Spec\RequestSpec;
use Civi\Api4\Service\Spec\SpecFormatter;

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
    // Add DAO fields, since they might not have been set by \Civi\Api4\Service\Spec\SpecGatherer::addDAOFields(), as
    // CRM_Eck_DAO_Entity::fields() is static, but depends on the ECK entity type for compiling the field list.
    $entityTypes = \CRM_Eck_BAO_EckEntityType::getEntityTypes();
    $entityName = $spec->getEntity();
    // Instantiate the BAO for initialization with the given ECK entity type.
    $bao = new \CRM_Eck_BAO_Entity($entityTypes[$entityName]['name']);
    foreach ($bao::getSupportedFields() as $DAOField) {
      if (NULL === $spec->getFieldByName($DAOField['name'])) {
        $this->setDynamicFk($DAOField, $spec);
        $field = SpecFormatter::arrayToField($DAOField, $entityName);
        $spec->addFieldSpec($field);
      }
    }

    if (NULL !== ($subTypeField = $spec->getFieldByName('subtype'))) {
      $subTypeField
        ->setSuffixes(['name', 'label', 'description', 'icon'])
        ->setOptionsCallback([$this, 'getSubTypes']);
    }
  }

  /**
   * Copied from \Civi\Api4\Service\Spec\SpecGatherer::setDynamicFk()
   *
   * @param array{name: string, FKClassName: string, bao: string, type: int, DFKEntities: array<mixed>} $DAOField
   *
   * Adds metadata about dynamic foreign key fields.
   *
   * E.g. some tables have a DFK with a pair of columns named `entity_table` and `entity_id`.
   * This will gather the list of 'dfk_entities' to add as metadata to the e.g. `entity_id` column.
   *
   * Additionally, if $values contains a value for e.g. `entity_table`,
   * then getFields will also output the corresponding `fk_entity` for the `entity_id` field.
   */
  private function setDynamicFk(array &$DAOField, RequestSpec $spec): void {
    // @phpstan-ignore-next-line
    if (empty($DAOField['FKClassName']) && !empty($DAOField['bao']) && $DAOField['type'] == \CRM_Utils_Type::T_INT) {
      // Check if this field is a key for a dynamic FK
      foreach ($DAOField['bao']::getReferenceColumns() ?? [] as $reference) {
        if ($reference instanceof \CRM_Core_Reference_Dynamic && $reference->getReferenceKey() === $DAOField['name']) {
          $entityTableColumn = $reference->getTypeColumn();
          $DAOField['DFKEntities'] = $reference->getTargetEntities();
          $DAOField['html']['controlField'] = $entityTableColumn;
          // If we have a value for entity_table then this field can pretend to be a single FK too.
          // @phpstan-ignore-next-line
          if ($spec->hasValue($entityTableColumn) && $DAOField['DFKEntities']) {
            $DAOField['FKClassName'] = \CRM_Core_DAO_AllCoreTables::getDAONameForEntity(
              // @phpstan-ignore-next-line
              $DAOField['DFKEntities'][$spec->getValue($entityTableColumn)]
            );
          }
          break;
        }
      }
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
    if (!is_string($entity)) {
      throw new \CRM_Core_Exception('No ECK entity type given while retrieving subtypes.');
    }
    $entityType = \CRM_Eck_BAO_Entity::getEntityType($entity);
    $options = NULL !== $entityType ? \CRM_Eck_BAO_EckEntityType::getSubTypes($entityType, FALSE) : [];
    foreach ($options as &$option) {
      $option['id'] = $option['value'];
    }
    return $options;
  }

}
