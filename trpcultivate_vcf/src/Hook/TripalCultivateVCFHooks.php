<?php

namespace Drupal\trpcultivate_vcf\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Implements hooks for trpcultivate_vcf module.
 */
class TripalCultivateVCFHooks {
  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    switch ($route_name) {
      // Provides the module overview in the help tab.
      case 'help.page.trpcultivate_vcf':
        $output = '';
        $output .= '<h3>' . $this->t('About') . '</h3>';

        $output .= '<p>' . $this->t('This module creates a tool for easily filtering
      VCF files and converting to different formats.') . '</p>';

        return $output;

      default:
    }
  }

}
