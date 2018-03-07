<?php

/**
 * @file
 * Contains custom_views_logic.views_execution.inc.
 */

/**
 * Implements hook_views_data_alter().
 */
function social_view_views_data_alter(&$data) {
  $data['node__field_social_feed_type']['field_social_feed_type_value']['filter']['id'] = 'network_select';
}
