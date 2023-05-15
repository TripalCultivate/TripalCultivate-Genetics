<?php

namespace Drupal\Tests\trpcultivate_genotypes\Functional\GenotypesLoader;

use Drupal\Core\Url;
use Drupal\Tests\tripal_chado\Functional\ChadoTestBrowserBase;
use Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderPluginManager;
use Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderInterface;
use Drupal\trpcultivate_genotypes\Plugin\GenotypesLoader\VCFGenotypesLoader;

/**
 * A test to call the methods in the plugin base for the genotypes loader.
 *
 * @group TripGeno Genetics
 * @group Genotypes Loader
 */
class GenotypesLoaderPluginManagerTest extends ChadoTestBrowserBase {

  protected $defaultTheme = 'stable';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['trpcultivate_genotypes'];

  /**
   * Test a fake instance of Genotypes Loader Plugin.
   * 
   * @group GenotypesLoader
   */
  public function testGenotypesLoaderPluginManager(){
    
    // Ensure we see all logging in tests.
    \Drupal::state()->set('is_a_test_environment', TRUE);

    // Open connection to Chado
    $connection = $this->createTestSchema(ChadoTestBrowserBase::PREPARE_TEST_CHADO);

    // Grab our plugin manager
    $plugin_manager = \Drupal::service('trpcultivate_genotypes.genotypes_loader');
    $this->assertIsObject($plugin_manager, 'Unable to create a Plugin Manager');
    $this->assertInstanceOf(GenotypesLoaderPluginManager::class, $plugin_manager,"Returned object is not an instance of GenotypesLoaderPluginManager.");

    // Start adding to the database what we need in order to build our options array
    // Create an organism
    $organism_id = $connection->insert('1:organism')
      ->fields(['genus', 'species'])
      ->values([
        'genus' => 'Tripalus',
        'species' => 'databasica',
      ])
      ->execute();

    // Create a project
    $project_id = $connection->insert('1:project')
      ->fields(['name'])
      ->values([
        'name' => 'Test Project',
      ])
      ->execute();

    // Grab the cvterm ID for Sequence Variant
    $variant_cvterm_id = $this->getCvtermID('SO', '0001060');

    // Grab the cvterm ID for Genetic Marker
    $marker_cvterm_id = $this->getCvtermID('SO', '0001645');

    // Assign the file type of the input file
    $input_file_type = 'vcf';

    // Input Filepath
    $input_file_path = __DIR__ . '/../../Fixtures/cats.vcf';

    // Sample Filepath
    $sample_file_path = __DIR__ . '/../../Fixtures/cats_samples.tsv';

    $options = array(
      "organism_id" => $organism_id,
      "project_id" => $project_id,
      "variant_subtype_id" => $variant_cvterm_id,
      "marker_subtype_id" => $marker_cvterm_id,
      "input_file_type" => $input_file_type,
      "input_filepath" => $input_file_path,
      "sample_filepath" => $sample_file_path
    );

    // Use our plugin manager to create a plugin and set its parameters
    $plugin = $plugin_manager->setParameters($options);

    // Test that our Plugin Manager is the VCFGenotypesLoader type
    $grabbed_input_file_type = $plugin->getInputFileType();
    $this->assertEquals($input_file_type, $grabbed_input_file_type, "The input file type is not the expected type of VCF.");
    $this->assertInstanceOf(VCFGenotypesLoader::class, $plugin,"Returned object is not an instance of VCFGenotypesLoader.");

    // Test with an invalid file type and use a try-catch to ensure an exception was thrown
    $invalid_input_file_type = 'goose';
    $options["input_file_type"] = $invalid_input_file_type;

    $exception_caught = false;
    try {
      $new_plugin = $plugin_manager->setParameters($options);
    } 
    catch ( \Exception $e ) {
      $exception_caught = true;
    }
    $this->assertTrue($exception_caught);
  }
}
