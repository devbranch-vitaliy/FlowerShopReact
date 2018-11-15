<?php

namespace Drupal\fleur_message\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides the registration pane.
 *
 * @CommerceCheckoutPane(
 *   id = "fleur_order_message",
 *   label = @Translation("Order message"),
 * )
 */
class OrderMessage extends CheckoutPaneBase implements ContainerFactoryPluginInterface {

  /**
   * The form storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $formStorage;

  /**
   * Constructs a new OrderMessage object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface $checkout_flow
   *   The parent checkout flow.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);

    $this->formStorage = $entity_type_manager->getStorage('entity_form_display');
  }

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
    $entity_form_display = $this->formStorage->load('commerce_order.default.default');

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
    $message = $form_state->getValue(['fleur_order_message', 'field_message'], '');
    if (!empty($message)){
      $this->order->set('field_message', $message);
    }
  }

}
