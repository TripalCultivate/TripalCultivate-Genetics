<?php

namespace Drupal\trpcultivate_genetics\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Implements hooks for TripalCultivate Genetics module.
 */
class TripalCultivateGeneticsHooks {

  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    switch ($route_name) {
      // Provides the module overview in the help tab.
      case 'help.page.trpcultivate_genetics':
        $output = '';
        $output .= '<h3>' . $this->t('About') . '</h3>';

        $output .= '<ul>'
        . '<li>' . $this->t('Genetic maps, markers, sequence variants and QTL
      - Large-scale genotypic datasets with both') . '</li>'
          . '<ul>'
          . '<li>' . $this->t('the power of a relational database for tight integration with germplasm, phenotypic data and cross data type tools') . '</li>'
          . '<li>' . $this->t('the speed/ease of flat file storage and querying via the Variant Call Format (VCF)') . '</li>'
          . '</ul>'
          . '<li>' . $this->t('Genotype Matrix tool for quick visual querying of genotypic differences between germplasm in smaller regions (e.g. QTL or GWAS peak)') . '</li>'
          . '<li>' . $this->t('Management of metadata for VCF files including a form for researchers to filter and download the results in multiple formats.') . '</li>'
          . '</ul>';

        return $output;

      default:
    }
  }

}
