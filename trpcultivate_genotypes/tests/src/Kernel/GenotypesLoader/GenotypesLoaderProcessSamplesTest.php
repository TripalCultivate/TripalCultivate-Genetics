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
  protected static $modules = ['trpcultivate_genetics','trpcultivate_genotypes'];

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

		// Install module configuration and set values
    $this->installConfig(['trpcultivate_genotypes','trpcultivate_genetics']);
    $config_factory = \Drupal::configFactory();
		$genetics_config = $config_factory->getEditable('trpcultivate_genetics.settings');
		$genetics_config->set('terms.sample_type', 9);
		$genetics_config->set('terms.germplasm_type', 10);
		$genetics_config->set('terms.sample_germplasm_relationship_type', 11);
		$genetics_config->save();
    $genotypes_config = $config_factory->getEditable('trpcultivate_genotypes.settings');
		$genotypes_config->set('modes.samples_mode', 1);
		$genotypes_config->set('modes.germplasm_mode', 1);
		$genotypes_config->save();

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
		$catus_organism_id = $connection->insert('1:organism')
			->fields([
				'genus' => 'Felis',
				'species' => 'catus',
			])
			->execute();

		// Felis silvestris
		$silvestris_organism_id = $connection->insert('1:organism')
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
		// First check Ross is a Felis catus
		$Ross_query = $connection->select('1:stock','s')
    	->fields('s', ['organism_id'])
			->condition('stock_id', 1, '=');
		$Ross_record = $Ross_query->execute()->fetchAll();
		$this->assertEquals($catus_organism_id, $Ross_record[0]->organism_id, "One of the samples that was inserted (Ross) is of the wrong organism.");

		// Second, check Zapelli is a Felis silvestris
		$Zapelli_query = $connection->select('1:stock','s')
    	->fields('s', ['organism_id'])
			->condition('stock_id', 17, '=');
		$Zapelli_record = $Zapelli_query->execute()->fetchAll();
		$this->assertEquals($silvestris_organism_id, $Zapelli_record[0]->organism_id, "One of the samples that was inserted (Zapelli) is of the wrong organism.");

		// Check that our samples' germplasm are the correct germplasm type
		// Pull out the cvterm ID for CO_010:0000044
		$germplasm_type_query = $connection->select('1:cvterm', 'cvt')
      ->fields('cvt', ['cvterm_id']);
		$germplasm_type_query->join('1:dbxref', 'dbx', 'dbx.dbxref_id = cvt.dbxref_id');
		$germplasm_type_query->join('1:db', 'db', 'dbx.db_id = db.db_id');
		$germplasm_type_query->condition('db.name', 'CO_010')
			->condition('dbx.accession', '0000044');
		$germplasm_type_records = $germplasm_type_query->execute()->fetchAll();
		$germplasm_type_id = $germplasm_type_records[0]->cvterm_id;
		// Check the cvterm_id for the germplasm Ross
		$Ross_germ_query = $connection->select('1:stock','s')
    	->fields('s', ['type_id'])
			->condition('stock_id', 2, '=');
		$Ross_germ_record = $Ross_germ_query->execute()->fetchAll();
		$this->assertEquals($germplasm_type_id, $Ross_germ_record[0]->type_id, "The germplasm being inserted has an unexpected type_id.");

		// Test for 5 columns in our sample file, and ensure the default germplasm
		// type and organism are being assigned
		// Sample Filepath
		$five_col_file_path = __DIR__ . '/../../Fixtures/cats_samples_5_columns.tsv';

		// Set sample filepath
		$success = $plugin->setSampleFilepath($five_col_file_path);
		$this->assertTrue($success, "Unable to set sample filepath for test file with 5 columns");

		$five_col_processed_samples = $plugin->processSamples();

		// Pull out the germplasm type for a sample (Expect: Individual)

		// Pull out the organism for the first sample (Expect: Felis catus)

		/****************************************************************************
     *  TESTS for Exceptions
     ****************************************************************************/
		// Try a samples file with an incorrect number of columns
		// Sample Filepath
		$too_few_col_file_path = __DIR__ . '/../../Fixtures/cats_samples_4_columns.tsv';

		// Set sample filepath
		$success = $plugin->setSampleFilepath($too_few_col_file_path);
		$this->assertTrue($success, "Unable to set sample filepath for test file with too few columns");

    $exception_caught = FALSE;
    try {
      $too_few_col_processed_samples = $plugin->processSamples();
    }
    catch ( \Exception $e ) {
      $exception_caught = TRUE;
    }
    $this->assertTrue($exception_caught, "Did not catch exception for detecting a samples files with the wrong number of columns.");

		// Try samples with an organism that doesn't exist in the database

		// Try samples with more than one organism entry in the database

	}
}
