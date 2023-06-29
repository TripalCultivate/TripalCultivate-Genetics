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
class GenotypesLoaderProcessSamplesTest extends ChadoTestBrowserBase {

  protected $defaultTheme = 'stark';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['trpcultivate_genotypes'];

  /**
   * Test a fake instance of Genotypes Loader Plugin in terms of processing a samples file.
   *
   * @group GenotypesLoader
   */
  public function testGenotypesLoaderProcessSamples(){
        
    // Ensure we see all logging in tests.
    \Drupal::state()->set('is_a_test_environment', TRUE);

    // Open connection to Chado
    $connection = $this->createTestSchema(ChadoTestBrowserBase::PREPARE_TEST_CHADO);

    // Mock genetics config
    $config = $this->createMock('\Drupal\Core\Config\ImmutableConfig');
    // Mock getting the sample type ID
    $config->expects($this->any())
      ->method('get')
      ->with('terms.sample_type')
      ->willReturn(9);
    // Mock getting the germplasm type ID
    $config->expects($this->any())
      ->method('get')
      ->with('terms.germplasm_type')
      ->willReturn(10);
    // Mock getting the sample germplasm relationship type ID
    $config->expects($this->any())
      ->method('get')
      ->with('terms.sample_germplasm_relationship_type')
      ->willReturn(11);

    // Config factory mock.
    $config_factory = $this->createMock('Drupal\Core\Config\ConfigFactoryInterface');
    // Mocking get method.
    $config_factory->expects($this->any())
      ->method('get')
      ->with('trpcultivate_genetics.settings')
      ->willReturn($config);

    // Create the Genotypes Loader object
    // Configuration should be any key value pairs specific to Genotypes Loader plugin
    $configuration = [];
    $plugin_definition = [];
    $logger = \Drupal::service('tripal.logger');
    $plugin = new GenotypesLoaderFakePlugin($configuration,"fake_genotypes_loader",$plugin_definition,$logger,$connection,$config_factory);
    $this->assertIsObject($plugin, 'Unable to create a Plugin');
    $this->assertInstanceOf(GenotypesLoaderInterface::class, $plugin,"Returned object is not an instance of GenotypesLoaderInterface.");

    // Setup our array with our samples and compare it to the output from our method
    $samples_array = [
      'Ross' => 'Catsam1',
      'Prado' => 'Catsam2',
      'Ash' => 'Catsam3',
      'Piero' => 'Catsam4',
      'Tai' => 'Catsam5',
      'Beverly' => 'Catsam6',
      'Argent' => 'Catsam7',
      'Trenus' => 'Catsam8',
      'Zapelli' => 'Catsam9'
    ];

    // Sample Filepath
    $sample_file_path = __DIR__ . '/../../Fixtures/cats_samples.tsv';

    // Set sample filepath
    $success = $plugin->setSampleFilepath($sample_file_path);
    $this->assertTrue($success, "Unable to set sample filepath");

    // Get sample filepath
    $grabbed_sample_file_path = $plugin->getSampleFilepath();
    $this->assertEquals($sample_file_path, $grabbed_sample_file_path, "The sample filepath grabbed by the getter method does not match.");

    // Insert our 2 organisms that are in the file
    // Felis catus
    $organism_id = $connection->insert('1:organism')
      ->fields([
        'genus' => 'Felis',
        'species' => 'catus',
      ])
      ->execute();

    // Felis silvestris
    $organism_id = $connection->insert('1:organism')
      ->fields([
        'genus' => 'Felis',
        'species' => 'silvestris',
      ])
      ->execute();

    // // Set our samples mode and germplasm mode to be select only.
    // $samples_mode 
    // // We'd expect an exception from getRecordPkey
    // $exception_caught = FALSE;
    // try {
    //   $plugin->processSamples();
    // } 
    // catch ( \Exception $e ) {
    //   $exception_caught = TRUE;
    // }
    // $this->assertTrue($exception_caught, "Did not catch exception for attempting to select a non-existing sample.");

    // Test that our samples all get inserted into the database
    $processed_samples = $plugin->processSamples();

    //print_r($processed_samples);

    

    // Check that the number of stocks match what we expect
    $this->assertEquals(count($samples_array), count($processed_samples), "The number of samples that were processed is incorrect.");

    // Iterate through the process samples array and check that each one exists in the database
    

  }
}