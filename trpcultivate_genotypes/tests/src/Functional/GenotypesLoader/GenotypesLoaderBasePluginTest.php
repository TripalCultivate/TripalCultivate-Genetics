<?php

namespace Drupal\Tests\trpcultivate_genotypes\Functional\GenotypesLoader;

use Drupal\Core\Url;
use Drupal\Tests\tripal_chado\Functional\ChadoTestBrowserBase;
use Drupal\Tests\trpcultivate_genotypes\Functional\GenotypesLoader\Subclass\GenotypesLoaderFakePlugin;
use Drupal\trpcultivate_genotypes\GenotypesLoader\GenotypesLoaderPluginBase;

/**
 * A test to call the methods in the plugin base for the genotypes loader.
 *
 * @group TripGeno Genetics
 * @group Genotypes Loader
 */
class GenotypesLoaderBasePluginTest extends ChadoTestBrowserBase {

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
  public function testGenotypesLoaderPluginBaseGetSet(){

    // Create the Genotypes Loader object
    // Configuration should be any key value pairs specific to Genotypes Loader plugin
    $configuration = [];
    $plugin_definition = [];
    $plugin = new GenotypesLoaderFakePlugin($configuration,"fake_genotypes_loader",$plugin_definition);
    $this->assertIsObject($plugin, 'Unable to create a Plugin');

    // Open connection to Chado
    $connection = \Drupal::service('tripal_chado.database');

    // Create an organism
    $organism_id = $connection->insert('chado.organism')
      ->fields(['genus', 'species'])
      ->values([
        'genus' => 'Tripalus',
        'species' => 'databasica',
      ])
      ->execute();

    $success = $plugin->setOrganismID($organism_id);
    $this->assertTrue($success,"Unable to set organism_id");

  }
}