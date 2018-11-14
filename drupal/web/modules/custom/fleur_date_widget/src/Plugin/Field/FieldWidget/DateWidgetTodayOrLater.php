<?php

namespace Drupal\fleur_date_widget\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\datetime\Plugin\Field\FieldWidget\DateTimeDefaultWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'InternationalPhoneDefaultWidget' widget.
 *
 * @FieldWidget(
 *   id = "date_today_or_later",
 *   label = @Translation("Date today or later"),
 *   field_types = {
 *     "datetime"
 *   }
 * )
 */
class DateWidgetTodayOrLater extends DateTimeDefaultWidget {

  /**
   * Define the form for the field type.
   *
   * Inside this method we can define the form used to edit the field type.
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $formState) {
    $element = parent::formElement($items, $delta, $element, $form, $formState);
    $element['#element_validate'] = [
      [$this, 'validate'],
    ];
    return $element;
  }

  /**
   * Validate the date field.
   */
  public function validate($element, FormStateInterface $form_state) {
    $value = $element['value']['#value'];
    $date_obj = $value['object']->setTime(17, 00, 00);
    $current_date_obj = new DrupalDateTime();

    if ($current_date_obj->diff($date_obj)->invert) {
      $form_state->setError($element, $this->t("It is not possible to process orders in past."));
    }
  }

}
