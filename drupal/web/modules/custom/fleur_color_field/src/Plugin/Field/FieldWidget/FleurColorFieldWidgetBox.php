<?php

namespace Drupal\fleur_color_field\Plugin\Field\FieldWidget;

use Drupal\color_field\Plugin\Field\FieldWidget\ColorFieldWidgetBox;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the color_field box custom widget.
 *
 * @FieldWidget(
 *   id = "fleur_color_field_widget_box",
 *   module = "fleur_color_field",
 *   label = @Translation("Color boxes v2"),
 *   field_types = {
 *     "color_field_type"
 *   }
 * )
 */
class FleurColorFieldWidgetBox extends ColorFieldWidgetBox {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['#type'] = 'container';
    $element['opacity'] = [
      '#type' => 'value',
      '#value' => 1,
    ];
    return $element;
  }

}
