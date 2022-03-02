<?php
namespace api\v4\EckEntity;

use Civi\Api4\CustomField;
use Civi\Api4\CustomGroup;
use Civi\Api4\EckEntityType;
use Civi\Api4\OptionValue;
use Civi\Test\HeadlessInterface;

/**
 * @group headless
 */
class EckEntityTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface {

  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  public function testCreateEntityType():void {
    $entityType = EckEntityType::create(FALSE)
      ->addValue('label', 'Test One Type')
      ->execute()->first();

    // Name should have been auto-derived from label
    $this->assertEquals('Test_One_Type', $entityType['name']);

    // APIv4 entity should now exist
    $newEntity = \Civi\Api4\Entity::get(FALSE)
      ->addWhere('name', '=', 'Eck_Test_One_Type')
      ->execute()->single();
    $this->assertEquals('Test One Type', $newEntity['title']);
    $this->assertEquals('secondary', $newEntity['searchable']);
    $this->assertEquals('title', $newEntity['label_field']);
    $this->assertContains('EckEntity', $newEntity['type']);

    // Table should have been created
    $this->assertTrue(\CRM_Core_DAO::checkTableExists('civicrm_eck_test_one_type'));

    // CustomGroup.extends should contain new entity
    $extends = CustomGroup::getFields(FALSE)
      ->setLoadOptions(TRUE)
      ->addWhere('name', '=', 'extends')
      ->execute()->single();
    $this->assertArrayHasKey('Eck_Test_One_Type', $extends['options']);

    // Delete the entity type
    $deleted = EckEntityType::delete(FALSE)
      ->addWhere('name', '=', 'Test_One_Type')
      ->execute();
    $this->assertCount(1, $deleted);

    // APIv4 entity should no longer exist
    $entities = \Civi\Api4\Entity::get(FALSE)
      ->addWhere('name', '=', 'Eck_Test_One_Type')
      ->execute();
    $this->assertCount(0, $entities);

    // Table should have been dropped
    $this->assertFalse(\CRM_Core_DAO::checkTableExists('civicrm_eck_test_one_type'));

    // CustomGroup.extends should not contain deleted entity
    $extends = CustomGroup::getFields(FALSE)
      ->setLoadOptions(TRUE)
      ->addWhere('name', '=', 'extends')
      ->execute()->single();
    $this->assertArrayNotHasKey('Eck_Test_One_Type', $extends['options']);
  }

  public function testRenameEntityType():void {
    $entityType = EckEntityType::create(FALSE)
      ->addValue('label', 'Test Two Type')
      ->execute()->single();

    $edited = EckEntityType::update(FALSE)
      ->addValue('id', $entityType['id'])
      // Setting the same name as before is allowed
      ->addValue('name', 'Test_Two_Type')
      ->addValue('label', 'Different label')
      ->execute()->single();
    $this->assertEquals('Different label', $edited['label']);
    $this->assertEquals('Test_Two_Type', $edited['name']);

    try {
      EckEntityType::update(FALSE)
        ->addValue('id', $entityType['id'])
        // Changing name is not allowed
        ->addValue('name', 'Something_else')
        ->execute();
      $this->fail();
    }
    catch (\Exception $e) {
    }
    $this->assertStringContainsString('not allowed', $e->getMessage());
  }

  public function testTwoEntityTypes() {
    $firstEntity = $this->createEntity(['one' => 'One', 'two' => 'Two']);
    $secondEntity = $this->createEntity(['one' => 'One', 'three' => 'Three']);

    $saved = civicrm_api4($firstEntity, 'save', [
      'checkPermissions' => FALSE,
      'records' => [
        ['title' => 'Abc', 'subtype:name' => 'one'],
        ['title' => 'Def', 'subtype:name' => 'two'],
      ],
    ]);
    $this->assertCount(2, $saved);

    $saved = civicrm_api4($secondEntity, 'save', [
      'checkPermissions' => FALSE,
      'records' => [
        ['title' => 'Abc', 'subtype:name' => 'one'],
        ['title' => 'Def', 'subtype:name' => 'three'],
      ],
    ]);
    $this->assertCount(2, $saved);

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
    $this->assertCount(2, $firstRecords);
    $this->assertEquals('Abc', $firstRecords[0]['title']);
    $this->assertEquals('one', $firstRecords[0]['subtype:name']);
    $this->assertEquals('Def', $firstRecords[1]['title']);
    $this->assertEquals('two', $firstRecords[1]['subtype:name']);

    $deleted = civicrm_api4($firstEntity, 'delete', [
      'checkPermissions' => FALSE,
      'where' => [['subtype:name', '=', 'one']],
    ]);
    $this->assertCount(1, $deleted);

    $firstRecordCount = civicrm_api4($firstEntity, 'get', [
      'checkPermissions' => FALSE,
      'select' => ['row_count'],
    ]);
    $this->assertCount(1, $firstRecordCount);

    $secondRecords = civicrm_api4($secondEntity, 'get', [
      'select' => ['title', 'subtype:name'],
      'checkPermissions' => FALSE,
      'orderBy' => ['subtype' => 'ASC'],
    ]);
    $this->assertCount(2, $secondRecords);
    $this->assertEquals('New', $secondRecords[0]['title']);
    $this->assertEquals('one', $secondRecords[0]['subtype:name']);
    $this->assertEquals('Def', $secondRecords[1]['title']);
    $this->assertEquals('three', $secondRecords[1]['subtype:name']);
  }

  public function testEntityCustomFields() {
    $entityName = $this->createEntity(['one' => 'One', 'two' => 'Two']);

    $fields = (array) civicrm_api4($entityName, 'getFields', [
      'checkPermissions' => FALSE,
      'loadOptions' => ['id', 'name'],
    ], 'name');
    $this->assertEquals($entityName, $fields['title']['entity']);
    $this->assertEquals('civicrm_' . strtolower($entityName), $fields['id']['table_name']);

    $subTypeKeys = array_column($fields['subtype']['options'], 'id', 'name');

    $this->assertEquals(['one', 'two'], array_keys($subTypeKeys));

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
    $this->assertEquals('Custom', $fields['My_Entity_Fields.MyField1']['type']);
    $this->assertArrayHasKey('One_Subtype_Fields.MyField2', $fields);

    $subTypeOneFields = civicrm_api4($entityName, 'getFields', [
      'checkPermissions' => FALSE,
      'values' => ['subtype' => $subTypeKeys['one']],
    ], 'name');
    $this->assertArrayHasKey('One_Subtype_Fields.MyField2', $subTypeOneFields);

    $subTypeTwoFields = civicrm_api4($entityName, 'getFields', [
      'checkPermissions' => FALSE,
      'values' => ['subtype' => $subTypeKeys['two']],
    ], 'name');
    // FIXME: This is actually a bug in CiviCRM-core.
    // @see https://github.com/civicrm/civicrm-core/pull/22827
    // $this->assertArrayNotHasKey('One_Subtype_Fields.MyField2', $subTypeTwoFields);
  }

  private function createEntity(array $subTypes) {
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
