<?php

namespace Drupal\fleur_checkout\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\OrderSummary;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Order summary pane.
 *
 * @CommerceCheckoutPane(
 *   id = "fleur_order_summary",
 *   label = @Translation("Fleur - Order summary"),
 *   admin_label = @Translation("Fleur - Order summary"),
 *   default_step = "_sidebar",
 *   wrapper_element = "container",
 * )
 */
class FleurOrderSummary extends OrderSummary {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    if ($this->configuration['view']) {
      $pane_form['summary'] = [
        '#type' => 'view',
        '#name' => $this->configuration['view'],
        '#display_id' => 'default',
        '#arguments' => [$this->order->id()],
        '#embed' => TRUE,
      ];
    }
    else {

      $order_items_by_type = ['default', 'containers', 'specials'];
      $type_names = [
        'containers' => $this->t('Containers:'),
        'specials' => $this->t('Specials:'),
      ];

      /** @var \Drupal\commerce_order\Entity\OrderItemInterface $item */
      foreach ($this->order->getItems() as $item) {
        $type = $item->getPurchasedEntity()->get('type')->first()->getString();

        if ($type == 'default') {
          $order_items_by_type['default'][] = $item;
        }

        if ($type == 'containers') {
          $order_items_by_type['containers'][] = $item;
        }

        if ($type == 'extras_selection') {
          $order_items_by_type['specials'][] = $item;
        }
      }

      $shipments = [];
      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
      foreach ($this->order->get('shipments')->referencedEntities() as $shipment) {
        $shipments[] = [
          'title' => $shipment->getShippingMethod()->getName(),
          'price' => $shipment->getAmount(),
        ];
      }

      $pane_form['summary'] = [
        '#theme' => 'fleur_checkout_order_summary',
        '#order_entity' => $this->order,
        '#order_items_by_type' => $order_items_by_type,
        '#shipments' => $shipments,
        '#type_names' => $type_names,
        '#checkout_step' => $complete_form['#step_id'],
      ];
    }

    return $pane_form;
  }

}
