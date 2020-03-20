<?php

namespace Drupal\tableau_vis\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'tableau_vis_iframe' formatter.
 *
 * Class TableauVisualizationIframeFieldFormatter
 * @package Drupal\tableau_vis\Plugin\Field\FieldFormatter
 *
 * @FieldFormatter(
 *   id = "tableau_vis_iframe",
 *   label = @Translation("Tableau Iframe"),
 *   field_types = {
 *     "tableau_vis"
 *   }
 * )
 *
 */
class TableauVisualizationIframeFieldFormatter extends FormatterBase {

  /**
   * @inheritDoc
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $config = \Drupal::service('config.factory')->getEditable('tableau_visualization.settings');
    $element = array();
    $tableau_server_url =
      !empty($config->get('tableau_server')) ? $config->get('tableau_server') : '';
    $tableau_target_site =
      !empty($config->get('tableau_target_site')) ? $config->get('tableau_target_site') : '';
    foreach ($items as $delta => $item) {
      $element[$delta] = array(
        '#type' => 'markup',
        '#markup' => '<div class="fluid-width-map-wrapper"><iframe src="' . $tableau_server_url . tableau_viz_get_trusted_ticket() . '/t/' . $tableau_target_site . '/views/' . $item->get('visualization')->getValue() . '?:embed=yes&:showVizHome=no&:toolbar=no"></iframe></div>',
        '#allowed_tags' => ['iframe', 'div'],
      );
    }

    return $element;
  }

}