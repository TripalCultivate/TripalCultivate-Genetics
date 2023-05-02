<?php

namespace Drupal\Tests\trpcultivate_genotypes\Functional\GenotypesLoader;

use Drupal\Core\Url;
use Drupal\Tests\tripal_chado\Functional\ChadoTestBrowserBase;
use Drupal\Tests\trpcultivate_genotypes\Functional\GenotypesLoader\Subclass\GenotypesLoaderFakePlugin;
use Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderPluginBase;

/**
 * A test to call the methods in the plugin base for the genotypes loader.
 *
 * @group TripGeno Genetics
 * @group Genotypes Loader
 */
class GenotypesLoaderBasePluginTest extends ChadoTestBrowserBase {

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
  public function testGenotypesLoaderPluginBaseGetSet(){

    // Create the Genotypes Loader object
    // Configuration should be any key value pairs specific to Genotypes Loader plugin
    $configuration = [];
    $plugin_definition = [];
    $plugin = new GenotypesLoaderFakePlugin($configuration,"fake_genotypes_loader",$plugin_definition);
    $this->assertIsObject($plugin, 'Unable to create a Plugin');

    // Ensure we see all logging in tests.
    \Drupal::state()->set('is_a_test_environment', TRUE);

    // Open connection to Chado
    $connection = $this->createTestSchema(ChadoTestBrowserBase::PREPARE_TEST_CHADO);

    // Create an organism
    $organism_id = $connection->insert('1:organism')
      ->fields(['genus', 'species'])
      ->values([
        'genus' => 'Tripalus',
        'species' => 'databasica',
      ])
      ->execute();

    // Test the set method for Organism ID
    $success = $plugin->setOrganismID($organism_id);
    $this->assertTrue($success, "Unable to set organism_id");

    // Test the getter method
    $grabbed_organism_id = $plugin->getOrganismID();
    $this->assertEquals($organism_id, $grabbed_organism_id, "The organism_id using the getter method does not match.");

    // Create a project
    $project_id = $connection->insert('1:project')
      ->fields(['name'])
      ->values([
        'name' => 'Test Project',
      ])
      ->execute();

    // Set method for Project ID
    $success = $plugin->setProjectID($project_id);
    $this->assertTrue($success, "Unable to set project_id");

    // Get method for Project ID
    $grabbed_project_id = $plugin->getProjectID();
    $this->assertEquals($project_id, $grabbed_project_id, "The project_id using the getter method does not match.");

    // Grab the cvterm ID for Sequence Variant
    $variant_cvterm_id = $this->getCvtermID('SO', '0001060');

    // Set method for Variant Subtype ID
    $success = $plugin->setVariantSubTypeID($variant_cvterm_id);
    $this->assertTrue($success, "Unable to set cvterm_id for variant subtype");
    
    // Get method for Variant Subtype ID
    $grabbed_variant_cvterm_id = $plugin->getVariantSubTypeID();
    $this->assertEquals($variant_cvterm_id, $grabbed_variant_cvterm_id, "The variant_cvterm_id using the getter method does not match.");

    // Grab the cvterm ID for Genetic Marker
    $marker_cvterm_id = $this->getCvtermID('SO', '0001645');

    // Set method for Marker Subtype ID
    $success = $plugin->setMarkerSubTypeID($marker_cvterm_id);
    $this->assertTrue($success, "Unable to set cvterm_id for marker subtype");
    
    // Get method for Marker Subtype ID
    $grabbed_marker_cvterm_id = $plugin->getMarkerSubTypeID();
    $this->assertEquals($marker_cvterm_id, $grabbed_marker_cvterm_id, "The marker_cvterm_id using the getter method does not match.");

    // Input Filepath
    $input_file_path = __DIR__ . '/../../Fixtures/cats.vcf';

    // Set input filepath
    $success = $plugin->setInputFilepath($input_file_path);
    $this->assertTrue($success, "Unable to set input filepath");

    // Get input filepath
    $grabbed_input_file_path = $plugin->getInputFilepath();
    $this->assertEquals($input_file_path, $grabbed_input_file_path, "The input filepath grabbed by the getter method does not match.");

    // Sample Filepath
    $sample_file_path = __DIR__ . '/../../Fixtures/cats_samples.tsv';

    // Set sample filepath
    $success = $plugin->setSampleFilepath($sample_file_path);
    $this->assertTrue($success, "Unable to set sample filepath");

    // Get sample filepath
    $grabbed_sample_file_path = $plugin->getSampleFilepath();
    $this->assertEquals($sample_file_path, $grabbed_sample_file_path, "The sample filepath grabbed by the getter method does not match.");
  }
}