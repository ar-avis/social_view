<?php

namespace Drupal\social_view\Plugin\SocialNetworkView;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\social_view\Plugin\SocialNetworkViewBase;
use Drupal\social_view\SocialStorage;
use MetzWeb\Instagram\Instagram as InstagramLib;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Instagram network view plugin (https://www.instagram.com/developer/).
 *
 * @SocialNetworkView(
 *   id = "instagram",
 *   title = @Translation("Instagram")
 * )
 */
class Instagram extends SocialNetworkViewBase {
  private $client = NULL;

  /**
   * Function parse records and save to db.
   *
   * @param int $count
   *   Count of records for parsing.
   */
  public function parseRecords($count = 3) {
    $client = new InstagramLib($this->settings['client_id']);
    $client->setAccessToken($this->settings['app_token']);

    try {
      // Get the media for the current user.
      $response = $client->getUserMedia('self', $count);

      $records = $response->data;
      $result = [];

      foreach ($records as $key => $record) {
        $record->caption->text = !empty($record->caption->text) ? htmlspecialchars_decode($record->caption->text, ENT_QUOTES) : '';
        $record->caption->text = preg_replace(
          '/(?<!=|\b|&)#([a-z0-9_]+)/i',
          '<a href="https://www.instagram.com/explore/tags/$1" target="_blank">#$1</a>',
          $record->caption->text
        );


        $result[] = [
          'type' => $this->getPluginId(),
          'date' => $record->created_time,
          'name' => $record->user->full_name,
          'url' => 'https://www.instagram.com/' . $record->user->username,
          'logo' => $record->user->profile_picture,
          'image' =>  $record->images->standard_resolution->url,
          'title' => !empty($this->settings['title']) ? t($this->settings['title']) : $record->user->username,
          'body' => $record->caption->text,
        ];
      }
      SocialStorage::save($result);
    }
    catch (\Exception $e) {
      \Drupal::logger('social_view')->warning($this->getPluginId() . ': ' . $e->getMessage());
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

    $form['client_id'] = [
      '#title' => t('Client id'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['client_id']) ? $config['client_id'] : '',
    ];

    $form['client_secret'] = [
      '#title' => t('Client secret'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['client_secret']) ? $config['client_secret'] : '',
    ];

    if (!empty($config['client_id']) && !empty($config['client_secret'])) {
      $instagram = new InstagramLib([
        'apiKey'      => $config['client_id'],
        'apiSecret'   => $config['client_secret'],
        'apiCallback' => Url::fromRoute('<current>')->setAbsolute()->toString()
      ]);

      if (!empty($config['client_id']) && !empty($_REQUEST['code'])) {
        $data = $instagram->getOAuthToken($_REQUEST['code']);
        $config['app_token'] = $data->access_token;
        $update = \Drupal::configFactory()
          ->getEditable('social_view.settings')
          ->set($this->getPluginId(), $config);
        $update->save();

        $page = $_SERVER['REDIRECT_URL'];
        header("Refresh: 0; url=$page");
      }

      $form['app_token'] = [
        '#title' => t('Access token'),
        '#type' => 'textfield',
        '#default_value' => !empty($config['app_token']) ? $config['app_token'] : '',
      ];

      $url = $instagram->getLoginUrl(array(
        'basic',
        'likes',
        'relationships'
      ));
      $form['regenerate'] = Link::fromTextAndUrl(t('Generate new token'), Url::fromUri($url))
        ->toRenderable();
    }
    return $form;
  }

}
