<?php

namespace Drupal\trpcultivate_genomatrix\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Implements hooks for tripalcultivate genomatrix module.
 */
class TripalCultivateGenomatrixHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    switch ($route_name) {
      // Provides the module overview in the help tab.
      case 'help.page.trpcultivate_genomatrix':
        $output = '';
        $output .= '<h3>' . $this->t('About') . '</h3>';

        $output .= '<p>' . $this->t('This module creates a germplasm by marker matrix
      tool for quick visual querying of genotypic differences in smaller
      regions (e.g. QTL or GWAS peak).') . '</p>';

        $output .= '<p>' . $this->t('This tool is referred to as the Genotype Matrix and
      can be accessed from a stand-alone tool page, as well as, through links
      on Tripal Content Pages provided by specialized Tripal Fields. For example,
      a field is added to Germplasm pages providing a link to the Genotype Matrix
      with that particular germplasm already added.') . '</p>';

        return $output;

      default:
    }
  }

}
