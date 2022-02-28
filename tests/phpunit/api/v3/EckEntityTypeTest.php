<?php

use Civi\Test\HeadlessInterface;
use Civi\Test\HookInterface;
use Civi\Test\TransactionalInterface;

/**
 * EckEntityType API Test Case
 * @group headless
 */
class api_v3_EckEntityTypeTest extends \PHPUnit\Framework\TestCase implements HeadlessInterface, HookInterface {
  use \Civi\Test\Api3TestTrait;

  protected $_apiversion = 3;

  /**
   * Set up for headless tests.
   *
   * Civi\Test has many helpers, like install(), uninstall(), sql(), and sqlFile().
   *
   * See: https://docs.civicrm.org/dev/en/latest/testing/phpunit/#civitest
   */
  public function setUpHeadless() {
    return \Civi\Test::headless()
      ->installMe(__DIR__)
      ->apply();
  }

  /**
   * Test basic APIv3 functionality
   */
  public function testCreateGetDelete() {
    $entityType = $this->callAPISuccess('EckEntityType', 'create', [
      'label' => 'TestAPIv3',
    ]);
    $this->assertTrue(is_numeric($entityType['id']));

    $get = $this->callAPISuccess('EckEntityType', 'get', ['name' => 'TestAPIv3']);
    $this->assertEquals(1, $get['count']);

    // Table should have been created
    $this->assertTrue(\CRM_Core_DAO::checkTableExists('civicrm_eck_testapiv3'));

    $records = $this->callAPISuccess('EckTestAPIv3', 'get');
    $this->assertEquals(0, $records['count']);

    // Create entities
    $this->callAPISuccess('EckTestAPIv3', 'create', [
      'title' => 'Abc',
    ]);
    $this->callAPISuccess('EckTestAPIv3', 'create', [
      'title' => 'Def',
    ]);

    $records = $this->callAPISuccess('EckTestAPIv3', 'get');
    $this->assertEquals(2, $records['count']);

    $this->callAPISuccess('EckEntityType', 'delete', [
      'id' => $entityType['id'],
    ]);

    // Table should have been dropped
    $this->assertFalse(\CRM_Core_DAO::checkTableExists('civicrm_eck_testapiv3'));
  }

}
