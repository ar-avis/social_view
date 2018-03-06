<?php

/**
 * @file
 * Contains social_view.views.inc.
 */

/**
 * Implements hook_views_data().
 */
function social_view_views_data() {
  $data = [];

  $data['social_parsed_data']['table']['base'] = [
    'field' => 'id',
    'title' => t('Social View'),
    'help' => t('Parsed data from social networks by Social View module.'),
  ];

  $data['social_parsed_data']['table']['group'] = t('Content');
  $data['social_parsed_data']['table']['provider'] = 'social_parsed_data';

  $data['social_parsed_data']['id'] = [
    'title' => t('ID'),
    'field' => [
      'id' => 'numeric',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['social_parsed_data']['type'] = [
    'title' => t('Plugin type'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'in_operator',
      'options callback' => '\Drupal\social_view\Plugin\SocialNetworkViewManager::getSelectList',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['social_parsed_data']['date'] = [
    'title' => t('Date'),
    'field' => [
      'id' => 'date',
    ],
    'argument' => [
      'id' => 'date',
    ],
    'filter' => [
      'id' => 'date',
    ],
    'sort' => [
      'id' => 'date',
    ],
  ];

  $data['social_parsed_data']['name'] = [
    'title' => t('Page name'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];

  $data['social_parsed_data']['url'] = [
    'title' => t('Page url'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
  ];

  $data['social_parsed_data']['logo'] = [
    'title' => t('Page logo'),
    'field' => [
      'id' => 'external_image',
    ],
  ];

  $data['social_parsed_data']['image'] = [
    'title' => t('Image'),
    'field' => [
      'id' => 'external_image',
    ],
  ];

  $data['social_parsed_data']['title'] = [
    'title' => t('Title'),
    'field' => [
      'id' => 'standard',
    ],
    'argument' => [
      'id' => 'string',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'sort' => [
      'id' => 'standard',
    ],
  ];
  $data['social_parsed_data']['body'] = [
    'title' => t('Body'),
    'field' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
  ];
  return $data;
}
