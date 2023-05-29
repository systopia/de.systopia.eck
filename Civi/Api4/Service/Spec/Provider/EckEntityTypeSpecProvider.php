<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Query\Api4SelectQuery;
use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;

/**
 * Class EckEntityTypeSpecProvider
 *
 * @package Civi\Api4\Service\Spec\Provider
 */
class EckEntityTypeSpecProvider implements Generic\SpecProviderInterface {

  /**
   * @param \Civi\Api4\Service\Spec\RequestSpec $spec
   */
  public function modifySpec(RequestSpec $spec) {
    $action = $spec->getAction();

    $spec->getFieldByName('name')->setRequired(FALSE);

    if ($action === 'get') {
      $field = new FieldSpec('api_name', 'EckEntityType', 'String');
      $field
        ->setTitle(ts('API Name'))
        ->setLabel(ts('API Name'))
        ->setColumnName('name')
        ->setDescription(ts('APIv4 name of this entity.'))
        ->setInputType('Type')
        ->setSqlRenderer([__CLASS__, 'renderSqlForApiName']);
      $spec->addFieldSpec($field);

      $field = new FieldSpec('sub_types', 'EckEntityType', 'Array');
      $field
        ->setTitle(ts('Sub Types'))
        ->setLabel(ts('Sub Types'))
        ->setColumnName('name')
        ->setDescription(ts('All subtypes of this entity.'))
        ->setOptionsCallback([__CLASS__, 'getSubtypeOptions'])
        ->setInputType('Select')
        ->setInputAttrs(['multiple' => TRUE])
        ->setSerialize(\CRM_Core_DAO::SERIALIZE_COMMA)
        ->setSqlRenderer([__CLASS__, 'renderSqlForEckSubtypes']);
      $spec->addFieldSpec($field);
    }
  }

  public static function renderSqlForApiName(array $field, Api4SelectQuery $query): string {
    return "CONCAT('Eck_', {$field['sql_name']})";
  }

  public static function renderSqlForEckSubtypes(array $field, Api4SelectQuery $query): string {
    $optionGroupId = \CRM_Core_DAO::getFieldValue('CRM_Core_DAO_OptionGroup', 'eck_sub_types', 'id', 'name');
    return "(SELECT GROUP_CONCAT(`civicrm_option_value`.`value`) 
      FROM `civicrm_option_value` 
      WHERE `civicrm_option_value`.`option_group_id` = $optionGroupId 
      AND `civicrm_option_value`.`grouping` = {$field['sql_name']})";
  }

  public static function getSubtypeOptions() {
    $options = \CRM_Core_OptionValue::getValues(['name' => 'eck_sub_types']);
    foreach ($options as &$option) {
      $option['id'] = $option['value'];
    }
    return $options;
  }

  /**
   * @param string $entity
   * @param string $action
   *
   * @return bool
   */
  public function applies($entity, $action) {
    return $entity === 'EckEntityType';
  }

}
