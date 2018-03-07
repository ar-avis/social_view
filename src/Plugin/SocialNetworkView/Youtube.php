<?php

namespace Drupal\social_view\Plugin\SocialNetworkView;

use Drupal\social_view\Plugin\SocialNetworkViewBase;
use Drupal\social_view\SocialStorage;
use Incremental\YouTube\YouTube as YoutubeLib;

/**
 * Youtube network view plugin (https://console.cloud.google.com/apis/dashboard?project=api-project-214747612809&hl=ru&duration=PT1H).
 *
 * @SocialNetworkView(
 *   id = "youtube",
 *   title = @Translation("Youtube")
 * )
 */
class Youtube extends SocialNetworkViewBase {

  /**
   * Function parse records and save to db.
   *
   * @param int $count
   *   Count of records for parsing.
   */
  public function parseRecords($count = 3) {
    try {
      $youtube = new YoutubeLib($this->settings['api_key']);
      $response = $youtube->listChannels([
        'part'       => 'snippet, contentDetails',
        'id'  => $this->settings['chanel_id'],
        'maxResults' => 1,
      ]);

      if (!empty($response['items'][0])) {
        $page_info = [
          'name' => $response['items'][0]['snippet']['title'],
          'url' => 'https://www.youtube.com/channel/' . $response['items'][0]['id'],
          'logo' => $response['items'][0]['snippet']['thumbnails']['default']['url'],
        ];

        $videos = $youtube->listPlaylistItems([
          'part'          => 'snippet',
          'playlistId'    => $response['items'][0]['contentDetails']['relatedPlaylists']['uploads'],
          'maxResults'    => $count,
        ]);

        $result = [];
        foreach ($videos['items'] as $key => $record) {
          $result[] = [
            'page_info' => $page_info,
            'post_info' => [
              'date' => strtotime($record['snippet']['publishedAt']),
              'url' => 'https://youtu.be/' . $record['snippet']['resourceId']['videoId'],
              'image' => $record['snippet']['thumbnails']['standard']['url'],
              'title' => !empty($this->settings['title']) ? t($this->settings['title']) : $record['snippet']['title'] . ' #' . $record['snippet']['resourceId']['videoId'],
              'body' => htmlspecialchars_decode($record['snippet']['description'], ENT_QUOTES),
            ],
          ];
        }
        SocialStorage::save($this->getPluginId(), $result);
      }
    }
    catch (\Exception $e) {
      \Drupal::logger('social_view')->warning($this->getPluginId() . ' - Error: ' . $e->getMessage());
    }
  }

  /**
   * Return settings form.
   *
   * @param array $config
   *   Current settings for this network.
   *
   * @return array
   *   Form extra fields array.
   */
  public function getSettingsForm(array $config) {
    $form = [];

    $form['api_key'] = [
      '#title' => t('Api key'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['api_key']) ? $config['api_key'] : '',
    ];
    $form['chanel_id'] = [
      '#title' => t('Chanel id'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['chanel_id']) ? $config['chanel_id'] : '',
    ];
    return $form;
  }

}
