<?php

namespace Drupal\trpcultivate_genotypes\GenotypesLoader;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for genotypes_loader plugins.
 */
abstract class GenotypesLoaderPluginBase extends PluginBase implements GenotypesLoaderInterface {

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
  public function setOrganismID( integer $organism_id ) {
    
    // Do validation - throw exception if not valid
    if(false) {
      throw new \Exception(
        t("The organism must already exist but an organism_id of @organism was provided." , ['@organism'=>$organism_id])
      );
    }

    $this->organism_id = $organism_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setProjectID( integer $project_id ) {
    
    // Do validation - throw exception if not valid
    if(false) {
      throw new \Exception(
        t("The project must already exist but a project_id of @project was provided." , ['@project'=>$project_id])
      );
    }

    $this->project_id = $project_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setVariantSubTypeID( integer $cvterm_id ) {
    
    // Do validation - throw exception if not valid
    if(false) {
      throw new \Exception(
        t("The variant subtype must already exist but a cvterm_id of @cvterm was provided." , ['@cvterm'=>$cvterm_id])
      );
    }

    $this->variant_subtype_id = $cvterm_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setMarkerSubTypeID( integer $cvterm_id ) {
    
    // Do validation - throw exception if not valid
    if(false) {
      throw new \Exception(
        t("The marker subtype must already exist but a cvterm_id of @cvterm was provided." , ['@cvterm'=>$cvterm_id])
      );
    }

    $this->marker_subtype_id = $cvterm_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setInputFilepath( string $input_file ) {
    
    // Do validation - throw exception if not valid
    if(false) {
      throw new \Exception(
        t("The input file must already exist but a filepath of @file was provided." , ['@file'=>$input_file])
      );
    }

    $this->input_file = $input_file;
  }

  /**
   * {@inheritdoc}
   */
  public function setSampleFilepath( string $sample_file ) {
    
    // Do validation - throw exception if not valid
    if(false) {
      throw new \Exception(
        t("The samples file must already exist but a filepath of @file was provided." , ['@file'=>$sample_file])
      );
    }

    $this->sample_file = $sample_file;
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
