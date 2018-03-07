<?php

namespace Drupal\social_view;
use Drupal\node\Entity\Node;

/**
 * Storage implementation for Social View.
 */
class SocialStorage {

  /**
   * Save records.
   *
   * @param string $type
   *   Recod type.
   *
   * @param array $data
   *   Record data.
   */
  public static function save($type, array $data) {
    $exists = [];
    $nodes = \Drupal::entityQuery('node')
      ->condition('type', 'social_feed')
      ->condition('field_social_feed_type', $type)
      ->exists('field_social_feed_url')
      ->execute();

    $nodes = Node::loadMultiple($nodes);
    if (!empty($nodes)) {
      foreach ($nodes as $node) {
        $url = $node->get('field_social_feed_url')->value;
        $exists[] = $url;
      }
    }

    foreach ($data as $item) {
      if (!in_array($item['post_info']['url'], $exists)) {
        $node = Node::create([
          'type' => 'social_feed',
          'title' => $item['post_info']['title'],
          'body' => [
            'value' => $item['post_info']['body'],
            'format' => 'full_html',
          ],
          'created' => $item['post_info']['date'],
          'field_social_feed_url' => $item['post_info']['url'],
          'field_social_feed_type' => $type,
          'field_social_feed_image_url' => $item['post_info']['image'],
        ]);
        $node->save();
      }
    }
  }

}
