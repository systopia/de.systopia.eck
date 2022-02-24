<?php
namespace api\v4\EckEntity;

use Civi\Api4\CustomGroup;
use Civi\Api4\EckEntityType;
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
      ->addWhere('name', '=', 'EckTest_One_Type')
      ->execute()->single();
    $this->assertEquals('Test One Type', $newEntity['title']);

    // Table should have been created
    $this->assertTrue(\CRM_Core_DAO::checkTableExists('civicrm_eck_test_one_type'));

    // CustomGroup.extends should contain new entity
    $extends = CustomGroup::getFields(FALSE)
      ->setLoadOptions(TRUE)
      ->addWhere('name', '=', 'extends')
      ->execute()->single();
    $this->assertArrayHasKey('EckTest_One_Type', $extends['options']);

    // Delete the entity type
    $deleted = EckEntityType::delete(FALSE)
      ->addWhere('name', '=', 'Test_One_Type')
      ->execute();
    $this->assertCount(1, $deleted);

    // APIv4 entity should no longer exist
    $entities = \Civi\Api4\Entity::get(FALSE)
      ->addWhere('name', '=', 'EckTest_One_Type')
      ->execute();
    $this->assertCount(0, $entities);

    // Table should have been dropped
    $this->assertFalse(\CRM_Core_DAO::checkTableExists('civicrm_eck_test_one_type'));

    // CustomGroup.extends should not contain deleted entity
    $extends = CustomGroup::getFields(FALSE)
      ->setLoadOptions(TRUE)
      ->addWhere('name', '=', 'extends')
      ->execute()->single();
    $this->assertArrayNotHasKey('EckTest_One_Type', $extends['options']);
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

}
