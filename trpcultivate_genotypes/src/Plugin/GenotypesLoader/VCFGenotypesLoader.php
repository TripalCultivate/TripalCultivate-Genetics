<?php

namespace Drupal\trpcultivate_genotypes\Plugin\GenotypesLoader;

use Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderPluginBase;
use Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderInterface;

/**
 * Provides a plugin for loading genotypes from a VCF file.
 *
 *  @GenotypesLoader(
 *    id = "vcf_genotypes_loader",
 *    label = @Translation("VCF Genotypes Loader"),
 *    description = @Translation("Handles the loading of genotypes from a VCF file."),
 *    input_file_type = "vcf"
 *  )
 */
class VCFGenotypesLoader extends GenotypesLoaderPluginBase implements GenotypesLoaderInterface {
  
  /**
   * 
   */

}