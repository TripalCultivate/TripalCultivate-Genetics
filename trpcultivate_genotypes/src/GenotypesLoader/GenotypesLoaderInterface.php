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

}
