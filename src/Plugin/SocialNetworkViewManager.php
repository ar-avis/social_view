<?php

namespace Drupal\social_view\Plugin;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Provides the Social network view plugin manager.
 */
class SocialNetworkViewManager extends DefaultPluginManager {

  /**
   * Constructs a new SocialNetworkViewManager object.
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
    parent::__construct('Plugin/SocialNetworkView', $namespaces, $module_handler, 'Drupal\social_view\Plugin\SocialNetworkViewInterface', 'Drupal\social_view\Annotation\SocialNetworkView');

    $this->alterInfo('social_view_social_network_view_info');
    $this->setCacheBackend($cache_backend, 'social_view_social_network_view_plugins');
  }

  /**
   * Return array of social networks.
   *
   * @return array
   *   List of plugins.
   */
  public static function getList() {
    return \Drupal::service('plugin.manager.social_network_view')
      ->getDefinitions();
  }

  /**
   * Return array of payment methods for select.
   *
   * @return array
   *   List of plugin as assoc array.
   */
  public static function getSelectList() {
    $types = SocialNetworkViewManager::getList();
    $options = [];
    foreach ($types as $type) {
      $plugin = SocialNetworkViewManager::load($type['id']);
      if ($plugin->isEnable()) {
        $options[$type['id']] = $type['title'];
      }
    }
    asort($options);
    return $options;
  }

  /**
   * Return social network object.
   *
   * @param string $id
   *   The method machine name.
   *
   * @return object
   *   Plugin object.
   */
  public static function load($id) {
    if (!\Drupal::service('plugin.manager.social_network_view')->hasDefinition($id)) {
      throw new NotFoundHttpException();
    }
    return \Drupal::service('plugin.manager.social_network_view')
      ->createInstance($id);
  }

}
