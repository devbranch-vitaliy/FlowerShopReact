<?php

namespace Drupal\fleur_checkout\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_shipping\OrderShipmentSummaryInterface;
use Drupal\commerce_shipping\PackerManagerInterface;
use Drupal\commerce_shipping\Plugin\Commerce\CheckoutPane\ShippingInformation;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityTypeManagerInterface;
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
    $pane_form['#wrapper_id'] = 'shipping-information-wrapper';
    $pane_form['#prefix'] = '<div id="' . $pane_form['#wrapper_id'] . '">';
    $pane_form['#suffix'] = '</div>';

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
    $shipment_indexes = Element::children($pane_form['shipments']);
    $triggering_element = $form_state->getTriggeringElement();
    $recalculate = !empty($triggering_element['#recalculate']);
    $button_type = isset($triggering_element['#button_type']) ? $triggering_element['#button_type'] : '';
    if (!$recalculate && $button_type == 'primary' && empty($shipment_indexes)) {
      // The checkout step was submitted without shipping being calculated.
      // Force the recalculation now and reload the page.
      $recalculate = TRUE;
      drupal_set_message($this->t('Please select a shipping method.'), 'error');
      $form_state->setRebuild(TRUE);
    }

    if ($recalculate) {
      $form_state->set('recalculate_shipping', TRUE);
      // The profile in form state needs to reflect the submitted values, since
      // it will be passed to the packers when the form is rebuilt.
      $form_state->set('shipping_profile', $pane_form['shipping_profile']['#profile']);
    }

    foreach ($shipment_indexes as $index) {
      $shipment = clone $pane_form['shipments'][$index]['#shipment'];
      $form_display = EntityFormDisplay::collectRenderDisplay($shipment, 'default');
      $form_display->removeComponent('shipping_profile');
      $form_display->removeComponent('title');
      $form_display->extractFormValues($shipment, $pane_form['shipments'][$index], $form_state);
      $form_display->validateFormValues($shipment, $pane_form['shipments'][$index], $form_state);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    // Save the modified shipments.
    $shipments = [];
    foreach (Element::children($pane_form['shipments']) as $index) {
      /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
      $shipment = clone $pane_form['shipments'][$index]['#shipment'];
      $form_display = EntityFormDisplay::collectRenderDisplay($shipment, 'default');
      $form_display->removeComponent('shipping_profile');
      $form_display->removeComponent('title');
      $form_display->extractFormValues($shipment, $pane_form['shipments'][$index], $form_state);
      $shipment->setShippingProfile($pane_form['shipping_profile']['#profile']);
      $shipment->save();
      $shipments[] = $shipment;
    }
    $this->order->shipments = $shipments;

    // Delete shipments that are no longer in use.
    $removed_shipment_ids = $pane_form['removed_shipments']['#value'];
    if (!empty($removed_shipment_ids)) {
      $shipment_storage = $this->entityTypeManager->getStorage('commerce_shipment');
      $removed_shipments = $shipment_storage->loadMultiple($removed_shipment_ids);
      $shipment_storage->delete($removed_shipments);
    }
  }

}
