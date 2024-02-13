<?php

namespace api\v4\EckEntity;

use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Api4\EckEntityType;
use Civi\Api4\OptionValue;
use Civi\Test\CiviEnvBuilder;
use Civi\Test\HeadlessInterface;

/**
 * @group headless
 */
class EckEntityTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface {

  public function setUpHeadless(): CiviEnvBuilder {
    return \Civi\Test::headless()
      ->install(['de.systopia.eck', 'org.civicrm.search_kit'])
      ->apply();
  }

  public function testCreateEntityType():void {
    $entityType = EckEntityType::create(FALSE)
      ->addValue('label', 'Test One Type')
      ->addValue('icon', 'fa-random')
      ->execute()->first();

    /**
     * Name should have been auto-derived from label
     * @var array{name: string} $entityType
     */
    self::assertEquals('Test_One_Type', $entityType['name']);

    // APIv4 entity should now exist
    $newEntity = \Civi\Api4\Entity::get(FALSE)
      ->addWhere('name', '=', 'Eck_Test_One_Type')
      ->execute()->single();
    self::assertEquals('Test One Type', $newEntity['title']);
    self::assertEquals('secondary', $newEntity['searchable']);
    self::assertEquals('title', $newEntity['label_field']);
    self::assertEquals('civicrm_eck_test_one_type', $newEntity['table_name']);
    self::assertEquals('fa-random', $newEntity['icon']);
    self::assertContains('EckEntity', $newEntity['type']);

    // Table should have been created
    self::assertTrue(\CRM_Core_DAO::checkTableExists('civicrm_eck_test_one_type'));

    // CustomGroup.extends should contain new entity
    $extends = CustomGroup::getFields(FALSE)
      ->setLoadOptions(TRUE)
      ->addWhere('name', '=', 'extends')
      ->execute()->single();
    self::assertArrayHasKey('Eck_Test_One_Type', $extends['options']);

    // Delete the entity type
    $deleted = EckEntityType::delete(FALSE)
      ->addWhere('api_name', '=', 'Eck_Test_One_Type')
      ->execute();
    self::assertCount(1, $deleted);

    // APIv4 entity should no longer exist
    $entities = \Civi\Api4\Entity::get(FALSE)
      ->addWhere('name', '=', 'Eck_Test_One_Type')
      ->execute();
    self::assertCount(0, $entities);

    // Table should have been dropped
    self::assertFalse(\CRM_Core_DAO::checkTableExists('civicrm_eck_test_one_type'));

    // CustomGroup.extends should not contain deleted entity
    $extends = CustomGroup::getFields(FALSE)
      ->setLoadOptions(TRUE)
      ->addWhere('name', '=', 'extends')
      ->execute()->single();
    self::assertArrayNotHasKey('Eck_Test_One_Type', $extends['options']);
  }

  public function testRenameEntityType():void {
    $entityType = EckEntityType::create(FALSE)
      ->addValue('label', 'Test Two Type')
      ->addValue('name', 'Test Two')
      ->execute()->single();

    // Name should have been munged
    self::assertEquals('Test_Two', $entityType['name']);

    $edited = EckEntityType::update(FALSE)
      ->addValue('id', $entityType['id'])
      // Setting the same name as before is allowed
      ->addValue('name', 'Test_Two')
      ->addValue('label', 'Different label')
      ->execute()->single();
    self::assertEquals('Different label', $edited['label']);
    self::assertEquals('Test_Two', $edited['name']);

    try {
      EckEntityType::update(FALSE)
        ->addValue('id', $entityType['id'])
        // Changing name is not allowed
        ->addValue('name', 'Something_else')
        ->execute();
      self::fail();
    }
    catch (\Exception $e) {
      // @ignoreException
    }
    self::assertStringContainsString('not allowed', $e->getMessage());
  }

