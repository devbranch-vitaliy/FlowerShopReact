<?php

namespace Drupal\fleur_checkout\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_payment\Plugin\Commerce\CheckoutPane\PaymentProcess;

/**
 * Provides Fleur specific payment process pane.
 *
 * @CommerceCheckoutPane(
 *   id = "fleur_payment_process",
 *   label = @Translation("Fleur - Payment process"),
 *   default_step = "payment",
 *   wrapper_element = "container",
 * )
 */
class FleurPaymentProcess extends PaymentProcess {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    if ($this->order->isPaid() || $this->order->getTotalPrice()->isZero()) {
      // No payment is needed if the order is free or has already been paid.
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function getErrorStepId() {
    // Default to the step that contains the PaymentInformation pane.
    $step_id = $this->checkoutFlow->getPane('fleur_payment_process')->getStepId();
    if ($step_id == '_disabled') {
      throw new \RuntimeException('Cannot get the step ID for the fleur_payment_process pane. The pane is disabled.');
    }

    return $step_id;
  }

}
