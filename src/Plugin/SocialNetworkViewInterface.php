<?php

namespace Drupal\social_view\Plugin;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for Social network view plugins.
 */
interface SocialNetworkViewInterface extends PluginInspectionInterface {

  /**
   * Function return network enable setting.
   */
  public function isEnable();

  /**
   * Function parse records and save to db.
   *
   * @param int $count
   *   Count of records for parsing.
   */
  public function parseRecords($count = 3);

  /**
   * Return settings form.
   *
   * @param array $config
   *   Current settings for this network.
   *
   * @return array
   *   Form extra fields array.
   */
  public function getSettingsForm(array $config);

  /**
   * Return settings for current network.
   *
   * @param string $network
   *   Network id.
   *
   * @return array
   *   Network plugin settings.
   */
  public function getSettings($network = NULL);

}
