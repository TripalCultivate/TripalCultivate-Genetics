<?php

namespace Drupal\Tests\trpcultivate_genotypes\Functional\GenotypesLoader;

use Drupal\Core\Url;
use Drupal\Tests\tripal_chado\Functional\ChadoTestBrowserBase;
use Drupal\Tests\trpcultivate_genotypes\Functional\GenotypesLoader\Subclass\GenotypesLoaderFakePlugin;
use Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderPluginBase;
use Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderInterface;

/**
 * A test to call the methods in the plugin base for the genotypes loader.
 *
 * @group TripGeno Genetics
 * @group Genotypes Loader
 */
class GenotypesLoaderBasePluginTest extends ChadoTestBrowserBase {

  protected $defaultTheme = 'stark';

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
  public function testGenotypesLoaderPluginBase(){

    // Ensure we see all logging in tests.
    \Drupal::state()->set('is_a_test_environment', TRUE);

    // Open connection to Chado
    $connection = $this->createTestSchema(ChadoTestBrowserBase::PREPARE_TEST_CHADO);

    // Create the Genotypes Loader object
    // Configuration should be any key value pairs specific to Genotypes Loader plugin
    $configuration = [];
    $plugin_definition = [];
    $logger = \Drupal::service('tripal.logger');
    $plugin = new GenotypesLoaderFakePlugin($configuration,"fake_genotypes_loader",$plugin_definition,$logger,$connection);
    $this->assertIsObject($plugin, 'Unable to create a Plugin');
    $this->assertInstanceOf(GenotypesLoaderInterface::class, $plugin,"Returned object is not an instance of GenotypesLoaderInterface.");

    // Check that dependency injection worked properly.
    // Since the database connection and logger are protected properties, we cannot test them directly.
    // As such, we will use PHP closures to access these properties for testing.
    //  -- Create a variable to store a copy of this test object for use within the closure.
    $that = $this;
    //  -- Create a closure (i.e. a function tied to a variable) that does not need any parameters.
    //     Within this function we will want all of the assertions we will use to test the private methods.
    //     Also, $this within the function will actually be the plugin object that you bind later (mind blown).
    $assertDependencyInjectionClosure = function ()  use ($that){
      $that->assertIsObject($this->connection,
        "The connection object in our plugin was not set properly.");
      $that->assertIsObject($this->logger,
        "The connection object in our plugin was not set properly.");
    };
    //  -- Now, bind our assertion closure to the $plugin object. This is what makes the plugin available
    //     inside the function.
    $doAssertDependencyInjectionClosure = $assertDependencyInjectionClosure->bindTo($plugin, get_class($plugin));
    //  -- Finally, call our bound closure function to run the assertions on our plugin.
    $doAssertDependencyInjectionClosure();

    // Create an organism
    $genus = "Tripalus";
    $species = "databasica";
    $organism_id = $connection->insert('1:organism')
      ->fields([
        'genus' => $genus,
        'species' => $species,
      ])
      ->execute();

    // Test the set method for Organism ID
    $success = $plugin->setOrganismID($organism_id);
    $this->assertTrue($success, "Unable to set organism_id");

    // Test the getter method
    $grabbed_organism_id = $plugin->getOrganismID();
    $this->assertEquals($organism_id, $grabbed_organism_id, "The organism_id using the getter method does not match.");

    // Create a project
    $project_name = "Test Project";
    $project_id = $connection->insert('1:project')
      ->fields([
        'name' => $project_name,
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

    // Assign the file type of the input file
    $input_file_type = 'vcf';

    // Set method for input file type
    $success = $plugin->setInputFileType($input_file_type);
    $this->assertTrue($success, "Unable to set file type for the input file");

    // Get method for input file type
    $grabbed_input_file_type = $plugin->getInputFileType();
    $this->assertEquals($input_file_type, $grabbed_input_file_type, "The input file type grabbed using the getter method does not match.");

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

    /****************************************************************************
     *  TESTS for method getRecordPkey()
     ****************************************************************************/
    // Get the primary key of the organism we previously created using getRecordPkey()
    $new_organism_id = $plugin->getRecordPkey('Organism', 'organism', 0, [
      'genus' => $genus,
      'species' => $species
    ]);
    $this->assertEquals($new_organism_id, $grabbed_organism_id, "The organism ID selected by getRecordPkey() does not match.");

    // Try to select a record using an invalid mode
    $exception_caught = FALSE;
    try {
      $plugin->getRecordPkey('Organism', 'organism', 5, [
        'genus' => $genus,
        'species' => $species
      ]);
    }
    catch ( \Exception $e ) { 
      $exception_caught = TRUE;
    }
    $this->assertTrue($exception_caught, "Did not catch exception for using an invalid mode (5).");

    // Try to insert a record that already exists and catch the expected exception
    $exception_caught = FALSE;
    try {
      $plugin->getRecordPkey('Organism', 'organism', 1, [
        'genus' => $genus,
        'species' => $species
      ]);
    } 
    catch ( \Exception $e ) { 
      $exception_caught = TRUE;
    }
    $this->assertTrue($exception_caught, "Did not catch exception for trying to insert a duplicate.");

    // Enter a duplicate record to trigger an exception
    $dup_organism_id = $connection->insert('1:organism')
      ->fields([
        'genus' => $genus,
        'species' => $species,
      ])
      ->execute();

    $exception_caught = FALSE;
    try {
      $plugin->getRecordPkey('Organism', 'organism', 2, [
        'genus' => $genus,
        'species' => $species
      ]);
    }
    catch ( \Exception $e ) { 
      $exception_caught = TRUE;
    }
    $this->assertTrue($exception_caught, "Did not catch exception for attempting to select/insert a record that was already duplicated.");

    // Try to insert an organism with improper values to trigger an exception
    $exception_caught = FALSE;
    try {
      $plugin->getRecordPkey('Organism', 'organism', 1, [
        'genus' => $genus,
        'species' => $species,
        'blah' => "blah"
      ]);
    }
    catch ( \Exception $e ) { 
      $exception_caught = TRUE;
    }
    $this->assertTrue($exception_caught, "Did not catch exception for inserting a record with an invalid field.");

    // Test if we can select a record that does not exist, and catch the exception 
    $exception_caught = FALSE;
    try {
      $plugin->getRecordPkey('Organism', 'organism', 0, [
        'genus' => "Felis",
        'species' => "silvestris"
      ]);
    }
    catch ( \Exception $e ) { 
      $exception_caught = TRUE;
    }
    $this->assertTrue($exception_caught, "Did not catch exception for selecting a non-existing record.");

    // Now that we confirmed the new record doesn't exist, try to insert it
    $felis_organism_id = $plugin->getRecordPkey('Organism', 'organism', 1, ['genus' => "Felis"], ['species' => "silvestris"]);
    $this->assertIsNumeric($felis_organism_id, "Did not successfully insert a new organism (Felis silvestris) using getRecordPkey()");

    // Select it again to confirm that it now exists
    $select_felis_organism_id = $plugin->getRecordPkey('Organism', 'organism', 0, [
      'genus' => "Felis",
      'species' => "silvestris"
    ]);
    $this->assertEquals($felis_organism_id, $select_felis_organism_id, "Could not select a new record inserted using method getRecordPkey()");

  }
}
