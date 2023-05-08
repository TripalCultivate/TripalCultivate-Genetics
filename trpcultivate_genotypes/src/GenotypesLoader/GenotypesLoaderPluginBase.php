<?php

namespace Drupal\trpcultivate_genotypes\GenotypesLoader;

use Drupal\Component\Plugin\PluginBase;
use Drupal\tripal\Services\TripalLogger;
use Drupal\tripal_chado\Database\ChadoConnection;
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
      $container->get('tripal_chado.database')
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
  public function __construct(array $configuration, $plugin_id, $plugin_definition, TripalLogger $logger, ChadoConnection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->logger = $logger;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

  /****************************************************************************
   *  Setter functions
   ****************************************************************************/

  /**
   * {@inheritdoc}
   */
  public function setOrganismID( int $organism_id ) {
    
    // Do validation - throw exception if not valid
    // Open a db connection and query the provided organism ID
    $connection = \Drupal::service('tripal_chado.database');
    $query = $connection->select('1:organism', 'o');
    $query->fields('o', ['organism_id']);
    $query->condition('o.organism_id', $organism_id, '=');
    $result = $query->execute();

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
    // Open a db connection and query the provided project ID
    $connection = \Drupal::service('tripal_chado.database');
    $query = $connection->select('1:project', 'p');
    $query->fields('p', ['project_id']);
    $query->condition('p.project_id', $project_id, '=');
    $result = $query->execute();

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
    // Open a db connection and query the provided cvterm ID
    $connection = \Drupal::service('tripal_chado.database');
    $query = $connection->select('1:cvterm', 'cvt');
    $query->fields('cvt', ['cvterm_id']);
    $query->condition('cvt.cvterm_id', $cvterm_id, '=');
    $result = $query->execute();

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
    // Open a db connection and query the provided cvterm ID
    $connection = \Drupal::service('tripal_chado.database');
    $query = $connection->select('1:cvterm', 'cvt');
    $query->fields('cvt', ['cvterm_id']);
    $query->condition('cvt.cvterm_id', $cvterm_id, '=');
    $result = $query->execute();

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
  public function setInputFilepath( string $input_file ) {
    
    // Do validation - throw exception if not valid
    // Check if file exists
    if(!file_exists($input_file)) {
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
    // Check if file exists
    if(!file_exists($sample_file)) {
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