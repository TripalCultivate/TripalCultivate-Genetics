<?php

namespace Drupal\trpcultivate_genotypes\GenotypesLoader;

use Drupal\Component\Plugin\PluginBase;
use Drupal\tripal\Services\TripalLogger;
use Drupal\tripal_chado\Database\ChadoConnection;
use \Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for genotypes_loader plugins.
 */
abstract class GenotypesLoaderPluginBase extends PluginBase implements GenotypesLoaderInterface, ContainerFactoryPluginInterface {

  /**
   * The chado.stock.organism_id of the samples in the samples file.
   * This must already exist.
   *
   * @var integer
   */
  protected $organism_id;

  /**
   * The chado.project.project_id that these genotypes are grouped under.
   * This must already exist.
   *
   * @var integer
   */
  protected $project_id;

  /**
   * The cvterm_id of the subtype of variant of the genotypes being inserted
   * For example, if the variant type is sequence_variant, the subtype can be one of SNP, MNP, indel, etc.
   * This must already exist.
   *
   * @var integer
   */
  protected $variant_subtype_id;

  /**
   * The cvterm_id of the subtype of marker of the genotypes being inserted
   * For example, if the marker type is genetic_marker, the subtype can be one of "Exome Capture", "GBS", "KASPar", etc.
   * This must already exist.
   *
   * @var integer
   */
  protected $marker_subtype_id;

  /**
   * The type of input file containing the genotypes
   * Currently, this must be one of: vcf, matrix, legacy
   *
   * @var string
   */
  protected $file_type;

  /**
   * The filepath of the input file containing the genotypes
   *
   * @var string
   */
  protected $input_file;

  /**
   * The filepath of the tab-delimited file specifying each sample name in the genotypes file
   *
   * @var string
   */
  protected $sample_file;

  /**
   * An array of the sample names within the genotypes input file, associated with their stock IDs
   * [sample_source_name] => [stock_id]
   *
   * @var array
   */
  protected $samples;

  /**
   * The logger for reporting progress, warnings and errors to admin.
   *
   * @var Drupal\tripal\Services\TripalLogger
   */
  protected $logger;

  /**
   * The database connection for querying Chado.
   *
   * @var Drupal\tripal_chado\Database\ChadoConnection
   */
  protected $connection;

