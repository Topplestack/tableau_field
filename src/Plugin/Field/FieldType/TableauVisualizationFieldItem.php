<?php

namespace Drupal\tableau_vis\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'tableau_vis' field type.
 *
 * Class TableauVisualizationFieldItem
 * @package Drupal\tableau_vis\Plugin\Field\FieldType
 *
 * @FieldType(
 *   id = "tableau_vis",
 *   label = @Translation("Tableau Visualization"),
 *   description = @Translation("A Tableau visualization requiring trusted authentication."),
 *   category = @Translation("Tableau Visualization"),
 *   default_widget = "tableau_vis_default",
 *   default_formatter = "tableau_vis_iframe",
 * )
 *
 */
class TableauVisualizationFieldItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema['columns']['visualization'] = [
      'type' => 'varchar',
      'length' => 255,
      'not null' => FALSE,
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['visualization'] = DataDefinition::create('string')
      ->setLabel(t('Tableau Visualization'))
      ->setRequired(FALSE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('visualization')->getValue();
    return $value === NULL || $value === '';
  }

}