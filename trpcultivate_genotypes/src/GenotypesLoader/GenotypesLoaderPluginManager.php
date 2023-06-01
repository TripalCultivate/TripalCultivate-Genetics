<?php

namespace Drupal\trpcultivate_genotypes\GenotypesLoader;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * GenotypesLoader plugin manager.
 */
class GenotypesLoaderPluginManager extends DefaultPluginManager {

  /**
   * Constructs GenotypesLoaderPluginManager object.
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct(
      'Plugin/GenotypesLoader',
      $namespaces,
      $module_handler,
      'Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderInterface',
      'Drupal\trpcultivate_genotypes\Annotation\GenotypesLoader'
    );
    $this->alterInfo('genotypes_loader_info');
    $this->setCacheBackend($cache_backend, 'genotypes_loader_plugins');
  }

  /**
   * Create plugin and set its parameters.
   * 
   * @param array $options
   *   An array of options that can be used to determine a suitable plugin to instantiate and how to configure it.
   *    - organism_id: The chado.stock.organism_id of the samples in the samples file.
   *    - project_id: The chado.project.project_id that these genotypes are grouped under.
   *    - variant_subtype_id: The cvterm_id of the subtype of variant of the genotypes being inserted.
   *    - marker_subtype_id: The cvterm_id of the subtype of marker of the genotypes being inserted.
   *    - input_file_type: one of "vcf", "matrix", "legacy".
   *    - input_filepath: The filepath of the input file containing the genotypes.
   *    - sample_filepath: The filepath of the tab-delimited file specifying each sample name in the genotypes file.
   * @return object|false 
   *   A fully configured plugin instance. The interface of the plugin instance will depend on the plugin type. 
   *   If no instance can be retrieved, FALSE will be returned.
   */
  public function setParameters(array $options) {

    // Chooses plugin implementation based on parameters (e.g. VCF implementation for VCF file format)
    // Collect all the plugin IDs that match our input file type
    $supported_pluginIDs = [];
    $definitions = $this->getDefinitions();
    foreach($definitions as $pluginId => $details) {
      if ($details['input_file_type'] == ($options['input_file_type'])) {
        $supported_pluginIDs[] = $pluginId;
      };
    }

    // Check the size of the supported_pluginIDs array. If multiple values or no values, throw an exception
    $num_pluginIds = count($supported_pluginIDs);
    if ($num_pluginIds > 1) {
      throw new \Exception(
        t("Found more than one pluginID for input file type of '@type'. This is currently unsupported functionality." , ['@type'=>$options['input_file_type']])
      );
    }
    if ($num_pluginIds == 0) {
      throw new \Exception(
        t("Could not find a pluginID for input file type of '@type'." , ['@type'=>$options['input_file_type']])
      );
    }

    // Creates a GenotypesLoader object for that implementation (e.g. returns VCFGenotypesLoader which inherits from GenotypesLoader)
    $plugin_instance = $this->createInstance($pluginId, []);

    // Uses the Base Class setter methods to set the parameters on that object
    // Catches any exceptions which indicate validation errors and reports back to caller of plugin manager these errors
    try {
      $plugin_instance->setOrganismID($options['organism_id']);
      $plugin_instance->setProjectID($options['project_id']);
      $plugin_instance->setVariantSubtypeID($options['variant_subtype_id']);
      $plugin_instance->setMarkerSubtypeID($options['marker_subtype_id']);
      $plugin_instance->setInputFileType($options['input_file_type']);
      $plugin_instance->setInputFilePath($options['input_filepath']);
      $plugin_instance->setSampleFilePath($options['sample_filepath']);
    }
    catch ( \Exception $e ) { 
      throw new \Exception(
        t("Could not set a parameter while instanciating GenotypesLoader: '@pluginId'" , ['@pluginId'=>$pluginId]) . $e->getMessage()
      );
    }

    // Returns the fully initialized GenotypesLoader object for that implementation (only if there were no errors)
    return $plugin_instance;
  }
}
