<?php

namespace Drupal\tableau_vis\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'tableau_vis_default' widget.
 *
 * Class TableauVisualizationDefaultWidget
 * @package Drupal\tableau_vis\Plugin\Field\FieldWidget
 *
 * @FieldWidget(
 *   id = "tableau_vis_default",
 *   label = @Translation("Tableau Field"),
 *   field_types = {
 *     "tableau_vis"
 *   }
 * )
 *
 */
class TableauVisualizationDefaultWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = !empty($items[$delta]->get('visualization')->getValue()) ?
      $items[$delta]->get('visualization')->getValue() : '';

    $widget = $element;
    $widget['#delta'] = $delta;

    $widget += array(
      '#type' => 'textfield',
      '#default_value' => $value,
      '#size' => 60,
      '#maxlength' => 255,
    );

    $element['visualization'] = $widget;

    return $element;
  }

}