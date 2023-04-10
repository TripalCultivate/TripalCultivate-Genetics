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
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

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

}
