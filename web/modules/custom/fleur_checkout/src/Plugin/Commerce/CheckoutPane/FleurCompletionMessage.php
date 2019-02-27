<?php

namespace Drupal\fleur_checkout\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the completion message pane.
 *
 * @CommerceCheckoutPane(
 *   id = "fleur_completion_message",
 *   label = @Translation("Fleur - Completion message"),
 *   default_step = "complete",
 * )
 */
class FleurCompletionMessage extends CheckoutPaneBase {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['#theme'] = 'fleur_checkout_completion_message';
    $pane_form['#order_entity'] = $this->order;

    return $pane_form;
  }

}
