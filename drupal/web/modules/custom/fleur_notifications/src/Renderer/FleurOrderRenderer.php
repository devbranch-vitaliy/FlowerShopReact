<?php

namespace Drupal\fleur_notifications\Renderer;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Renderer\OrderRenderer;

/**
 * Provides a custom entity_print renderer for orders.
 *
 * Uses the commerce_order_receipt template for the document contents.
 */
class FleurOrderRenderer extends OrderRenderer {

  /**
   * Builds a print ready render array for a single order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order entity.
   *
   * @return array
   *   The render array.
   */
  protected function renderSingle(OrderInterface $order) {
    $build = [
      '#theme' => 'fleur_order_receipt',
      '#order_entity' => $order,
      '#totals' => $this->orderTotalSummary->buildTotals($order),
    ];
    if ($billing_profile = $order->getBillingProfile()) {
      $build['#billing_information'] = $this->profileViewBuilder->view($billing_profile);
    }

    return $build;
  }

}
