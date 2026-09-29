<?php

namespace Drupal\trpcultivate_qtl\Service;

use Drupal\Core\Config\FileStorage;

/**
 * Service class to setup the module.
 */
class TrpcultivateQtlSetupModuleService {

  /**
   * Import the optional Genetic map view.
   */
  public static function importOptionalView() {
    $dir = \Drupal::service('extension.list.module')->getPath('trpcultivate_qtl');
    $fileStorage = new FileStorage($dir);
    $config = $fileStorage->read('config/optional/views.view.trpcultivate_genetic_maps');

    // Load the storage system for views entities and check if the view
    // already exists. If it does then don't reload it.
    $storage = \Drupal::service('entity_type.manager')->getStorage('view');
    $view = $storage->load('trpcultivate_genetic_maps');
    if (!$view) {
      $view = $storage->create($config);
      $view->save();
    }
  }

}
