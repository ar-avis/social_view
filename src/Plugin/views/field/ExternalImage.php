<?php

namespace Drupal\social_view\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\views\ResultRow;
use Drupal\views\Plugin\views\field\FieldPluginBase;

/**
 * Field handler to provide simple renderer image from url.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("external_image")
 */
class ExternalImage extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['display_as_url'] = ['default' => FALSE];
    if ($this->moduleHandler->moduleExists('image') && $this->moduleHandler->moduleExists('imagecache_external')) {
      $options['style'] = ['default' => ''];
    }

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['display_as_url'] = [
      '#title' => $this->t('Display as url'),
      '#type' => 'checkbox',
      '#default_value' => !empty($this->options['display_as_url']),
    ];

    if ($this->moduleHandler->moduleExists('image') && $this->moduleHandler->moduleExists('imagecache_external')) {
      $form['style'] = [
        '#type' => 'select',
        '#title' => 'Image style',
        '#options' => image_style_options(),
        '#default_value' => $this->options['display_as_url'],
      ];
    }

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = $this->getValue($values);
    if (empty($this->options['display_as_url'])) {

      if (
        !empty($this->options['style'])
        && $this->moduleHandler->moduleExists('image')
        && $this->moduleHandler->moduleExists('imagecache_external')
      ) {
        return [
          '#theme' => 'imagecache_external',
          '#style_name' => $this->options['style'],
          '#uri' => $value,
          '#alt' => !empty($values->social_parsed_data_name) ? $values->social_parsed_data_name : '',
          '#title' => !empty($values->social_parsed_data_name) ? $values->social_parsed_data_name : '',
        ];
      } else {
        return [
          '#theme' => 'image',
          '#uri' => $value,
          '#alt' => !empty($values->social_parsed_data_name) ? $values->social_parsed_data_name : '',
          '#title' => !empty($values->social_parsed_data_name) ? $values->social_parsed_data_name : '',
        ];
      }
    }
    return $value;
  }

}
