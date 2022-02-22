<?php
namespace api\v4\EckEntity;

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

  public function testCreateEntityType() {
    $entityType = EckEntityType::create(FALSE)
      ->addValue('label', 'Test One Type')
      ->execute()->first();

    // Name should have been auto-derived from label
    $this->assertEquals('Test_One_Type', $entityType['name']);

    $newEntity = \Civi\Api4\Entity::get(FALSE)
      ->addWhere('name', '=', 'EckTest_One_Type')
      ->execute()->single();

    $deleted = EckEntityType::delete(FALSE)
      ->addWhere('name', '=', 'Test_One_Type')
      ->execute();
    $this->assertCount(1, $deleted);

    $entities = \Civi\Api4\Entity::get(FALSE)
      ->addWhere('name', '=', 'EckTest_One_Type')
      ->execute();
    $this->assertCount(0, $entities);
  }

}