  /**
   * The service for retreiving configuration values.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config_factory;

  /**
   * Implements ContainerFactoryPluginInterface->create().
   *
   * Since we have implemented the ContainerFactoryPluginInterface this static function
   * will be called behind the scenes when a Plugin Manager uses createInstance(). Specifically
   * this method is used to determine the parameters to pass to the contructor.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('tripal.logger'),
      $container->get('tripal_chado.database'),
      $container->get('config.factory')
    );
  }

  /**
   * Implements __contruct().
   *
   * Since we have implemented the ContainerFactoryPluginInterface, the constructor
   * will be passed additional parameters added by the create() function. This allows
   * our plugin to use dependency injection without our plugin manager service needing
   * to worry about it.
   *
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param Drupal\tripal\Services\TripalLogger $logger
   * @param Drupal\tripal_chado\Database\ChadoConnection $connection
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TripalLogger $logger, ChadoConnection $connection, ConfigFactoryInterface $config_factory) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->logger = $logger;
    $this->connection = $connection;
    $this->config_factory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /**
    * {@inheritdoc}
    */
  public function getRecordPkey(string $record_type, string $table, int $mode, array $select_values, array $insert_values = []) {

    // Check if the mode is one of the 3 options, throw an exception otherwise
    $valid_modes = [0, 1, 2];

    if (!in_array($mode, $valid_modes)) {
      throw new \Exception(
        t("The specified mode is not valid (mode=@mode)." , ['@mode'=>$mode])
      );
      return FALSE;
    }

    // Set some variables to abstract mode
    $select_only = 0;
    $insert_only = 1;
    $both = 2;

    // the name of the primary key.
    $pkey = $table . '_id';

    // First we select the record to see if it already exists.
    $query = $this->connection->select('1:' . $table, 't');
    $query->fields('t', [$pkey]);
    // Iterate through our select_values array
    foreach($select_values as $key => $value) {
      $query->condition('t.'.$key, $value, '=');
    }
    $record = $query->execute()->fetchAll();

    // If it exists and the mode is 1 (Insert Only), then throw an exception.
    if (sizeof($record) == 1) {
      if ($mode == $insert_only) {
        throw new \Exception(
          t("Record '@record_type' already exists but you chose to only insert (mode=@mode). Values: " .print_r($select_values, TRUE), ['@record_type'=>$record_type, '@mode'=>$mode])
        );
        return FALSE;
      }
      // Otherwise the mode allows select so return the value of the primary key.
      else {
        return $record[0]->{$pkey};
      }
    }

    // If more then one result is returned then this is NOT UNIQUE and we should report an
    // error to the user - not just run with the first one.
    elseif (sizeof($record) > 1) {
      throw new \Exception(
        t("Record '@record_type' is not unique (mode=@mode). Values: " .print_r($select_values, TRUE), ['@record_type'=>$record_type, '@mode'=>$mode])
      );
      return FALSE;
    }

    // If there is no pre-existing record but we've been given permission to create it,
    // then insert it
    elseif ($mode != $select_only) {

      // If we want to insert values, we can merge our values to have all the information we need
      $values = array_merge($select_values, $insert_values);

      // Insert all of our values
      $result = $this->connection->insert('1:' . $table)
        ->fields($values)
        ->execute();

      // If the primary key is available then the insert worked and we can return it.
      if ($result) {
        return $result;
      }
      else { // Otherwise, something went wrong so tell the user
        throw new \Exception(
          t("Tried to insert '@record_type' but the primary key is returned empty (mode=@mode). Values: " .print_r($select_values, TRUE), ['@record_type'=>$record_type, '@mode'=>$mode])
        );
        return FALSE;
      }
    }
    // If there is no pre-existing record and we are not allowed to create one,
    // then return an error.
    else {
      throw new \Exception(
        t("Record '@record_type' doesn't already exist but you chose to only select (mode=@mode). Values: " .print_r($select_values, TRUE), ['@record_type'=>$record_type, '@mode'=>$mode])
      );
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function processSamples() {

    // Grab config from our settings.yml
    $genetics_config = $this->config_factory->get('trpcultivate_genetics.settings');
    $genotypes_config = $this->config_factory->get('trpcultivate_genotypes.settings');
    $sample_file = $this->getSampleFilepath();
    // Open the sample mapping file
    $SAMPLES_FILE = fopen($sample_file, 'r');

    // Grab the header and count the number of columns
    $header = fgetcsv($SAMPLES_FILE, 0, "\t");
    $num_columns = count($header);
    if (!(($num_columns >= 5) && ($num_columns <= 7))) {
      throw new \Exception(
        t("Unexpected number of columns (%columns) in the samples file: %file", ['@file'=>$sample_file, '@columns'=>$num_columns])
      );
    }

    // Collect our default germplasm type and organism
    $default_germplasm_type_id = $genetics_config->get('terms.germplasm_type');
    $default_organism_id = $this->getOrganismID();

    // Iterate through each row to grab all of the samples
    while(!feof($SAMPLES_FILE)) {
      $current_line = fgetcsv($SAMPLES_FILE, 0, "\t");
      if (empty($current_line)) continue;

      // Column 1: DNA source (name should match sample in the genotype input file)
      $source_name = array_shift($current_line);
      // Column 2: Name of the sample assayed
      $sample_name = array_shift($current_line);
      // Column 3: Accession of the sample assayed
      $sample_accession = array_shift($current_line);
      // Column 4: Name of the germplasm
      $germplasm_name = array_shift($current_line);
      // Column 5: Accession of the germplasm
      $germplasm_accession = array_shift($current_line);
      // Column 6: User can optionally supply a stock_type for each germplasm if they are inserting
      if ($num_columns >= 6) {
        $germplasm_type = array_shift($current_line);
        // Break our germplasm type into its dbname and accession
        list($germplasm_type_dbname, $germplasm_type_accession) = explode(':', $germplasm_type);
        // Reminder: check that germplasm_type is in the correct format
        $query = $this->connection->select('1:cvterm', 'cvt')
          ->fields('cvt', ['cvterm_id']);
        // Joins cannot be chained
        $query->join('1:dbxref', 'dbx', 'dbx.dbxref_id = cvt.dbxref_id');
        $query->join('1:db', 'db', 'dbx.db_id = db.db_id');
        $query->condition('db.name', $germplasm_type_dbname)
          ->condition('dbx.accession', $germplasm_type_accession);
        $records = $query->execute()->fetchAll();
        // Check there is only 1 record, otherwise throw an exception
        $germplasm_type_id = $records[0]->cvterm_id;
      } else {
        // If not provided with a cvterm, grab the default
        $germplasm_type_id = $default_germplasm_type_id;
      }
      // Column 7: User can optionally supply an organism for each germplasm if
      // they are inserting germplasm into the database. We need to allow spaces
      // between the genus and species as well as infraspecific organisms, so we'll
      // use a method from Tripal's API to look it up in the database
      if ($num_columns == 7) {
        $organism_name = array_shift($current_line);
        // Grab the organism ID using the organism name and genus supplied in the samples file
        $organism_array = chado_get_organism_id_from_scientific_name($organism_name);
        //print_r($organism_array);
        if (!$organism_array) {
          throw new \Exception(
            t("ERROR: Could not find an organism \"@organism_name\" in the database.", ['@organism_name' => $organism_name])
          );
        }
        // We also want to check if we were given only one value back, as there is
        // potential to retrieve multiple IDs using that function
        if (is_array($organism_array) && (count($organism_array) > 1)) {
          throw new \Exception(
            t("ERROR: Retrieved more than one organism ID for \"@organism_name\" when only 1 was expected.", ['@organism_name' => $organism_name])
          );
        }
        $organism_id = $organism_array[0];
      } else {
        // If not provided with an organism, use the default
        $organism_id = $default_organism_id;
      }

      /** --------------------------
      *    LOOKUP/INSERT SAMPLES
      * ----------------------------
      * Samples in the samples file get checked for or inserted regardless if they
      * appear in the input file containing genotypic calls. This could be useful if
      * whoever is managing the database wants to use a single master file containing
      * all the samples in their database. It also means some samples may be inserted
      * but no additional data is inserted for those samples by this loader.
      */

      // ---------- STOCK ----------
      $samples_mode = $genotypes_config->get('modes.samples_mode');
      $sample_type_id = $genetics_config->get('terms.sample_type');
      $stock_id = $this->getRecordPkey('Sample', 'stock', $samples_mode, [
        'uniquename' => $sample_accession,
        'organism_id' => $organism_id,
        'type_id' => $sample_type_id
      ], [
        'name' => $sample_name
      ]);
      // Reminder: getRecordPkey() will throw an exception if !$stock_id

      // -------- GERMPLASM --------
      $germplasm_mode = $genotypes_config->get('modes.germplasm_mode');
      $germplasm_id = $this->getRecordPkey('Germplasm', 'stock', $germplasm_mode, [
        'uniquename' => $germplasm_accession,
        'organism_id' => $organism_id,
        'type_id' => $germplasm_type_id
      ], [
        'name' => $germplasm_name
      ]);
      // Reminder: getRecordPkey() will throw an exception if !$germplasm_id

      // ----- GERMPLASM TO SAMPLE LINK -----
      $sample_germplasm_relationship_type_id = $genetics_config->get('terms.sample_germplasm_relationship_type');
      $status = $this->getRecordPkey('Germplasm to Sample Link', 'stock_relationship', '2', [
        'subject_id' => $stock_id,
        'type_id' => $sample_germplasm_relationship_type_id,
        'object_id' => $germplasm_id,
      ]);
      // Reminder: getRecordPkey() will throw an exception if !$status

      // Save the sample source name (which will match the sample name given in the
      // genotypes input file) and its stock id in the samples array
      $samples[$source_name] = $stock_id;
    }

    // Return our samples array
    return $samples;
  }

  /****************************************************************************
   *  Setter functions
   ****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function setOrganismID( int $organism_id ) {

    // Do validation - throw exception if not valid
    // Query the provided organism ID
    $result = $this->getRecordPkey("Organism", "organism", 0, ['organism_id' => $organism_id]);

    // Ensure the organism ID exists
    if(!$result) {
      throw new \Exception(
        t("The organism must already exist but an organism_id of @organism was provided." , ['@organism'=>$organism_id])
      );
    }
    $this->organism_id = $organism_id;
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function setProjectID( int $project_id ) {

    // Do validation - throw exception if not valid
    // Query the provided project ID
    $result = $this->getRecordPkey("Project", "project", 0, ['project_id' => $project_id]);

    // Ensure the project ID exists
    if(!$result) {
      throw new \Exception(
        t("The project must already exist but a project_id of @project was provided." , ['@project'=>$project_id])
      );
    }
    $this->project_id = $project_id;
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariantSubTypeID( int $cvterm_id ) {

    // Do validation - throw exception if not valid
    // Query the provided cvterm ID
    $result = $this->getRecordPkey("Variant subtype cvterm", "cvterm", 0, ['cvterm_id' => $cvterm_id]);

    // Ensure the cvterm ID exists
    if(!$result) {
      throw new \Exception(
        t("The variant subtype must already exist but a cvterm_id of @cvterm was provided." , ['@cvterm'=>$cvterm_id])
      );
    }
    $this->variant_subtype_id = $cvterm_id;
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function setMarkerSubTypeID( int $cvterm_id ) {

    // Do validation - throw exception if not valid
    // Query the provided cvterm ID
    $result = $this->getRecordPkey("Marker subtype cvterm", "cvterm", 0, ['cvterm_id' => $cvterm_id]);

    // Ensure the cvterm ID exists
    if(!$result) {
      throw new \Exception(
        t("The marker subtype must already exist but a cvterm_id of @cvterm was provided." , ['@cvterm'=>$cvterm_id])
      );
    }
    $this->marker_subtype_id = $cvterm_id;
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function setInputFileType( string $file_type ) {

    // Do validation - throw exception if not valid
    // Check if the file type is set to one of vcf, matrix or legacy
    $valid_file_types = array(
      "vcf",
      "matrix",
      "legacy"
    );
    if(!in_array($file_type, $valid_file_types)) {
      throw new \Exception(
        t("The input file must be one of: vcf, matrix or legacy. A type of @type was provided." , ['@type'=>$file_type])
      );
    }

    $this->file_type = $file_type;
    return TRUE;
  }


  /**
   * {@inheritdoc}
   */
  public function setInputFilepath( string $input_file ) {

    // Do validation - throw exception if not valid
    // Check if file exists and is a file (NOT a directory)
    if(!(is_file($input_file) && file_exists($input_file))) {
      throw new \Exception(
        t("The input file must already exist but a filepath of @file was provided." , ['@file'=>$input_file])
      );
    }

    // Check that the file can be opened (eg. due to permissions or corruption)
    $result = is_readable($input_file);
    if (!$result) {
      throw new \Exception(
        t("The input file (@file) exists but is not readable. Check for permissions or if it is corrupt." , ['@file'=>$input_file])
      );
    }

    $this->input_file = $input_file;
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function setSampleFilepath( string $sample_file ) {

    // Do validation - throw exception if not valid
    // Check if file exists and is a file (NOT a directory)
    if(!(is_file($sample_file) && file_exists($sample_file))) {
      throw new \Exception(
        t("The samples file must already exist but a filepath of @file was provided." , ['@file'=>$sample_file])
      );
    }

    // Check that the file can be opened (eg. due to permissions or corruption)
    $result = is_readable($sample_file);
    if (!$result) {
      throw new \Exception(
        t("The samples file (@file) exists but is not readable. Check for permissions or if it is corrupt." , ['@file'=>$sample_file])
      );
    }
    $this->sample_file = $sample_file;
    return TRUE;
  }

  /****************************************************************************
   *  Getter functions
   ****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function getOrganismID() {
    return $this->organism_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getProjectID() {
    return $this->project_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getVariantSubTypeID() {
    return $this->variant_subtype_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getMarkerSubTypeID() {
    return $this->marker_subtype_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getInputFileType() {
    return $this->file_type;
  }

  /**
   * {@inheritdoc}
   */
  public function getInputFilepath() {
    return $this->input_file;
  }

  /**
   * {@inheritdoc}
   */
  public function getSampleFilepath() {
    return $this->sample_file;
  }
}
