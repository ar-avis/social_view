<?php

namespace Drupal\social_view\Plugin\SocialNetworkView;

use Drupal\social_view\Plugin\SocialNetworkViewBase;
use Drupal\social_view\SocialStorage;
use Facebook\Facebook as FacebookLib;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;

/**
 * Facebook network view plugin (https://developers.facebook.com/apps/).
 *
 * @SocialNetworkView(
 *   id = "facebook",
 *   title = @Translation("Facebook")
 * )
 */
class Facebook extends SocialNetworkViewBase {
  private $fb = NULL;

  /**
   * Function parse records and save to db.
   *
   * @param int $count
   *   Count of records for parsing.
   */
  public function parseRecords($count = 3) {
    $this->fb = new FacebookLib([
      'app_id' => $this->settings['app_id'],
      'app_secret' => $this->settings['app_secret'],
      'default_graph_version' => 'v2.10',
      'default_access_token' => $this->settings['app_id'] . '|' . $this->settings['app_secret'],
    ]);

    $page_id = $this->settings['page_id'];
    try {
      // Get the \Facebook\GraphNodes\GraphUser object for the current user.
      $response = $this->fb->get('/' . $page_id . '?fields=name,about,link,emails,picture{url}');
      $response = json_decode($response->getBody());
      $page_info = [
        'id' => $response->id,
        'name' => $response->name,
        'url' => $response->link,
        'logo' => $response->picture->data->url,
      ];

      // Get the \Facebook\GraphNodes\GraphUser object for the current user.
      $response = $this->fb->get(
        '/' . $page_id . '/feed?fields=id,message,attachments,from,created_time'
      );

      $response = json_decode($response->getBody());
      $records = $response->data;
      $result = [];

      foreach ($records as $key => $record) {
        if (!empty($record->from->id) && $record->from->id == $page_info['id']) {
          $record->message = !empty($record->message) ? htmlspecialchars_decode($record->message, ENT_QUOTES) : '';
          $record->message = preg_replace(
            '/(?<!=|\b|&)#([a-z0-9_]+)/i',
            '<a href="https://www.facebook.com/hashtag/$1" target="_blank">#$1</a>',
            $record->message
          );

          $image = NULL;
          if (!empty($record->attachments->data[0]->media->image->src)) {
            $image = $record->attachments->data[0]->media->image->src;
          } elseif (!empty($record->attachments->data[0]->subattachments->data[0]->media->image->src)) {
            $image = $record->attachments->data[0]->subattachments->data[0]->media->image->src;
          }
          $result[] = [
            'type' => $this->getPluginId(),
            'date' => strtotime($record->created_time),
            'name' => $page_info['name'],
            'url' => $page_info['url'],
            'logo' => $page_info['logo'],
            'image' =>  $image,
            'title' => !empty($this->settings['title']) ? t($this->settings['title']) : $record->from->name,
            'body' => $record->message,
          ];
          if (count($result) >= $count) {
            break;
          }
        }
      }
      SocialStorage::save($result);
    }
    catch (FacebookResponseException $e) {
      \Drupal::logger('social_view')->warning($this->getPluginId() . ' - Graph returned an error: ' . $e->getMessage());
    }
    catch (FacebookSDKException $e) {
      \Drupal::logger('social_view')->warning($this->getPluginId() . ' - Facebook SDK returned an error: ' . $e->getMessage());
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

    $form['app_id'] = [
      '#title' => t('Application id'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['app_id']) ? $config['app_id'] : '',
    ];

    $form['app_secret'] = [
      '#title' => t('Application secret'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['app_secret']) ? $config['app_secret'] : '',
    ];

    $form['page_id'] = [
      '#title' => t('page_id'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['page_id']) ? $config['page_id'] : '',
    ];
    return $form;
  }

}
