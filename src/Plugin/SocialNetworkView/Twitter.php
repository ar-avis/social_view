<?php

namespace Drupal\social_view\Plugin\SocialNetworkView;

use Drupal\social_view\Plugin\SocialNetworkViewBase;
use Drupal\social_view\SocialStorage;

/**
 * Twitter network view plugin (https://github.com/J7mbo/twitter-api-php).
 *
 * @SocialNetworkView(
 *   id = "twitter",
 *   title = @Translation("Twitter")
 * )
 */
class Twitter extends SocialNetworkViewBase {

  /**
   * Function parse records and save to db.
   *
   * @param int $count
   *   Count of records for parsing.
   */
  public function parseRecords($count = 3) {
    try {
      $channel_id = !empty($this->settings['chanel_id']) ? $this->settings['chanel_id'] : '';
      $twitter = new \TwitterAPIExchange($this->settings);
      $response = $twitter->setGetfield('?screen_name=' . $channel_id)
        ->buildOauth('https://api.twitter.com/1.1/users/show.json', 'GET')
        ->performRequest();
      $response = json_decode($response, TRUE);
      $page_info = [
        'id' => $response['id'],
        'name' => $response['name'],
        'url' => 'https://twitter.com/' . $response['screen_name'],
        'logo' => $response['profile_image_url'],
      ];

      $response = $twitter->setGetfield('?screen_name=' . $channel_id . '&tweet_mode=extended')
        ->buildOauth('https://api.twitter.com/1.1/statuses/user_timeline.json', 'GET')
        ->performRequest();
      $response = json_decode($response, TRUE);
      $result = [];
      foreach ($response as $key => $record) {
        if (empty($record['in_reply_to_user_id']) && empty($record['retweeted_status']) && $record['user']['id'] == $page_info['id']) {
          $record['text'] = htmlspecialchars_decode($record['full_text'], ENT_QUOTES);
          $record['text'] = preg_replace(
            '/(?<!=|\b|&)#([a-z0-9_]+)/i',
            '<a href="https://twitter.com/hashtag/$1" target="_blank">#$1</a>',
            $record['text']
          );
          $record['text'] = preg_replace(
            '/(?<!=|\b|&)@([a-z0-9_]+)/i',
            '<a href="https://twitter.com/$1" target="_blank">@$1</a>',
            $record['text']
          );

          $image = NULL;
          if (!empty($record['entities']['media'])) {
            foreach ($record['entities']['media'] as $media) {

              if (!empty($media['media_url']) && $media['type'] == 'photo') {
                $image = $media['media_url'];
                break;
              }
            }
          }

          $result[] = [
            'page_info' => $page_info,
            'post_info' => [
              'date' => strtotime($record['created_at']),
              'url' => $page_info['url'] . '/status/' . $record['id'],
              'image' => $image,
              'title' => !empty($this->settings['title']) ? t($this->settings['title']) : $page_info['name'] . ' #' . $record['id'],
              'body' => $record['text'],
            ]
          ];
        }

        if (count($result) >= $count) {
          break;
        }
      }
      SocialStorage::save($this->getPluginId(), $result);
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

    $form['consumer_key'] = [
      '#title' => t('Consumer Key'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['consumer_key']) ? $config['consumer_key'] : '',
    ];

    $form['consumer_secret'] = [
      '#title' => t('Consumer Secret'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['consumer_secret']) ? $config['consumer_secret'] : '',
    ];

    $form['oauth_access_token'] = [
      '#title' => t('Access Token'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['oauth_access_token']) ? $config['oauth_access_token'] : '',
    ];

    $form['oauth_access_token_secret'] = [
      '#title' => t('Access Token Secret'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['oauth_access_token_secret']) ? $config['oauth_access_token_secret'] : '',
    ];

    $form['chanel_id'] = [
      '#title' => t('Chanel id'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['chanel_id']) ? $config['chanel_id'] : '',
    ];
    return $form;
  }

}
