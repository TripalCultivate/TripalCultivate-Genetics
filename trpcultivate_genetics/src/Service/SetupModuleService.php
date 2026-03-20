<?php

namespace Drupal\trpcultivate_genetics\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\tripal_chado\Database\ChadoConnection;
use Drupal\tripal_chado\Services\ChadoCustomTableManager;
use Drupal\tripal_chado\Services\ChadoTermsInit;
use Drupal\tripal\Services\TripalEntityTypeCollection;
use Drupal\tripal\Services\TripalFieldCollection;
use Drupal\tripal_layout\Controller\TripalEntityUILayoutController;

/**
 * Service class for setting up the Tripal Cultivate Genetics module.
 */
class SetupModuleService {

  /**
   * The entity type manager.
   *
   * @var Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The database connection for querying Chado.
   *
   * @var Drupal\tripal_chado\Database\ChadoConnection
   */
  protected ChadoConnection $chado_connection;

  /**
   * The Tripal Chado custom tables service.
   *
   * @var Drupal\tripal_chado\Services\ChadoCustomTableManager
   */
  protected ChadoCustomTableManager $custom_tables;

  /**
   * The Tripal Chado terms init service.
   *
   * @var Drupal\tripal_chado\Services\ChadoTermsInit
   */
  protected ChadoTermsInit $terms_init;

  /**
   * The Tripal entity type collection.
   *
   * @var Drupal\tripal\Services\TripalEntityTypeCollection
   */
  protected TripalEntityTypeCollection $entityTypeCollection;

  /**
   * The Tripal field collection.
   *
   * @var Drupal\tripal\Services\TripalFieldCollection
   */
  protected TripalFieldCollection $fieldCollection;

  /**
   * Constructor for the service.
   *
   * @param Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param Drupal\tripal_chado\Database\ChadoConnection $chado_connection
   *   The database connection for querying Chado.
   * @param Drupal\tripal_chado\Services\ChadoCustomTableManager $custom_tables
   *   The Tripal Chado custom tables service.
   * @param Drupal\tripal_chado\Services\ChadoTermsInit $terms_init
   *   The Tripal Chado terms init service.
   * @param Drupal\tripal\Services\TripalEntityTypeCollection $entityTypeCollection
   *   The Tripal entity type collection.
   * @param Drupal\tripal\Services\TripalFieldCollection $fieldCollection
   *   The Tripal field collection.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    ChadoConnection $chado_connection,
    ChadoCustomTableManager $custom_tables,
    ChadoTermsInit $terms_init,
    TripalEntityTypeCollection $entityTypeCollection,
    TripalFieldCollection $fieldCollection,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->chado_connection = $chado_connection;
    $this->custom_tables = $custom_tables;
    $this->terms_init = $terms_init;
    $this->entityTypeCollection = $entityTypeCollection;
    $this->fieldCollection = $fieldCollection;
  }

  /**
   * Create custom tables needed by this module in the default chado instance.
   */
  public function createCustomTables() {
    $schema = [
      'table' => 'featuremap_analysis',
      'description' => 'Maps analysis to the featuremaps they generated.',
      'fields' => [
        'featuremap_analysis_id' => [
          'type' => 'serial',
          'not null' => TRUE,
        ],
        'featuremap_id' => [
          'size' => 'big',
          'type' => 'int',
          'not null' => TRUE,
        ],
        'analysis_id' => [
          'size' => 'big',
          'type' => 'int',
          'not null' => TRUE,
        ],
      ],
      'primary key' => [
        'featuremap_analysis_id',
      ],
      'indexes' => [
        'featuremap_analysis_idx1' => [
          0 => 'featuremap_id',
        ],
        'featuremap_analysis_idx2' => [
          0 => 'analysis_id',
        ],
      ],
      'foreign keys' => [
        'featuremap' => [
          'table' => 'featuremap',
          'columns' => [
            'featuremap_id' => 'featuremap_id',
          ],
        ],
        'analysis' => [
          'table' => 'analysis',
          'columns' => [
            'analysis_id' => 'analysis_id',
          ],
        ],
      ],
    ];
    $custom_table = $this->custom_tables->create('featuremap_analysis', $this->chado_connection);
    $custom_table->setTableSchema($schema);
    $custom_table->setLocked(TRUE);
  }

  /**
   * Insert terms needed by this module into the default chado instance.
   *
   * Expected but not required to be run by a Tripal Job.
   */
  public function installTerms() {
    $config_id = 'trpcultivate_genetic_terms';
    $this->terms_init->installTerms($config_id);
  }

  /**
   * Import content types and fields used by this module.
   *
   * Expected but not required to be run by a Tripal Job.
   */
  public function importContenttypes() {
    $collections = [
      'trpcultivate_genetic',
    ];

    // Import the content types.
    $this->entityTypeCollection->install($collections);

    // Import the fields.
    $this->fieldCollection->install($collections);

    // Apply the default layouts.
    $this->applyLayout();
  }

  /**
   * Apply the default layouts for this module.
   */
  public function applyLayout() {

    $bundle = 'genetic_map';
    $genetic_map = $this->entityTypeManager->getStorage('tripal_entity_type')->load($bundle);

    // Automatically apply both layouts.
    $controller = new TripalEntityUILayoutController();
    $controller->applyViewLayout($genetic_map);
    $controller->applyFormLayout($genetic_map);

    // Now modify the form display.
    $config_entity_storage = $this->entityTypeManager->getStorage('entity_form_display');
    $display = $config_entity_storage->load('tripal_entity.' . $bundle . '.default');

    // -- Set a number of properties to use the "Short Text" widget.
    $property_fields = [
      'genetic_map_identifier', 'genetic_map_population_name',
      'genetic_map_population_type', 'genetic_map_population_size', 'genetic_map_year_published',
      'genetic_map_analysis',
    ];
    foreach ($property_fields as $component_name) {
      $options = $display->getComponent($component_name);
      $options['type'] = 'chado_property_string_widget_default';
      $options['settings'] = [];
      $display->setComponent($component_name, $options);
    }

    // -- Expand rows for a few longer description fields.
    $fields = ['genetic_map_description'];
    foreach ($fields as $component_name) {
      $options = $display->getComponent($component_name);
      $options['settings']['num_rows'] = 6;
      $display->setComponent($component_name, $options);
    }

    // -- Finally save it.
    $display->save();
  }

  /**
   * Runs all setup tasks for this module.
   *
   * Expected to be run by a Tripal Job.
   */
  public static function runSetupModuleTripalJob($job_id) {

    // Get the service.
    $service = \Drupal::service('trpcultivate_genetics.setup_module_service');

    // Create custom tables needed by this module.
    $service->createCustomTables();

    // Submit job to install terms needed by this module.
    $service->installTerms();

    // Submit job to import content types and fields used by this module.
    $service->importContenttypes();
  }

}
