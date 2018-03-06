<?php

namespace Drupal\social_view\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\social_view\Plugin\SocialNetworkViewManager;

/**
 * Class for Settings Form.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'social_view_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['social_view.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('social_view.settings');
    $config_values = $config->getRawData();

    $timestamp = \Drupal::state()->get('social_view_timestamp');

    $form['interval'] = [
      '#title' => t('Minimum parsing interval'),
      '#description' => t("Can't be less than Cron interval. Sets in seconds.")
      . ' ' . t('Last parsing:') . ' ' . date('d.m.Y H:i', $timestamp),
      '#type' => 'number',
      '#default_value' => !empty($config_values['interval']) ? $config_values['interval'] : 300,
    ];

    foreach (SocialNetworkViewManager::getList() as $network) {
      $mach_name = $network['id'];
      $name = $network['title'];
      $network = SocialNetworkViewManager::load($mach_name);
      $config_values[$mach_name] = !empty($config_values[$mach_name]) ? $config_values[$mach_name] : [];
      $fields = $network->getSettingsForm($config_values[$mach_name]);

      $form['network_' . $mach_name] = [
        '#title' => $name,
        '#type' => 'details',
        '#open' => TRUE,
        '#tree' => TRUE,
      ];
      $form['network_' . $mach_name]['enabled'] = [
        '#type' => 'checkbox',
        '#title' => t('Enabled'),
        '#default_value' => !empty($config_values[$mach_name]['enabled']),
      ];
      $form['network_' . $mach_name]['link'] = [
        '#type' => 'textfield',
        '#title' => t('Link for social block'),
        '#default_value' => !empty($config_values[$mach_name]['link']) ? $config_values[$mach_name]['link'] : '',
      ];
      $form['network_' . $mach_name]['title'] = [
        '#type' => 'textfield',
        '#title' => t('Title'),
        '#default_value' => !empty($config_values[$mach_name]['title']) ? $config_values[$mach_name]['title'] : '',
      ];

      if (!empty($fields)) {
        $form['network_' . $mach_name] += $fields;
      }
    }
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = $this->config('social_view.settings');
    $values = $form_state->getValues();
    foreach ($values as $key => $value) {
      if (strpos($key, 'network_') !== FALSE) {
        $config->set(str_replace('network_', '', $key), $value);
      }
    }
    $config->set('interval', $values['interval']);
    $config->save();
    \Drupal::state()->set('social_view_timestamp', 0);
    drupal_set_message(t('Configuration was saved.'));
  }

}
