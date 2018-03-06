<?php

namespace Drupal\social_view\Plugin\SocialNetworkView;

use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\social_view\Plugin\SocialNetworkViewBase;
use Drupal\social_view\SocialStorage;
use Symfony\Component\HttpFoundation\RedirectResponse;
use LinkedIn\LinkedIn as LinkedInLib;

/**
 * Linkedin network view plugin (https://www.linkedin.com/secure/developer).
 *
 * @SocialNetworkView(
 *   id = "linkedin",
 *   title = @Translation("Linkedin")
 * )
 */
class Linkedin extends SocialNetworkViewBase {

  /**
   * Function init new LinkedIn class.
   */
  private function init() {
    $redirect = 'http://aft.docksal/admin/config/content/social_view/settings';
    return new LinkedInLib(
      [
        'api_key' => $this->settings['client_id'],
        'api_secret' => $this->settings['client_secret'],
        'callback_url' => $redirect,
      ]
    );
  }

  /**
   * Function parse records and save to db.
   *
   * @param int $count
   *   Count of records for parsing.
   */
  public function parseRecords($count = 3) {
    try {
      $li = $this->init();
      $li->setAccessToken($this->settings['token']);
      $info = $li->get('/companies/' . $this->settings['company_id'] . ':(id,name,square-logo-url)');

      if (!empty($info)) {
        $page_info = [
          'name' => $info['name'],
          'url' => 'https://www.linkedin.com/company/' . $info['id'],
          'logo' => $info['squareLogoUrl'],
        ];

        $records = $li->get('/companies/' . $this->settings['company_id'] . '/updates');
        $result = [];
        $count = 0;
        foreach ($records['values'] as $record) {
          if ($count >= 3) {
            break;
          }
          $result[] = [
            'type' => $this->getPluginId(),
            'date' => ($record['updateContent']['companyStatusUpdate']['share']['timestamp'] / 1000),
            'name' => $page_info['name'],
            'url' => $page_info['url'],
            'logo' => $page_info['logo'],
            'title' => !empty($this->settings['title']) ? t($this->settings['title']) : '',
            'body' => htmlspecialchars_decode($record['updateContent']['companyStatusUpdate']['share']['comment'], ENT_QUOTES),
          ];
          $count++;
        }
        SocialStorage::save($result);
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

    if (!empty($config['client_id']) && !empty($_REQUEST['code'])) {
      $li = $this->init();

      $token = $li->getAccessToken($_REQUEST['code']);
      if (!empty($token)) {
        $config['token'] = $token;
        $config['token_expires'] = \Drupal::time()->getRequestTime() + $li->getAccessTokenExpiration();
        \Drupal::configFactory()->getEditable('social_view.settings')->set($this->getPluginId(), $config)->save();
        $redirect = new RedirectResponse(Url::fromRoute('social_view.settings')->toString());
        $redirect->send();
      }
    }

    $form['client_id'] = [
      '#title' => t('Client ID'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['client_id']) ? $config['client_id'] : '',
    ];

    $form['client_secret'] = [
      '#title' => t('Client Secret'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['client_secret']) ? $config['client_secret'] : '',
    ];

    $form['company_id'] = [
      '#title' => t('Company ID'),
      '#type' => 'textfield',
      '#default_value' => !empty($config['company_id']) ? $config['company_id'] : '',
    ];

    $form['token'] = [
      '#title' => t('Token'),
      '#type' => 'textfield',
      '#disabled' => TRUE,
      '#default_value' => !empty($config['token']) ? $config['token'] : '',
    ];

    $form['token_expires'] = [
      '#title' => t('Token expires'),
      '#type' => 'textfield',
      '#disabled' => TRUE,
      '#default_value' => !empty($config['token_expires']) ? date('d.m.Y H:i', $config['token_expires']) : '',
    ];

    if (!empty($config['client_id'])) {
      $li = $this->init();
      $url = $li->getLoginUrl([
        LinkedInLib::SCOPE_BASIC_PROFILE,
        'rw_company_admin'
      ]);
      $form['regenerate'] = Link::fromTextAndUrl(t('Generate new token'), Url::fromUri($url))
        ->toRenderable();
    }

    return $form;
  }

}
