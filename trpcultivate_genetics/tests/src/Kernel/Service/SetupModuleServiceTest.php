<?php

namespace Drupal\Tests\trpcultivate_genetics\Kernel\Service;

use Drupal\Tests\tripal_chado\Kernel\ChadoTestKernelBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Drupal\tripal_chado\Database\ChadoConnection;

/**
 * Tests the setup of the Tripal Cultivate Genetics module.
 *
 * @group trpcultivate_genetics
 */
#[Group('trpcultivate_genetics')]
#[RunTestsInSeparateProcesses]
class SetupModuleServiceTest extends ChadoTestKernelBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'path',
    'path_alias',
    'field',
    'tripal',
    'tripal_chado',
    'tripal_layout',
    'trpcultivate',
    'trpcultivate_genetics',
  ];

  /**
   * Connection to Chado schema.
   *
   * @var Drupal\tripal_chado\Database\ChadoConnection
   */
  protected ChadoConnection $chado_connection;

  /**
   * The service.
   *
   * @var \Drupal\trpcultivate_genetics\Service\SetupModuleService
   */
  protected $setupService;

  /**
   * Sets up the test.
   */
  protected function setUp(): void {
    parent::setUp();

    // Ensure we see all logging in tests.
    \Drupal::state()->set('is_a_test_environment', TRUE);

    // Get Chado in place.
    $this->chado_connection = $this->getTestSchema(ChadoTestKernelBase::PREPARE_TEST_CHADO);
    $this->setupService = \Drupal::service('trpcultivate_genetics.setup_module_service');

    $this->installSchema('tripal_chado', ['tripal_custom_tables']);
  }

  /**
   * Tests the creation of custom tables.
   */
  public function testCreateCustomTables() {

    // Ensure the table doesn't exist before running the method.
    $this->assertFalse(
      $this->chado_connection->schema()->tableExists('featuremap_analysis'),
      'The featuremap_analysis table already exists.'
    );

    // Run the method to create custom tables.
    $this->setupService->createCustomTables();

    // Check that the table was created.
    $this->assertTrue(
      $this->chado_connection->schema()->tableExists('featuremap_analysis'),
      'The featuremap_analysis table was not created.'
    );
  }

}
