<?php

namespace Drupal\fleur_message\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the registration pane.
 *
 * @CommerceCheckoutPane(
 *   id = "fleur_message_to_the_recipient_with_orders_widget",
 *   label = @Translation("Message for order with order's widget"),
 * )
 */
class ClientMessageToTheRecipientWithOrdersWidget extends CheckoutPaneBase {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    // Show the pane only when order has message field.
    return $this->order->hasField('field_message');
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['#order_entity'] = $this->order;

    // Get the EntityFormDisplay of commerce_order
    $entity_form_display = \Drupal::service('entity_type.manager')->getStorage('entity_form_display')->load('commerce_order.default.default');

    // Get the message field widget and add it to the form
    if ($widget = $entity_form_display->getRenderer('field_message')) {
      $items = $this->order->get('field_message');
      $items->filterEmptyItems();
      $pane_form['message'] = $widget->form($items, $pane_form, $form_state);
      $pane_form['message']['#access'] = $items->access('edit');
    }

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue('fleur_message_to_the_recipient_with_orders_widget');
    $message = $values['field_message'];
    if (!empty($message)){
      $this->order->set('field_message', $message);
    }
  }

}
