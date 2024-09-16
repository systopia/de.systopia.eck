<?php

namespace Civi\Api4\Service\Spec\Provider;

use Civi\Api4\Service\Spec\FieldSpec;
use Civi\Api4\Service\Spec\RequestSpec;
use Civi\Api4\Utils\CoreUtil;
use Civi\Api4\Utils\FormattingUtil;

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
    // TODO: Use `Civi::entity()->getFields()` instead
    foreach ($bao::getSupportedFields() as $DAOField) {
      if (NULL === $spec->getFieldByName($DAOField['name'])) {
        $this->setDynamicFk($DAOField, $spec);
        $field = self::arrayToField($DAOField, $entityName);
        $spec->addFieldSpec($field);
      }
    }

    if (NULL !== ($subTypeField = $spec->getFieldByName('subtype'))) {
      $subTypeField
        ->setSuffixes(['name', 'label', 'description', 'icon'])
        ->setOptionsCallback([$this, 'getSubTypes']);
    }
  }

  // phpcs:disable
  /**
   * Copied from \Civi\Api4\Service\Spec\SpecFormatter
   *
   * TODO: Use `Civi::entity()->getFields()` instead of DAO::fields()
   * so we don't need this copied function
   */
  private static function arrayToField(array $data, string $entityName): FieldSpec {
    $dataTypeName = self::getDataType($data);

    $hasDefault = isset($data['default']) && $data['default'] !== '';

    $name = $data['name'] ?? NULL;
    $field = new FieldSpec($name, $entityName, $dataTypeName);
    $field->setType('Field');
    $field->setColumnName($name);
    $field->setNullable(empty($data['required']));
    $field->setRequired(!empty($data['required']) && !$hasDefault && $name !== 'id');
    $field->setTitle($data['title'] ?? NULL);
    $field->setLabel($data['html']['label'] ?? NULL);
    $field->setLocalizable($data['localizable'] ?? FALSE);
    if (!empty($data['DFKEntities'])) {
      $field->setDfkEntities($data['DFKEntities']);
    }
    if (!empty($data['pseudoconstant'])) {
      // Do not load options if 'prefetch' is disabled
      if (($data['pseudoconstant']['prefetch'] ?? NULL) !== 'disabled') {
        $field->setOptionsCallback([__CLASS__, 'getOptions']);
      }
      // Explicitly declared suffixes
      if (!empty($data['pseudoconstant']['suffixes'])) {
        $suffixes = $data['pseudoconstant']['suffixes'];
      }
      else {
        // These suffixes are always supported if a field has options
        $suffixes = ['name', 'label'];
        // Add other columns specified in schema (e.g. 'abbrColumn')
        foreach (['abbr', 'color', 'description', 'icon', 'grouping', 'url'] as $suffix) {
          if (!empty($data['pseudoconstant'][$suffix . 'Column'])) {
            $suffixes[] = $suffix;
          }
        }
        if (!empty($data['pseudoconstant']['optionGroupName'])) {
          $suffixes = CoreUtil::getOptionValueFields($data['pseudoconstant']['optionGroupName'], 'name');
        }
      }
      $field->setSuffixes($suffixes);
    }
    $field->setReadonly(!empty($data['readonly']));
    if (isset($data['usage'])) {
      $field->setUsage(array_keys(array_filter($data['usage'])));
    }
    if ($hasDefault) {
      $field->setDefaultValue(FormattingUtil::convertDataType($data['default'], $dataTypeName));
    }
    $field->setSerialize($data['serialize'] ?? NULL);
    $field->setDescription($data['description'] ?? NULL);
    $field->setDeprecated($data['deprecated'] ?? FALSE);
    self::setInputTypeAndAttrs($field, $data, $dataTypeName);

    $field->setPermission($data['permission'] ?? NULL);
    $fkAPIName = $data['FKApiName'] ?? NULL;
    $fkClassName = $data['FKClassName'] ?? NULL;
    if ($fkAPIName || $fkClassName) {
      $field->setFkEntity($fkAPIName ?: CoreUtil::getApiNameFromBAO($fkClassName));
    }
    // For pseudo-fk fields like `civicrm_group.parents`
    elseif (($data['html']['type'] ?? NULL) === 'EntityRef' && !empty($data['pseudoconstant']['table'])) {
      $field->setFkEntity(CoreUtil::getApiNameFromTableName($data['pseudoconstant']['table']));
    }
    if (!empty($data['FKColumnName'])) {
      $field->setFkColumn($data['FKColumnName']);
    }

    return $field;
  }
  // phpcs:enable

  // phpcs:disable
  /**
   * Get the data type from an array. Defaults to 'data_type' with fallback to
   * mapping for the integer value 'type'
   *
   * @param array $data
   *
   * @return string
   */
  private static function getDataType(array $data) {
    $dataType = $data['data_type'] ?? $data['dataType'] ?? NULL;
    if (isset($dataType)) {
      return !empty($data['time_format']) ? 'Timestamp' : $dataType;
    }

    $dataTypeInt = $data['type'] ?? NULL;
    $dataTypeName = \CRM_Utils_Type::typeToString($dataTypeInt);

    return $dataTypeName === 'Int' ? 'Integer' : $dataTypeName;
  }
  // phpcs:enable

  // phpcs:disable
  /**
   * Copied from \Civi\Api4\Service\Spec\SpecFormatter
   *
   * @param \Civi\Api4\Service\Spec\FieldSpec $fieldSpec
   * @param array $data
   * @param string $dataTypeName
   */
  private static function setInputTypeAndAttrs(FieldSpec $fieldSpec, $data, $dataTypeName) {
    $inputType = $data['html']['type'] ?? $data['html_type'] ?? NULL;
    $inputAttrs = $data['html'] ?? [];
    unset($inputAttrs['type']);
    // Custom field EntityRef or ContactRef filters
    if (is_string($data['filter'] ?? NULL) && strpos($data['filter'], '=')) {
      $filters = explode('&', $data['filter']);
      $inputAttrs['filter'] = $filters;
    }

    $map = [
      'Select Date' => 'Date',
      'Link' => 'Url',
      'Autocomplete-Select' => 'EntityRef',
    ];
    $inputType = $map[$inputType] ?? $inputType;
    if ($dataTypeName === 'ContactReference' || $dataTypeName === 'EntityReference') {
      $inputType = 'EntityRef';
    }
    if (in_array($inputType, ['Select', 'EntityRef'], TRUE) && !empty($data['serialize'])) {
      $inputAttrs['multiple'] = TRUE;
    }
    if ($inputType == 'Date' && !empty($inputAttrs['formatType'])) {
      self::setLegacyDateFormat($inputAttrs);
    }
    // Number input for numeric fields
    if ($inputType === 'Text' && in_array($dataTypeName, ['Integer', 'Float'], TRUE)) {
      $inputType = 'Number';
      // Todo: make 'step' configurable for the custom field
      $inputAttrs['step'] = $dataTypeName === 'Integer' ? 1 : .01;
    }
    if ($inputType == 'Text' && !empty($data['maxlength'])) {
      $inputAttrs['maxlength'] = (int) $data['maxlength'];
    }
    if ($inputType == 'TextArea') {
      foreach (['rows', 'cols', 'note_rows', 'note_columns'] as $prop) {
        if (!empty($data[$prop])) {
          $key = str_replace('note_', '', $prop);
          // per @colemanw https://github.com/civicrm/civicrm-core/pull/28388#issuecomment-1835717428
          $key = str_replace('columns', 'cols', $key);
          $inputAttrs[$key] = (int) $data[$prop];
        }
      }
    }
    // Ensure all keys use lower_case not camelCase
    foreach ($inputAttrs as $key => $val) {
      if ($key !== strtolower($key)) {
        unset($inputAttrs[$key]);
        $key = \CRM_Utils_String::convertStringToSnakeCase($key);
        $inputAttrs[$key] = $val;
      }
      // Format EntityRef filter property (core and custom fields)
      if ($key === 'filter' && is_array($val)) {
        $filters = [];
        foreach ($val as $filter) {
          [$k, $v] = explode('=', $filter);
          // Explode comma-separated values
          $filters[$k] = strpos($v, ',') ? explode(',', $v) : $v;
        }
        // Legacy APIv3 custom field stuff
        if ($dataTypeName === 'ContactReference') {
          if (!empty($filters['group'])) {
            $filters['groups'] = $filters['group'];
          }
          unset($filters['action'], $filters['group']);
        }
        $inputAttrs['filter'] = $filters;
      }
    }
    // Custom autocompletes
    if (!empty($data['option_group_id']) && $inputType === 'EntityRef') {
      $fieldSpec->setFkEntity('OptionValue');
      $inputAttrs['filter']['option_group_id'] = $data['option_group_id'];
    }
    $fieldSpec
      ->setInputType($inputType)
      ->setInputAttrs($inputAttrs);
  }
  // phpcs:enable

  // phpcs:disable
  /**
   * Copied from \Civi\Api4\Service\Spec\SpecFormatter
   *
   * @param array $inputAttrs
   */
  private static function setLegacyDateFormat(&$inputAttrs) {
    if (empty(\Civi::$statics['legacyDatePrefs'][$inputAttrs['formatType']])) {
      \Civi::$statics['legacyDatePrefs'][$inputAttrs['formatType']] = [];
      $params = ['name' => $inputAttrs['formatType']];
      \CRM_Core_DAO::commonRetrieve('CRM_Core_DAO_PreferencesDate', $params, \Civi::$statics['legacyDatePrefs'][$inputAttrs['formatType']]);
    }
    $dateFormat = \Civi::$statics['legacyDatePrefs'][$inputAttrs['formatType']];
    unset($inputAttrs['formatType']);
    $inputAttrs['time'] = !empty($dateFormat['time_format']);
    $inputAttrs['date'] = TRUE;
    $inputAttrs['start_date_years'] = (int) $dateFormat['start'];
    $inputAttrs['end_date_years'] = (int) $dateFormat['end'];
  }
  // phpcs:enable

  /**
   * Copied from \Civi\Api4\Service\Spec\SpecGatherer::setDynamicFk()
   *
   * @param array{name: string, FKClassName: string, bao: string, type: int, DFKEntities: array<mixed>} $DAOField
   * @param \Civi\Api4\Service\Spec\RequestSpec $spec
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
