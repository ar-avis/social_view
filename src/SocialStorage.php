<?php

namespace Drupal\social_view;

/**
 * Storage implementation for Social View.
 */
class SocialStorage {

  /**
   * Delete records.
   *
   * @param string $type
   *   Plugin type.
   */
  public static function clear($type = NULL) {
    if (empty($type)) {
      \Drupal::database()->delete('social_parsed_data')->execute();
    }
    else {
      \Drupal::database()->delete('social_parsed_data')->condition('type', $type)->execute();
    }
  }

  /**
   * Save records.
   *
   * @param array $data
   *   Record data.
   */
  public static function save(array $data) {
    $transaction = \Drupal::database()->startTransaction();
    try {
      SocialStorage::clear($data[0]['type']);
      foreach ($data as $key => $item) {
        if (!empty($item['body']) && mb_strlen($item['body']) > 1024) {
          $item['body'] = mb_strimwidth($item['body'], 0, 1020, "...");
        }
        \Drupal::database()->insert('social_parsed_data')->fields($item)->execute();
      }
    }
    catch (\Exception $e) {
      $transaction->rollBack();
      \Drupal::logger('social_view')->error('Storage problem:' . $e->getMessage());
    }
  }

}
