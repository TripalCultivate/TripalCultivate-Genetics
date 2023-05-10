<?php

namespace Drupal\trpcultivate_genotypes\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines genotypes_loader annotation object.
 *
 * @Annotation
 */
class GenotypesLoader extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

  /**
   * The description of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

  /**
   * The file type of the input file.
   * 
   * @var string
   */
  public $input_file_type;

}
