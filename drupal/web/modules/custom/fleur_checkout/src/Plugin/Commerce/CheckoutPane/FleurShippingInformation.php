<?php

namespace Drupal\fleur_checkout\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_shipping\OrderShipmentSummaryInterface;
use Drupal\commerce_shipping\PackerManagerInterface;
use Drupal\commerce_shipping\Plugin\Commerce\CheckoutPane\ShippingInformation;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the shipping information pane.
 *
 * Collects the shipping profile, then the information for each shipment.
 * Assumes that all shipments share the same shipping profile.
 *
 * @CommerceCheckoutPane(
 *   id = "fleur_shipping_information",
 *   label = @Translation("Fleur - Shipping information"),
 *   wrapper_element = "fieldset",
 * )
 */
class FleurShippingInformation extends ShippingInformation {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $store = $this->order->getStore();
    $shipping_profile = $form_state->get('shipping_profile');
    if (!$shipping_profile) {
      $shipping_profile = $this->getShippingProfile();
      $form_state->set('shipping_profile', $shipping_profile);
    }
    $available_countries = [];
    foreach ($store->get('shipping_countries') as $country_item) {
      $available_countries[] = $country_item->value;
    }

    // Prepare the form for ajax.
    // Not using Html::getUniqueId() on the wrapper ID to avoid #2675688.
    $pane_form['#wrapper_id'] = 'fleur-shipping-information-wrapper';
    $pane_form['#prefix'] = '<div id="' . $pane_form['#wrapper_id'] . '">';
    $pane_form['#suffix'] = '</div>';

    $user_input = $form_state->getUserInput();
    $default_delivery_option = 'delivery';
    if (isset($user_input['fleur_shipping_information'])
      && isset($user_input['fleur_shipping_information']['delivery_options'])) {
      $default_delivery_option = $user_input['fleur_shipping_information']['delivery_options'];
    }
//    else {
//      $default_delivery_option = ($this->order->shipments->referencedEntities()) ? 'delivery' : 'pick_up';
//    }

    $pane_form['delivery_options'] = [
      '#type' => 'radios',
      '#recalculate' => TRUE,
      '#default_value' => $default_delivery_option,
      '#options' => [
        'delivery' => $this->t('I want my order to be delivered for me'),
        'pick_up' => $this->t('I want to pick up my order from the shop'),
      ],
      '#ajax' => [
        'callback' => [get_class($this), 'ajaxRefresh'],
        'wrapper' => $pane_form['#wrapper_id'],
        'event' => 'change',
      ],
    ];

    if ($default_delivery_option == 'pick_up') {
      return $pane_form;
    }

    $pane_form['shipping_profile'] = [
      '#type' => 'commerce_profile_select',
      '#default_value' => $shipping_profile,
      '#default_country' => $store->getAddress()->getCountryCode(),
      '#available_countries' => $available_countries,
    ];
    $pane_form['recalculate_shipping'] = [
      '#type' => 'button',
      '#value' => $this->t('Recalculate shipping'),
      '#recalculate' => TRUE,
      '#ajax' => [
        'callback' => [get_class($this), 'ajaxRefresh'],
        'wrapper' => $pane_form['#wrapper_id'],
      ],
      // The calculation process only needs a valid shipping profile.
      '#limit_validation_errors' => [
        array_merge($pane_form['#parents'], ['shipping_profile']),
      ],
    ];
    $pane_form['removed_shipments'] = [
      '#type' => 'value',
      '#value' => [],
    ];
    $pane_form['shipments'] = [
      '#type' => 'container',
    ];

    $shipments = $this->order->shipments->referencedEntities();
    $recalculate_shipping = $form_state->get('recalculate_shipping');
    $force_packing = empty($shipments) && empty($this->configuration['require_shipping_profile']);
    if ($recalculate_shipping || $force_packing) {
      list($shipments, $removed_shipments) = $this->packerManager->packToShipments($this->order, $shipping_profile, $shipments);

      // Store the IDs of removed shipments for submitPaneForm().
      $pane_form['removed_shipments']['#value'] = array_map(function ($shipment) {
        /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
        return $shipment->id();
      }, $removed_shipments);
    }

    $single_shipment = count($shipments) === 1;
    foreach ($shipments as $index => $shipment) {
      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
      $pane_form['shipments'][$index] = [
        '#parents' => array_merge($pane_form['#parents'], ['shipments', $index]),
        '#array_parents' => array_merge($pane_form['#parents'], ['shipments', $index]),
        '#type' => $single_shipment ? 'container' : 'fieldset',
        '#title' => $shipment->getTitle(),
      ];
      $form_display = EntityFormDisplay::collectRenderDisplay($shipment, 'default');
      $form_display->removeComponent('shipping_profile');
      $form_display->removeComponent('title');
      $form_display->buildForm($shipment, $pane_form['shipments'][$index], $form_state);
      $pane_form['shipments'][$index]['#shipment'] = $shipment;
    }

    return $pane_form;
  }

  /**
   * Ajax callback.
   */
  public static function ajaxRefresh(array $form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();
    $parents = array_slice($triggering_element['#parents'], 0, -1);
    return NestedArray::getValue($form, $parents);
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $triggering_element = $form_state->getTriggeringElement();
    $radio_type = isset($triggering_element['#type']) ? $triggering_element['#type'] : '';

    if ($radio_type == 'radio' && ($triggering_element['#value'] == 'pick_up' || $triggering_element['#value'] == 'delivery')) {
      $form_state->setRebuild(TRUE);
      return;
    }

    $delivery_option = $form_state->getValue(['fleur_shipping_information', 'delivery_options'], '');

    if ($delivery_option != 'pick_up') {
      parent::validatePaneForm($pane_form, $form_state, $complete_form);
    }

  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {

    $delivery_option = $form_state->getValue(['fleur_shipping_information', 'delivery_options'], '');

    if ($delivery_option == 'pick_up') {
      $shipment_storage = $this->entityTypeManager->getStorage('commerce_shipment');
      $shipment_storage->delete($this->order->shipments->referencedEntities());

      $this->order->shipments = [];
    }
    else {
      parent::submitPaneForm($pane_form, $form_state, $complete_form);
    }
  }

}
