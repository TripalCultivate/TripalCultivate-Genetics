<?php

namespace Drupal\trpcultivate_qtl\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Implemets hooks for tripalcultivate qtl module.
 */
class TripalCultivateQTLHooks {
  use StringTranslationTrait;

  /**
   * Implemets hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    switch ($route_name) {
      // Provides the module overview in the help tab.
      case 'help.page.trpcultivate_qtl':
        $output = '';
        $output .= '<h3>' . $this->t('About') . '</h3>';

        $output .= '<p>' . $this->t('This module expands Tripal Content pages to better
      support Genetic Maps + QTL.') . '</p>';

        return $output;

      default:
    }
  }

}
