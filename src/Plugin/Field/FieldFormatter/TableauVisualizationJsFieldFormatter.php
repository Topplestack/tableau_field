<?php

namespace Drupal\tableau_vis\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\file\Entity\File;

/**
 * Plugin implementation of the 'format_tableau_js' formatter.
 *
 * Class TableauVisualizationJsFieldFormatter
 * @package Drupal\tableau_vis\Plugin\Field\FieldFormatter
 *
 * @FieldFormatter(
 *   id = "format_tableau_js",
 *   label = @Translation("Tableau JS Script"),
 *   field_types = {
 *     "tableau_vis"
 *   }
 * )
 *
 */
class TableauVisualizationJsFieldFormatter extends FormatterBase {

  /**
   * @inheritDoc
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $config = \Drupal::service('config.factory')->getEditable('tableau_visualization.settings');
    $tableau_placeholder_url = '';
    $element = array();
    $tableau_honeypot = !empty($config->get('tableau_honeypot')) ? $config->get('tableau_honeypot') : '';
    $tableau_bot_blocker = !empty($config->get('tableau_bot_blocker')) ? $config->get('tableau_bot_blocker') : '';
    if ($tableau_honeypot == 1) {
      $honeypot_link = '<a class="honeypot-link-element" href="/bfas" rel="nofollow" style="display: none;" aria-hidden="true">bfas</a>';
    }

    // Output based on selected formatter
    $tableau_server_url = !empty($config->get('tableau_server')) ? $config->get('tableau_server') : '';
    $tableau_target_site = !empty($config->get('tableau_target_site')) ? $config->get('tableau_target_site') : '';
    $tableau_timeout = !empty($config->get('tableau_timeout')) ? $config->get('tableau_timeout') : '';
    $tableau_breakpoint = !empty($config->get('tableau_breakpoint')) ? $config->get('tableau_breakpoint') : '';
    $tableau_module_path = drupal_get_path('module', 'tableau_vis');;
    $tableau_timeout_bool = !empty($config->get('tableau_timeout_bool')) ? $config->get('tableau_timeout_bool') : '';
    $tableau_break_bool = !empty($config->get('tableau_break_bool')) ? $config->get('tableau_break_bool') : '';
    $tableau_back_bool = !empty($config->get('tableau_back_bool')) ? $config->get('tableau_back_bool') : '';
    $tableau_placeholder_fid = !empty($config->get('tableau_placeholder')) ? $config->get('tableau_placeholder') : '';

    if (!empty($tableau_placeholder_fid[0])) {
      $tableau_placeholder_image = File::load($tableau_placeholder_fid[0]);
      if (!empty($tableau_placeholder_image)) {
        $tableau_placeholder_url = file_create_url($tableau_placeholder_image->getFileUri());
      }
    }

    $settings['tableau_vis'] = [
      'tableau_basepath' => $tableau_module_path,
      'tableau_server' => $tableau_server_url,
      'tableau_target_site' => $tableau_target_site,
      'tableau_bot_blocker' => $tableau_bot_blocker,
      'tableau_timeout' => $tableau_timeout,
      'tableau_breakpoint' => $tableau_breakpoint,
      'tableau_timeout_bool' => $tableau_timeout_bool,
      'tableau_break_bool' => $tableau_break_bool,
      'tableau_back_bool' => $tableau_back_bool,
      'tableau_placeholder_url' => $tableau_placeholder_url,
    ];

    foreach ($items as $delta => $item) {
      $settings['tableau_vis']['tableau_visualization'] = $item->get('visualization')->getValue();
      $element['#attached'] = [
        'library' => [
          'tableau_vis/tableau_vis',
        ],
        'drupalSettings' => [
          $settings,
        ],
      ];

      $element[$delta] = array(
        '#type' => 'markup',
        '#markup' => '<div id="vizContainer"></div>' . $honeypot_link,
      );
    }

    return $element;
  }

}