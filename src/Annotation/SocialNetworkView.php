<?php

namespace Drupal\social_view\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a Social network view item annotation object.
 *
 * @see \Drupal\social_view\Plugin\SocialNetworkViewManager
 * @see plugin_api
 *
 * @Annotation
 */
class SocialNetworkView extends Plugin {


  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
