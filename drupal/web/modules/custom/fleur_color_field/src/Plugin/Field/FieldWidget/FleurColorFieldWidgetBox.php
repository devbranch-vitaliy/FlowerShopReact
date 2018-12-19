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
 *   label = @Translation("Palette de Fleur - Color boxes"),
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

    // If product has Colors field we get list of colors from that field.
    $storage = $form_state->getStorage();
    if (isset($storage['product']) && $storage['product']->hasField('field_colors')) {
      foreach ($storage['product']->get('field_colors') as $field_item) {
        $colors[] = $field_item->getValue()['value'];
      }
      $element['color']['#default_value'] = reset($colors);
      $element['#attached']['drupalSettings']['color_field']['color_field_widget_box']['settings'][$element['#uid']]['palette'] = $colors;
    }

    return $element;
  }

}
