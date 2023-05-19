<?php

namespace Drupal\trpcultivate_genotypes\GenotypesLoader;

/**
 * Interface for genotypes_loader plugins.
 */
interface GenotypesLoaderInterface {

  /**
   * Returns the translated plugin label.
   *
   * @return string
   *   The translated title.
   */
  public function label();

  /****************************************************************************
   *  Setter functions
   ****************************************************************************/

  /**
   * Sets the organism ID of the genome assembly variants were called on.
   * This organism ID will also be used for variants and markers created by this loader.
   *
   * @return bool
   *   Returns true if set, otherwise throws an exception
   */
  public function setOrganismID( int $organism_id );

  /**
   * Sets the project ID of the project under which the genotype calls were generated.
   *
   * @return bool
   *   Returns true if set, otherwise throws an exception
   */
  public function setProjectID( int $project_id );

  /**
   * Sets the cvterm ID of the subtype of variant being loaded (eg. SNP, MNP, indel).
   *
   * @return bool
   *   Returns true if set, otherwise throws an exception
   */
  public function setVariantSubTypeID( int $cvterm_id );

  /**
   * Sets the cvterm ID of the subtype of marker technology used to generate the genotypes being loaded (eg. "Exome Capture", "GBS", "KASPar", etc.).
   *
   * @return bool
   *   Returns true if set, otherwise throws an exception
   */
  public function setMarkerSubTypeID( int $cvterm_id );

  /**
   * Sets the file format type of the genotypes file being supplied to the loader (eg. vcf, matrix, legacy).
   *
   * @return bool
   *   Returns true if set, otherwise throws an exception
   */
  public function setInputFileType( string $file_type );

  /**
   * Sets the file path of the genotypes file being supplied to the loader.
   *
   * @return bool
   *   Returns true if set, otherwise throws an exception
   */
  public function setInputFilepath( string $input_file );

  /**
   * Sets the file path of the samples file being supplied to the loader.
   *
   * @return bool
   *   Returns true if set, otherwise throws an exception
   */
  public function setSampleFilepath( string $sample_file );

  /****************************************************************************
   *  Getter functions
   ****************************************************************************/
  
  /**
   * Gets the organism ID of the genome assembly variants were called on.
   * 
   * @return int
   *   The ID of the organism
   */ 
  public function getOrganismID();

  /**
   * Gets the project ID of the project under which the genotype calls were generated.
   * 
   * @return int
   *   The ID of the project
   */ 
  public function getProjectID();

  /**
   * Gets the cvterm ID of the subtype of variant being loaded (eg. SNP, MNP, indel).
   * 
   * @return int
   *   The ID of the cvterm of the variant subtype
   */ 
  public function getVariantSubTypeID();

  /**
   * Gets the cvterm ID of the subtype of marker technology used to generate the genotypes being loaded (eg. "Exome Capture", "GBS", "KASPar", etc.).
   * 
   * @return int
   *   The ID of the cvterm of the marker subtype
   */ 
  public function getMarkerSubTypeID();

  /**
   * Gets the file format type of the genotypes file being supplied to the loader (eg. vcf, matrix, legacy).
   * 
   * @return string
   *   The file format
   */ 
  public function getInputFileType();

  /**
   * Gets the file path of the genotypes file being supplied to the loader.
   * 
   * @return string
   *   The filepath
   */ 
  public function getInputFilepath();

  /**
   * Gets the file path of the samples file being supplied to the loader.
   * 
   * @return string
   *   The filepath
   */ 
  public function getSampleFilepath();

}
