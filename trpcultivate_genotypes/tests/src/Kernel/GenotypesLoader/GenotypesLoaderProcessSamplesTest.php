<?php

namespace Drupal\Tests\trpcultivate_genotypes\Kernel\GenotypesLoader;

use Drupal\Core\Url;
use Drupal\Tests\tripal_chado\Kernel\ChadoTestKernelBase;
use Drupal\Tests\trpcultivate_genotypes\Functional\GenotypesLoader\Subclass\GenotypesLoaderFakePlugin;
use Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderPluginBase;
use Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderInterface;

/**
 * A test to call the the processSamples() method in the plugin base for the genotypes loader.
 *
 * @group TripGeno Genetics
 * @group Genotypes Loader
 */
class GenotypesLoaderProcessSamplesTest extends ChadoTestKernelBase {

  /**
   * Dependent modules to enable.
	 * NOTE: No install code is run for these modules unless specified in setup
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
		$connection = $this->getTestSchema(ChadoTestKernelBase::PREPARE_TEST_CHADO);

		$configs = [
			'trpcultivate_genetics.settings' => [
				'terms.sample_type' => 9,
				'terms.germplasm_type' => 10,
				'terms.sample_germplasm_relationship_type' => 11,
			],
			'trpcultivate_genotypes.settings' => [
				'modes.samples_mode' => 1,
				'modes.germplasm_mode' => 1,
				'modes.variants_mode' => 0,
				'modes.markers_mode' => 0,
			],
		];
		$config_factory = $this->getConfigFactoryStub($configs);

		// Create the Genotypes Loader object
		// Configuration should be any key value pairs specific to Genotypes Loader plugin
		$configuration = [];
		$plugin_definition = [];
		$logger = \Drupal::service('tripal.logger');
		$plugin = new GenotypesLoaderFakePlugin($configuration,"fake_genotypes_loader",$plugin_definition,$logger,$connection,$config_factory);
		$this->assertIsObject($plugin, 'Unable to create a Plugin');
		$this->assertInstanceOf(GenotypesLoaderInterface::class, $plugin,"Returned object is not an instance of GenotypesLoaderInterface.");

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

		// Process our samples so that they all get inserted into the database
		$processed_samples = $plugin->processSamples();

		// Setup our array with our samples and compare it to the output from our method
		$samples_array = [
			'Ross' => 1,
			'Prado' => 3,
			'Ash' => 5,
			'Piero' => 7,
			'Tai' => 9,
			'Beverly' => 11,
			'Argent' => 13,
			'Trenus' => 15,
			'Zapelli' => 17
		];

		// Check that the number of stocks match what we expect
    $this->assertEquals(count($samples_array), count($processed_samples), "The number of samples that were processed is incorrect.");

		// Compare our returned samples array with what we expect to get
		$this->assertEquals($samples_array, $processed_samples, "The returned samples array is not what was expected.");

		// Check that our samples are the correct organisms

		// Check that our samples' germplasm are the correct germplasm type

		// Test for 5 columns in our sample file, and ensure the default germplasm
		// type and organism are being assigned
		// Sample Filepath
		$five_columns_file_path = __DIR__ . '/../../Fixtures/cats_samples_5_columns.tsv';

		// Set sample filepath
		$success = $plugin->setSampleFilepath($five_columns_file_path);
		$this->assertTrue($success, "Unable to set sample filepath for test file with 5 columns");

		$processed_samples = $plugin->processSamples();

		// Pull out the germplasm type for a sample (Expect: Individual)

		// Pull out the organism for the first sample (Expect: Felis catus)

		/****************************************************************************
     *  TESTS for Exceptions
     ****************************************************************************/
		// Try a samples file with an incorrect number of columns
		// Sample Filepath
		$too_few_columns_file_path = __DIR__ . '/../../Fixtures/cats_samples_4_columns.tsv';

		// Set sample filepath
		$success = $plugin->setSampleFilepath($too_few_columns_file_path);
		$this->assertTrue($success, "Unable to set sample filepath for test file with too few columns");

    $exception_caught = FALSE;
    try {
      $processed_samples = $plugin->processSamples();
    }
    catch ( \Exception $e ) {
      $exception_caught = TRUE;
    }
    $this->assertTrue($exception_caught, "Did not catch exception for detecting a samples files with the wrong number of columns.");

		// Try samples with an organism that doesn't exist in the database

		// Try samples with more than one organism entry in the database

	}

	/**
	 * Our version of UnitTestCase::getConfigFactoryStub()
	 * It is exactly the same at this point but was not available in kernel tests.
	 * @see https://api.drupal.org/api/drupal/core%21tests%21Drupal%21Tests%21UnitTestCase.php/function/UnitTestCase%3A%3AgetConfigFactoryStub/9
	 *
	 * @param array $configs
	 * 	An associative array of configuration settings whose keys are
	 * 	configuration object names and whose values are key => value arrays for the
	 * 	configuration object in question. Defaults to an empty array.
	 *
	 * @return \PHPUnit\Framework\MockObject\MockBuilder
	 * 	A MockBuilder object for the ConfigFactory with the desired return values.
	 */
	public function getConfigFactoryStub(array $configs = []) {
		$config_get_map = [];
		$config_editable_map = [];

		// Construct the desired configuration object stubs, each with its own
		// desired return map.
		foreach ($configs as $config_name => $config_values) {

			// Define a closure over the $config_values, which will be used as a
			// returnCallback below. This function will mimic
			// \Drupal\Core\Config\Config::get and allow using dotted keys.
			$config_get = function ($key = '') use ($config_values) {

				// Allow to pass in no argument.
				if (empty($key)) {
					return $config_values;
				}

				// See if we have the key as is.
				if (isset($config_values[$key])) {
					return $config_values[$key];
				}
				$parts = explode('.', $key);
				$value = NestedArray::getValue($config_values, $parts, $key_exists);
				return $key_exists ? $value : NULL;
			};
			$immutable_config_object = $this
				->getMockBuilder('Drupal\\Core\\Config\\ImmutableConfig')
				->disableOriginalConstructor()
				->getMock();
			$immutable_config_object
				->expects($this
				->any())
				->method('get')
				->willReturnCallback($config_get);
			$immutable_config_object
				->expects($this->any())
				->method('__sleep')
				->willReturn([]);
			$config_get_map[] = [
				$config_name,
				$immutable_config_object,
			];
			$mutable_config_object = $this
				->getMockBuilder('Drupal\\Core\\Config\\Config')
				->disableOriginalConstructor()
				->getMock();
			$mutable_config_object
				->expects($this
				->any())
				->method('get')
				->willReturnCallback($config_get);
			$config_editable_map[] = [
				$config_name,
				$mutable_config_object,
			];
		}

		// Construct a config factory with the array of configuration object stubs
		// as its return map.
		$config_factory = $this
			->createMock('Drupal\\Core\\Config\\ConfigFactoryInterface');
		$config_factory
			->expects($this
			->any())
			->method('get')
			->willReturnMap($config_get_map);
		$config_factory
			->expects($this
			->any())
			->method('getEditable')
			->willReturnMap($config_editable_map);

		return $config_factory;
	}
}
