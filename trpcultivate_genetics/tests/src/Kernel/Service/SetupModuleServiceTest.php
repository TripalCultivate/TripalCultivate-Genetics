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
    'views',
    'field',
    'file',
    'field_ui',
    'field_group',
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

    // Add any schema needed for the functionality I am testing.
    $this->prepareEnvironment(['TripalEntity', 'TripalTerm']);

    $this->installConfig(['trpcultivate', 'trpcultivate_genetics']);

    $this->installSchema('tripal', ['tripal_jobs', 'tripal_collection']);
    $this->installSchema('tripal_chado', ['tripal_custom_tables']);
    $this->installConfig('tripal_chado');
    // ... we need the layout entities for our content types.
    $this->installEntitySchema('tripal_layout_default_form');
    $this->installEntitySchema('tripal_layout_default_view');

    // Initialize the chado instance with all the records
    // that would be present after running prepare.
    $this->chado_connection = $this->getTestSchema(ChadoTestKernelBase::PREPARE_TEST_CHADO);

    // ... we need our own modules config.
    $this->setupService = \Drupal::service('trpcultivate_genetics.setup_module_service');
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

  /**
   * Test the intallTerms method and installContentTypes method.
   */
  public function testInstallMethods() {
    // Call the method to create the custom tables (i.e. featuremap_analysis).
    $this->setupService->createCustomTables();

    // Install terms defined in tripal.
    $terms_setup = \Drupal::service('tripal_chado.terms_init');
    $terms_setup->installTerms();

    // Install the terms defined in this module.
    $this->setupService->installTerms();

    // Check if the terms are installed properly.
    $idsmanager = \Drupal::service('tripal.collection_plugin_manager.idspace');
    $idSpace = $idsmanager->loadCollection('local');
    $term_id = $idSpace->getTerm('Population Name');
    $this->assertNotNull($term_id, 'The terms are not installed properly.');

    // Import the content types defined in this module.
    $this->setupService->importContenttypes();

    // Test if the content types are imported successfully.
    $entity_type_manager = \Drupal::entityTypeManager();
    $entity_type = $entity_type_manager->getStorage('tripal_entity_type')->load('genetic_map');

    $this->assertNotNull($entity_type, 'The genetic map content type was not installed successfully.');

    // Test if the field types are imported successfully.
    $field_manager = \Drupal::service('entity_field.manager');
    $field_defs = $field_manager->getFieldDefinitions('tripal_entity', 'genetic_map');

    $fields = ['genetic_map_identifier', 'genetic_map_name', 'genetic_map_population_name',
      'genetic_map_population_type', 'genetic_map_population_size', 'genetic_map_year_published', 'genetic_map_unit_type', 'genetic_map_type', 'genetic_map_author', 'genetic_map_organism', 'genetic_map_stock', 'genetic_map_dataset_file', 'genetic_map_dataset', 'genetic_map_pub', 'genetic_map_dbxref_ann', 'genetic_map_analysis', 'genetic_map_description',
    ];

    foreach ($fields as $field_id) {
      $this->assertArrayHasKey($field_id, $field_defs, "The field $field_id was not created successfully.");
    }
  }

}
