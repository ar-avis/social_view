<?php

namespace Drupal\social_view\Plugin\Block;

/**
 * @file
 * Contains \Drupal\social_view\Plugin\Block\SocialLinksBlock.
 */

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Cache\CacheBackendInterface;

/**
 * Provides a 'Social links' block.
 *
 * @Block(
 *   id = "social_links",
 *   admin_label = @Translation("Social links"),
 *   category = @Translation("Custom")
 * )
 */
class SocialLinksBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = \Drupal::config('social_view.settings')->getRawData();
    $links = [];
    foreach ($config as $id => $row) {
      if (!empty($row['link'])) {
        $links[$id] = Link::fromTextAndUrl(ucfirst($id), Url::fromUri($row['link']))->toRenderable();
        $links[$id]['#attributes']['class'] = [strtolower($links[$id]['#title'])];
      }
    }
    return [
      '#theme' => 'social_links',
      '#links' => $links,
      '#cache' => [
        'keys' => [
          'entity_view',
          'block',
          'social_links',
          'full'
        ],
        'contexts' => [],
        'tags' => ['block:social_links'],
        'max-age' => CacheBackendInterface::CACHE_PERMANENT,
      ],
    ];
  }

}
