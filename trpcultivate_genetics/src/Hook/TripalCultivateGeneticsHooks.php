<?php

namespace Drupal\trpcultivate_genetics\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\tripal\Services\TripalLogger;

/**
 * Implements hooks for TripalCultivate Genetics module.
 */
class TripalCultivateGeneticsHooks {

  use StringTranslationTrait;

  /**
   * The TripalLogger service.
   *
   * @var Drupal\tripal\Services\TripalLogger
   */
  protected $logger;

  /**
   * Constructs a TripalCultivateGeneticsHooks object.
   *
   * @param Drupal\tripal\Services\TripalLogger $logger
   *   The TripalLogger service.
   */
  public function __construct(TripalLogger $logger) {
    $this->logger = $logger;
  }

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

  /**
   * Implements hook_config_schema_info_alter().
   *
   * Update the schema to support markup fields.
   */
  #[Hook('config_schema_info_alter')]
  public function configSchemaInfoAlter(&$definitions) {

    // Support for the File Field being used on a TripalEntity.
    // -- field settings.
    if (array_key_exists('field.field_settings.file', $definitions)) {
      foreach ($definitions['field.field_settings.file']['mapping'] as $setting_key => $settings) {
        $definitions['field.field.tripal_entity.*.*']['mapping']['settings']['mapping'][$setting_key] = $settings;
      }
    }
    else {
      $this->logger->error("Tripal Cultivate Genetics module requires the File Field for its content types but it seems to be missing as the 'field.field_settings.file' schema definition is unavailable.");
    }
    // -- field storage settings.
    if (array_key_exists('field.storage_settings.file', $definitions)) {
      foreach ($definitions['field.storage_settings.file']['mapping'] as $setting_key => $settings) {
        $definitions['field.storage.tripal_entity.*']['mapping']['settings']['mapping'][$setting_key] = $settings;
      }
    }
    else {
      $this->logger->error("Tripal Cultivate Genetics module requires the File Field for its content types but it seems to be missing as the 'field.storage_settings.file' schema definition is unavailable.");
    }
    // Support for Third Party Tripal field settings being used on TripalEntity.
    // @todo this should likely be in tripal core.
    if (!array_key_exists('third_party_settings', $definitions['field.field.tripal_entity.*.*']['mapping'])) {
      $definitions['field.field.tripal_entity.*.*']['mapping']['third_party_settings'] = [
        'type' => 'mapping',
        'mapping' => [],
      ];
    }
    if (!array_key_exists('tripal', $definitions['field.field.tripal_entity.*.*']['mapping']['third_party_settings']['mapping'])) {
      $definitions['field.field.tripal_entity.*.*']['mapping']['third_party_settings']['mapping']['tripal'] = [
        'type' => 'mapping',
        'mapping' => [],
      ];
    }
    $definitions['field.field.tripal_entity.*.*']['mapping']['third_party_settings']['mapping']['tripal']['mapping']['termIdSpace'] = [
      'type' => 'string',
      'label' => 'Term ID Space',
      'nullable' => TRUE,
    ];
    $definitions['field.field.tripal_entity.*.*']['mapping']['third_party_settings']['mapping']['tripal']['mapping']['termAccession'] = [
      'type' => 'string',
      'label' => 'Term Accession',
      'nullable' => TRUE,
    ];
    $definitions['field.field.tripal_entity.*.*']['mapping']['settings']['mapping']['file_directory'] = [
      'type' => 'string',
      'label' => 'File Directory',
      'nullable' => TRUE,
    ];
    $definitions['field.field.tripal_entity.*.*']['mapping']['settings']['mapping']['file_extensions'] = [
      'type' => 'string',
      'label' => 'File Extensions',
      'nullable' => TRUE,
    ];
    $definitions['field.field.tripal_entity.*.*']['mapping']['settings']['mapping']['max_filesize'] = [
      'type' => 'string',
      'label' => 'Maximum Upload Size',
      'nullable' => FALSE,
    ];
  }

}
