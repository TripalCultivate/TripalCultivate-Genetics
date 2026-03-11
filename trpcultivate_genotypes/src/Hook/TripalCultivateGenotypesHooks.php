<?php

namespace Drupal\trpcultivate_genotypes\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Implements hooks for tripalcultivate genotypes module.
 */
class TripalCultivateGenotypesHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    switch ($route_name) {
      // Provides the module overview in the help tab.
      case 'help.page.trpcultivate_genotypes':
        $output = '';
        $output .= '<h3>' . $this->t('About') . '</h3>';

        $output .= '<p>' . $this->t('This module expands Chado to support large-scale
        genotypic data.') . '</p>';

        return $output;

      default:
    }
  }

}
