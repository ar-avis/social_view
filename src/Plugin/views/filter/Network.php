<?php

namespace Drupal\social_view\Plugin\views\filter;

use Drupal\social_view\Plugin\SocialNetworkViewManager;
use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Simple filter to handle matching of multiple options selectable via checkboxes
 *
 * Definition items:
 * - options callback: The function to call in order to generate the value options. If omitted, the options 'Yes' and 'No' will be used.
 * - options arguments: An array of arguments to pass to the options callback.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("network_select")
 */
class Network extends InOperator {

  /**
   * Child classes should be used to override this function and set the
   * 'value options', unless 'options callback' is defined as a valid function
   * or static public method to generate these values.
   *
   * This can use a guard to be used to reduce database hits as much as
   * possible.
   *
   * @return array|null
   *   The stored values from $this->valueOptions.
   */
  public function getValueOptions() {
    $this->valueOptions = SocialNetworkViewManager::getSelectList();
    return $this->valueOptions;
  }

}
