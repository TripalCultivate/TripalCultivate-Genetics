<?php

namespace Drupal\trpcultivate_genotypes\GenotypesLoader;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for genotypes_loader plugins.
 */
abstract class GenotypesLoaderPluginBase extends PluginBase implements GenotypesLoaderInterface {

  /**
   * {@inheritdoc}
   */
  public function label() {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
