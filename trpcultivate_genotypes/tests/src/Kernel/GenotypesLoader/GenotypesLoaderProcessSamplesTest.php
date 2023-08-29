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
   * Configuration for trpcultivate_genetics module
   *
   * @var config_entity
   */
  private $genetics_config;

	/**
   * Configuration for trpcultivate_genotypes module
   *
   * @var config_entity
   */
  private $genotypes_config;

	/**
	 * The Genotypes Loader plugin object
	 *
	 * @var GenotypesLoaderFakePlugin
	 */
	protected $plugin;

  /**
   * Tripal DBX Chado Connection object
   *
   * @var ChadoConnection
   */
  protected $connection;

	/**
   * {@inheritdoc}
   */
  protected function setUp(): void {
  	parent::setUp();

		// Ensure we see all logging in tests.
		\Drupal::state()->set('is_a_test_environment', TRUE);

		// Open connection to Chado
		$this->connection = $this->getTestSchema(ChadoTestKernelBase::PREPARE_TEST_CHADO);

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
		$this->genotypes_config = $genotypes_config;

		// Create the Genotypes Loader object
		// Configuration should be any key value pairs specific to Genotypes Loader plugin
		$configuration = [];
		$plugin_definition = [];
		$logger = \Drupal::service('tripal.logger');
		$this->plugin = new GenotypesLoaderFakePlugin($configuration,"fake_genotypes_loader",$plugin_definition,$logger,$this->connection,$config_factory);
		$this->assertIsObject($this->plugin, 'Unable to create a Plugin');
		$this->assertInstanceOf(GenotypesLoaderInterface::class, $this->plugin,"Returned object is not an instance of GenotypesLoaderInterface.");
	}

  /**
   * Test processing a samples file with 7 columns, to simulate a real-life example
   *
   * @group GenotypesLoader
   */
  public function testProcessSamplesSevenColumns(){

		// Assert the plugin was created
		$this->assertNotNull($this->plugin);

		// Sample Filepath
		$sample_file_path = __DIR__ . '/../../Fixtures/cats_samples.tsv';

		// Set sample filepath
		$success = $this->plugin->setSampleFilepath($sample_file_path);
		$this->assertTrue($success, "Unable to set sample filepath");

		// Get sample filepath
		$grabbed_sample_file_path = $this->plugin->getSampleFilepath();
		$this->assertEquals($sample_file_path, $grabbed_sample_file_path, "The sample filepath grabbed by the getter method does not match.");

		// Insert our 2 organisms that are in the file
		// Felis catus
		$catus_organism_id = $this->connection->insert('1:organism')
			->fields([
				'genus' => 'Felis',
				'species' => 'catus',
			])
			->execute();

		// Felis silvestris
		$silvestris_organism_id = $this->connection->insert('1:organism')
			->fields([
				'genus' => 'Felis',
				'species' => 'silvestris',
			])
			->execute();

		// Process our samples so that they all get inserted into the database
		$processed_samples = $this->plugin->processSamples();

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
		$Ross_query = $this->connection->select('1:stock','s')
    	->fields('s', ['organism_id'])
		  ->condition('stock_id', 1, '=');
		$Ross_record = $Ross_query->execute()->fetchAll();
		$this->assertEquals($catus_organism_id, $Ross_record[0]->organism_id, "One of the samples that was inserted (Ross) is of the wrong organism.");

		// Second, check Zapelli is a Felis silvestris
		$Zapelli_query = $this->connection->select('1:stock','s')
    	->fields('s', ['organism_id'])
		  ->condition('stock_id', 17, '=');
		$Zapelli_record = $Zapelli_query->execute()->fetchAll();
		$this->assertEquals($silvestris_organism_id, $Zapelli_record[0]->organism_id, "One of the samples that was inserted (Zapelli) is of the wrong organism.");

		// Check that our samples' germplasm are the correct germplasm type
		// Pull out the cvterm ID for CO_010:0000044
		$germplasm_type_query = $this->connection->select('1:cvterm', 'cvt')
      ->fields('cvt', ['cvterm_id']);
		$germplasm_type_query->join('1:dbxref', 'dbx', 'dbx.dbxref_id = cvt.dbxref_id');
		$germplasm_type_query->join('1:db', 'db', 'dbx.db_id = db.db_id');
		$germplasm_type_query->condition('db.name', 'CO_010')
			->condition('dbx.accession', '0000044');
		$germplasm_type_records = $germplasm_type_query->execute()->fetchAll();
		$germplasm_type_id = $germplasm_type_records[0]->cvterm_id;

		// Check the cvterm_id for the germplasm Ross
		$Ross_germ_query = $this->connection->select('1:stock','s')
    	->fields('s', ['type_id'])
			->condition('stock_id', 2, '=');
		$Ross_germ_record = $Ross_germ_query->execute()->fetchAll();
		$this->assertEquals($germplasm_type_id, $Ross_germ_record[0]->type_id, "The germplasm being inserted has an unexpected type_id.");
	}

	/**
   * Test processing a samples file with 5 columns, to simulate a real-life example
	 * Essentially, we are ensuring that the default organism and germplasm type are
	 * being set for each sample
   *
   * @group GenotypesLoader
   */
  public function testProcessSamplesFiveColumns(){

		// Test for 5 columns in our sample file, and ensure the default germplasm
		// type and organism are being assigned
		// Sample Filepath
		$five_col_file_path = __DIR__ . '/../../Fixtures/cats_samples_5_columns.tsv';

		// Set sample filepath
		$success = $this->plugin->setSampleFilepath($five_col_file_path);
		$this->assertTrue($success, "Unable to set sample filepath for test file with 5 columns");

		// Create our Felis catus organism
		$catus_organism_id = $this->connection->insert('1:organism')
			->fields([
				'genus' => 'Felis',
				'species' => 'catus',
			])
			->execute();

		// Set the default organism to Felis catus
		$success = $this->plugin->setOrganismID($catus_organism_id);
		$this->assertTrue($success, "Unable to set organism for test file with 5 columns");

		// Process our samples
		$processed_samples = $this->plugin->processSamples();

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

		// Pull out the organism for last sample (in the previous test, should have
		// been Felis Silvestris, now should be set as Felis catus)
		$Zapelli_query = $this->connection->select('1:stock','s')
    	->fields('s', ['organism_id'])
			->condition('stock_id', 17, '=');
		$Zapelli_record = $Zapelli_query->execute()->fetchAll();
		$this->assertEquals($catus_organism_id, $Zapelli_record[0]->organism_id, "One of the samples that was inserted (Zapelli) is of the wrong organism.");

		// Pull out the germplasm type for a sample (terms.germplasm_type = 10)
		$Prado_germ_query = $this->connection->select('1:stock','s')
    	->fields('s', ['type_id'])
			->condition('stock_id', 4, '=');
		$Prado_germ_record = $Prado_germ_query->execute()->fetchAll();
		$this->assertEquals($Prado_germ_record[0]->type_id, 10, "The germplasm being inserted has an unexpected type_id.");
	}

	/**
   * Test processing a samples file where exceptions are intentially being triggered
	 * by the formatting or content of the samples file
   *
   * @group GenotypesLoader
	 * @group ProcessSamplesExceptions
   */
  public function testProcessSamplesExceptions(){

		// Change the config mode from insert to select only for both samples and germplasm
		$this->genotypes_config->set('modes.samples_mode', 0);
		$this->genotypes_config->set('modes.germplasm_mode', 0);
		$this->genotypes_config->save();

		// Set sample filepath
		$sample_file_path = __DIR__ . '/../../Fixtures/cats_samples_5_columns.tsv';
		$success = $this->plugin->setSampleFilepath($sample_file_path);
		$this->assertTrue($success, "Unable to set sample filepath for test file with 5 columns");

		// Insert our felis catus organism
		$catus_organism_id = $this->connection->insert('1:organism')
		->fields([
			'genus' => 'Felis',
			'species' => 'catus',
		])
		->execute();

		// Now try to process samples when we can only select and not insert them
		$exception_caught = FALSE;
    try {
			$processed_samples = $this->plugin->processSamples();
    }
    catch ( \Exception $e ) {
     $exception_caught = TRUE;
    }
    $this->assertTrue($exception_caught, "Did not catch exception for trying to select samples that do not exist.");

		// Try a samples file with less than the minimum number of columns
		$too_few_col_file_path = __DIR__ . '/../../Fixtures/cats_samples_4_columns.tsv';
		$success = $this->plugin->setSampleFilepath($too_few_col_file_path);
		$this->assertTrue($success, "Unable to set sample filepath for test file with too few columns");

    $exception_caught = FALSE;
    try {
      $too_few_col_processed_samples = $this->plugin->processSamples();
    }
    catch ( \Exception $e ) {
     $exception_caught = TRUE;
    }
    $this->assertTrue($exception_caught, "Did not catch exception for too few columns in a samples file.");

		// Try a germplasm type with the wrong format
		$wrong_germ_type_format_file_path = __DIR__ . '/../../Fixtures/cats_samples_wrong_germ_type.tsv';
		$success = $this->plugin->setSampleFilepath($wrong_germ_type_format_file_path);
		$this->assertTrue($success, "Unable to set sample filepath for test file with a non-existant organism");

    $exception_caught = FALSE;
    try {
      $wrong_germ_type_format_processed_samples = $this->plugin->processSamples();
    }
    catch ( \Exception $e ) {
    $exception_caught = TRUE;
    }
    $this->assertTrue($exception_caught, "Did not catch exception for the wrong germplasm type in the samples file.");

		// Try germplasm with multiple copies of the cvterm accession in the database
		// First let's drop constraints on the cvterm table to allow us to insert a duplicate
		$this->connection->query('ALTER TABLE {1:cvterm} DROP CONSTRAINT cvterm_c2');
		// Create 3 records:
		// 1) In the dbxref table where db_id = 14 (local)
		$dbxref_id = $this->connection->insert('1:dbxref')
		->fields([
			'db_id' => '14',
			'accession' => '012345',
		])
		->execute();
		// 2) Create a record in the cvterm table with the same dbxref_id as 1)
		$cvterm_1 = $this->connection->insert('1:cvterm')
		->fields([
			'name' => 'test1',
			'cv_id' => '2',
			'dbxref_id' => $dbxref_id,
		])
		->execute();
		// 3) Insert a duplicate cvterm that has a different name but same cv_id and dbxref_id
		$cvterm2 = $this->connection->insert('1:cvterm')
		->fields([
			'name' => 'test2',
			'cv_id' => '2',
			'dbxref_id' => $dbxref_id,
		])
		->execute();

		$dup_germ_type_file_path = __DIR__ . '/../../Fixtures/cats_samples_dup_germ_type.tsv';
		$success = $this->plugin->setSampleFilepath($dup_germ_type_file_path);
		$this->assertTrue($success, "Unable to set sample filepath for test file with a duplicate germplasm type.");

		$exception_caught = FALSE;
    try {
			$dup_germ_type_processed_samples = $this->plugin->processSamples();
		}
    catch ( \Exception $e ) {
    $exception_caught = TRUE;
    }
    $this->assertTrue($exception_caught, "Did not catch exception for a duplicate germplasm type in the samples file.");

		// Try samples with an organism that doesn't exist in the database
		$nonexistant_org_file_path = __DIR__ . '/../../Fixtures/cats_samples_nonexistant_org.tsv';
		$success = $this->plugin->setSampleFilepath($nonexistant_org_file_path);
		$this->assertTrue($success, "Unable to set sample filepath for test file with a non-existant organism.");

		$exception_caught = FALSE;
    try {
      $nonexistant_org_processed_samples = $this->plugin->processSamples();
    }
    catch ( \Exception $e ) {
     $exception_caught = TRUE;
    }
    $this->assertTrue($exception_caught, "Did not catch exception for inserting a sample with nonexistant organism.");

		// Change the config to select and insert since the next test shouldn't throw
		// an exception but would complain once it tries to look for samples
		$this->genotypes_config->set('modes.samples_mode', 2);
		$this->genotypes_config->set('modes.germplasm_mode', 2);
		$this->genotypes_config->save();
		// Try a samples file with more than the expected number of columns
		$eight_col_sample_file_path = __DIR__ . '/../../Fixtures/cats_samples_8_columns.tsv';
		$success = $this->plugin->setSampleFilepath($eight_col_sample_file_path);
		$this->assertTrue($success, "Unable to set sample filepath for test file with 8 columns");

		// More than 7 columns does not trigger an exception, but should give a warning
		// It's proving difficult to check for the warning, but I'm leaving the function
		// call to prove that it does allow for more than 7 columns :)
		//ob_start();
		$eight_col_processed_samples = $this->plugin->processSamples();
		//$warning_message = ob_get_contents();
		//$warning_caught = preg_match("/WARNING/", $warning_message);
		//$this->assertTrue($warning_caught, "Did not catch warning for more than 7 columns in a samples file.");
		//ob_clean();

		// Check that our sample Ross was inserted
		$Ross_sample_name = 'Ross_110201';
		$Ross_query = $this->connection->select('1:stock','s')
    	->fields('s', ['name'])
		  ->condition('stock_id', 1, '=');
		$Ross_record = $Ross_query->execute()->fetchAll();
		$this->assertEquals($Ross_sample_name, $Ross_record[0]->name, "The sample inserted by the 8 column sample file failed to insert Ross_110201.");

		// Try a samples file where some of the optional columns (6;germplasm type & 7; organism) are left blank
		// but some are filled in
		$optional_blanks_sample_file_path = __DIR__ . '/../../Fixtures/cats_samples_optional_blanks.tsv';
		$success = $this->plugin->setSampleFilepath($optional_blanks_sample_file_path);
		$this->assertTrue($success, "Unable to set sample filepath for test file with some optional blank columns");

		// Set the default organism to Felis catus
		$success = $this->plugin->setOrganismID($catus_organism_id);
		$this->assertTrue($success, "Unable to set default organism just before test with optional blank columns");

		// Insert Felis Silvestris since we need it to ensure that stock Zapelli has this as its organism
		$silvestris_organism_id = $this->connection->insert('1:organism')
		->fields([
			'genus' => 'Felis',
			'species' => 'silvestris',
		])
		->execute();

		// We don't expect an exception to be thrown, but need to ensure the right values are inserted
		$optional_blanks_processed_samples = $this->plugin->processSamples();

		// Check if Ash (stock ID = 5) was correctly assigned Felis Silvestris as an organism
		$Ash_query = $this->connection->select('1:stock','s')
    	->fields('s', ['organism_id'])
			->condition('stock_id', 5, '=');
		$Ash_record = $Ash_query->execute()->fetchAll();
		$this->assertEquals($silvestris_organism_id, $Ash_record[0]->organism_id, "One of the samples that was inserted (Ash) is of the wrong organism.");

		// Check that Piero (stock ID = 7, directly after Ash) is assigned the default organism (Felis catus)
		$Piero_query = $this->connection->select('1:stock','s')
			->fields('s', ['organism_id'])
			->condition('stock_id', 7, '=');
		$Piero_record = $Piero_query->execute()->fetchAll();
		$this->assertEquals($catus_organism_id, $Piero_record[0]->organism_id, "One of the samples that was inserted (Piero) is of the wrong organism.");

		// Pull out the germplasm type for a sample with the default (terms.germplasm_type = 10)
		$Prado_germ_query = $this->connection->select('1:stock','s')
    	->fields('s', ['type_id'])
			->condition('stock_id', 4, '=');
		$Prado_germ_record = $Prado_germ_query->execute()->fetchAll();
		$this->assertEquals($Prado_germ_record[0]->type_id, 10, "The germplasm being inserted has an unexpected type_id.");
		
		// Pull out germplasm type for a sample where it was specified in the samples file
		$germplasm_type_id = $this->getCVtermID('CO_010','0000044');
		$Ash_germ_query = $this->connection->select('1:stock','s')
    	->fields('s', ['type_id'])
			->condition('stock_id', 6, '=');
		$Ash_germ_record = $Ash_germ_query->execute()->fetchAll();
		$this->assertEquals($Ash_germ_record[0]->type_id, $germplasm_type_id, "The germplasm being inserted has an unexpected type_id.");
	}
}
