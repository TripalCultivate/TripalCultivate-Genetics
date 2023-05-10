<?php

namespace Drupal\Tests\trpcultivate_genotypes\Functional\GenotypesLoader;

use Drupal\Core\Url;
use Drupal\Tests\tripal_chado\Functional\ChadoTestBrowserBase;
use Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderPluginManager;
use Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderInterface;

/**
 * A test to call the methods in the plugin base for the genotypes loader.
 *
 * @group TripGeno Genetics
 * @group Genotypes Loader
 */
class GenotypesLoaderPluginManagerTest extends ChadoTestBrowserBase {

  protected $defaultTheme = 'stable';

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = ['trpcultivate_genotypes'];

  /**
   * Test a fake instance of Genotypes Loader Plugin.
   * 
   * @group GenotypesLoader
   */
  public function testGenotypesLoaderPluginManager(){
    
    // Ensure we see all logging in tests.
    \Drupal::state()->set('is_a_test_environment', TRUE);

    // Grab our plugin manager
    $plugin_manager = \Drupal::service('trpcultivate_genotypes.genotypes_loader');

    // Create our options array
    $options = array(
      "organism_id" => "",
      "project_id" => "",
      "variant_subtype_id" => "",
      "marker_subtype_id" => "",
      "input_file_type" => "",
      "input_filepath" => "",
      "sample_filepath" => ""
    );

    // Use our plugin manager to create a plugin and set its parameters
    $plugin = $plugin_manager->setParameters($options);
  
  }
}
