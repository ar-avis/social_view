<?php

namespace Drupal\social_view\Plugin;

use Drupal\Component\Plugin\PluginBase;

/**
 * Base class for Social network view plugins.
 */
abstract class SocialNetworkViewBase extends PluginBase implements SocialNetworkViewInterface {
  public $settings = NULL;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->settings = $this->getSettings();
  }

  /**
   * Function return network enable setting.
   *
   * @return bool
   *   Enable frag.
   */
  public function isEnable() {
    return (bool) $this->settings['enabled'];
  }

  /**
   * Return settings for current network.
   *
   * @param string $network
   *   Network id.
   *
   * @return array
   *   Network plugin settings.
   */
  public function getSettings($network = NULL) {
    if (empty($network)) {
      $network = $this->getPluginId();
    }
    return \Drupal::config('social_view.settings')->get($network);
  }

}
