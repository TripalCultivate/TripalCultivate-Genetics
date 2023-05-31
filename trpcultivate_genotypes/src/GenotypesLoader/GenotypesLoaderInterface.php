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

  /**
   * Given a record, selects it from the chado database (or inserts if not found
   * and the mode allows) and returns the primary key.
   * 
   * @param string $record_type
   *   A human-readable term for your record, used for error messages.
   *   Eg. "Genotype", "Marker", "Genotype Marker Link"
   * @param string $table
   *   The name of the chado table to select from and/or insert into.
   * @param int $mode
   *   The user-specified means in which we are allowed to get the record's 
   *   primary key. 
   *   For example, the user may prefer to add stocks to the database
   *   manually or expects all of them to exist, so chooses a mode of 0 (select 
   *   only) so they will be informed if a germplasm does not already exist by
   *   an error.
   *   This must be one of: 0 (Select Only), 1 (Insert Only), 2 (Insert & Select)
   * @param array $select_values
   *   An array of [table column] => [value] mappings. 
   *   These values will be used for both the select and the insert, but are 
   *   specific to selecting the right record and thus are used in the 
   *   conditions (i.e. WHERE clause) of the query.
   * @param array $insert_values
   *   An array of [table column] => [value] mappings that is specific to the 
   *   insert. This is not required if mode is select only, and will be combined 
   *   with select values for an insert. More specifically, the values for all 
   *   columns set in the new record are defined in the combined select + insert
   *   values arrays.
   * @return int|false
   *   The value of the primary key for the inserted/selected record.
   *   If no primary key can be retrieved, then FALSE will be returned.
   */
  public function getRecordPkey(string $record_type, string $table, int $mode, array $select_values, array $insert_values = []);

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
