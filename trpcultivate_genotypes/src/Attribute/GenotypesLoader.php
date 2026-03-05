<?php

namespace Drupal\trpcultivate_genotypes\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a genotypes loader attribute object.
 *
 * Plugin Namespace: Drupal\trpcultivate_genotypes\GenotypesLoader.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class GenotypesLoader extends Plugin {

  /**
   * Constructs a GenotypesLoader attribute.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $title
   *   The human-readable name of the plugin.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup|null $description
   *   The description of the plugin.
   * @param string $input_file_type
   *   The file type of the input file.
   */
  public function __construct(
    public readonly string $id,
    public readonly ?TranslatableMarkup $title = NULL,
    public readonly ?TranslatableMarkup $description = NULL,
    public readonly string $input_file_type = '',
  ) {}

}