  public function testTwoEntityTypes(): void {
    $firstEntity = $this->createEntity(['one' => 'One', 'two' => 'Two']);
    $secondEntity = $this->createEntity(['one' => 'One', 'three' => 'Three']);

    // Ensure api_name and sub_types are correctly returned from the API
    $entityTypes = \Civi\Api4\EckEntityType::get(FALSE)
      ->addSelect('api_name', 'sub_types:label', 'sub_types:name')
      ->execute()
      ->indexBy('api_name');
    self::assertEquals(['One', 'Two'], $entityTypes[$firstEntity]['sub_types:label']);
    self::assertEquals(['one', 'two'], $entityTypes[$firstEntity]['sub_types:name']);
    self::assertEquals(['One', 'Three'], $entityTypes[$secondEntity]['sub_types:label']);
    self::assertEquals(['one', 'three'], $entityTypes[$secondEntity]['sub_types:name']);

    $saved = civicrm_api4($firstEntity, 'save', [
      'checkPermissions' => FALSE,
      'records' => [
        ['title' => 'Abc', 'subtype:name' => 'one'],
        ['title' => 'Def', 'subtype:name' => 'two'],
      ],
    ]);
    self::assertCount(2, $saved);

    $saved = civicrm_api4($secondEntity, 'save', [
      'checkPermissions' => FALSE,
      'records' => [
        ['title' => 'Abc', 'subtype:name' => 'one'],
        ['title' => 'Def', 'subtype:name' => 'three'],
      ],
    ]);
    self::assertCount(2, $saved);

    civicrm_api4($secondEntity, 'update', [
      'checkPermissions' => FALSE,
      'where' => [['subtype:name', '=', 'one']],
      'values' => ['title' => 'New'],
    ]);

    $firstRecords = civicrm_api4($firstEntity, 'get', [
      'select' => ['title', 'subtype:name'],
      'checkPermissions' => FALSE,
      'orderBy' => ['subtype' => 'ASC'],
    ]);
    self::assertCount(2, $firstRecords);
    self::assertEquals('Abc', $firstRecords[0]['title']);
    self::assertEquals('one', $firstRecords[0]['subtype:name']);
    self::assertEquals('Def', $firstRecords[1]['title']);
    self::assertEquals('two', $firstRecords[1]['subtype:name']);

    $deleted = civicrm_api4($firstEntity, 'delete', [
      'checkPermissions' => FALSE,
      'where' => [['subtype:name', '=', 'one']],
    ]);
    self::assertCount(1, $deleted);

    $firstRecordCount = civicrm_api4($firstEntity, 'get', [
      'checkPermissions' => FALSE,
      'select' => ['row_count'],
    ]);
    self::assertCount(1, $firstRecordCount);

    $secondRecords = civicrm_api4($secondEntity, 'get', [
      'select' => ['title', 'subtype:name'],
      'checkPermissions' => FALSE,
      'orderBy' => ['subtype' => 'ASC'],
    ]);
    self::assertCount(2, $secondRecords);
    self::assertEquals('New', $secondRecords[0]['title']);
    self::assertEquals('one', $secondRecords[0]['subtype:name']);
    self::assertEquals('Def', $secondRecords[1]['title']);
    self::assertEquals('three', $secondRecords[1]['subtype:name']);
  }

  public function testEntityCustomFields(): void {
    $entityName = $this->createEntity(['one' => 'One', 'two' => 'Two']);

    $fields = (array) civicrm_api4($entityName, 'getFields', [
      'checkPermissions' => FALSE,
      'loadOptions' => ['id', 'name'],
    ], 'name');
    self::assertEquals($entityName, $fields['title']['entity']);
    self::assertEquals('civicrm_' . strtolower($entityName), $fields['id']['table_name']);

    $subTypeKeys = array_column($fields['subtype']['options'], 'id', 'name');

    self::assertEquals(['one', 'two'], array_keys($subTypeKeys));

    CustomGroup::create(FALSE)
      ->addValue('title', 'My Entity Fields')
      ->addValue('extends', $entityName)
      ->addChain('fields', CustomField::save()
        ->addDefault('html_type', 'Text')
        ->addDefault('custom_group_id', '$id')
        ->addRecord(['label' => 'MyField1'])
      )->execute();
    CustomGroup::create(FALSE)
      ->addValue('title', 'One Subtype Fields')
      ->addValue('extends', $entityName)
      ->addValue('extends_entity_column_value', [$subTypeKeys['one']])
      ->addChain('fields', CustomField::save()
        ->addDefault('custom_group_id', '$id')
        ->addDefault('html_type', 'Text')
        ->addRecord(['label' => 'MyField2'])
      )->execute();

    $fields = (array) civicrm_api4($entityName, 'getFields', [
      'checkPermissions' => FALSE,
    ], 'name');
    self::assertEquals('Custom', $fields['My_Entity_Fields.MyField1']['type']);
    self::assertArrayHasKey('One_Subtype_Fields.MyField2', $fields);

    $subTypeOneFields = civicrm_api4($entityName, 'getFields', [
      'checkPermissions' => FALSE,
      'values' => ['subtype' => $subTypeKeys['one']],
    ], 'name');
    self::assertArrayHasKey('One_Subtype_Fields.MyField2', $subTypeOneFields);
    self::assertArrayHasKey('My_Entity_Fields.MyField1', $subTypeOneFields);

    $subTypeTwoFields = civicrm_api4($entityName, 'getFields', [
      'checkPermissions' => FALSE,
      'values' => ['subtype' => $subTypeKeys['two']],
    ], 'name');
    self::assertArrayHasKey('My_Entity_Fields.MyField1', $subTypeTwoFields);
    self::assertArrayNotHasKey('One_Subtype_Fields.MyField2', $subTypeTwoFields);
  }

  /**
   * @param array<string,string> $subTypes
   *
   * @return string
   * @throws \CRM_Core_Exception
   * @throws \Civi\API\Exception\UnauthorizedException
   */
  private function createEntity(array $subTypes): string {
    $name = uniqid();
    $entityType = EckEntityType::create(FALSE)
      ->addValue('label', $name)
      ->execute()->first();

    // Create sub-types
    OptionValue::save(FALSE)
      ->addDefault('option_group_id:name', 'eck_sub_types')
      ->addDefault('grouping', $entityType['name'])
      ->setRecords(\CRM_Utils_Array::makeNonAssociative($subTypes, 'name', 'label'))
      ->execute();

    return 'Eck_' . $entityType['name'];
  }

}
