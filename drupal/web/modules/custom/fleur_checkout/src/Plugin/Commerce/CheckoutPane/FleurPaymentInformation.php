<?php

namespace Drupal\fleur_checkout\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_payment\PaymentOption;
use Drupal\commerce_payment\PaymentOptionsBuilderInterface;
use Drupal\commerce_payment\Plugin\Commerce\CheckoutPane\PaymentInformation;
use Drupal\commerce_payment\Plugin\Commerce\PaymentGateway\SupportsStoredPaymentMethodsInterface;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the payment information pane.
 *
 * Disabling this pane will automatically disable the payment process pane,
 * since they are always used together. Developers subclassing this pane
 * should use hook_commerce_checkout_pane_info_alter(array &$panes) to
 * point $panes['payment_information']['class'] to the new child class.
 *
 * @CommerceCheckoutPane(
 *   id = "fleur_payment_information",
 *   label = @Translation("Fleur - Payment information"),
 *   default_step = "order_information",
 *   wrapper_element = "fieldset",
 * )
 */
class FleurPaymentInformation extends PaymentInformation {

  /**
   * Add markup element with wrapper.
   *
   * @param string $markup_text
   *   A text of markup.
   * @param array $classes
   *   Array with classes.
   *
   * @return array
   *   The form element.
   */
  private function addMarkup($markup_text, array $classes) {

    if (empty($markup_text)) {
      return [];
    }

    return [
      '#type' => 'container',
      'label' => [
        '#type' => 'markup',
        '#markup' => $markup_text,
      ],
      '#attributes' => [
        'class' => $classes,
      ],
    ];
  }

  /**
   * Add container element.
   *
   * @param array $classes
   *   Array with classes.
   *
   * @return array
   *   The form element.
   */
  private function addContainer(array $classes) {
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => $classes,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneSummary() {

    $summary = [];
    if ($this->isVisible()) {

      $billing_profile = $this->order->getBillingProfile();
      if (!$billing_profile) {
        return $summary;
      }

      $summary['payment'] = ['#type' => 'container'];
      $summary['payment']['panel_title'] = $this->addMarkup($this->t('Your information:'), ['panel-title']);

      $address = $billing_profile->get('address')->first()->getValue();

      $summary['payment']['client'] = $this->addContainer(['client-information information-group form-group']);
      $summary['payment']['client']['title'] = $this->addMarkup($this->t('Client:'), ['information-title']);
      $summary['payment']['client']['name'] = $this->addMarkup($address['given_name'] . ' ' . $address['family_name'], ['information-field']);
      $summary['payment']['client']['telephone'] = $this->addMarkup($billing_profile->get('field_telephone')->getString(), ['information-field']);
      $summary['payment']['client']['email'] = $this->addMarkup($billing_profile->get('field_email')->getString(), ['information-field']);

      $summary['payment']['address'] = $this->addContainer(['address-information information-group form-group']);
      $summary['payment']['address']['title'] = $this->addMarkup($this->t('Address:'), ['information-title']);
      $summary['payment']['address']['organization'] = $this->addMarkup($address['organization'], ['information-field']);
      $summary['payment']['address']['address_line1'] = $this->addMarkup($address['address_line1'], ['information-field']);
      $summary['payment']['address']['address_line2'] = $this->addMarkup($address['address_line2'], ['information-field']);
      $summary['payment']['address']['city'] = $this->addMarkup($address['locality'] . ', ' . $address['postal_code'], ['information-field']);
      $summary['payment']['address']['dependent_locality'] = $this->addMarkup($address['dependent_locality'], ['information-field']);
      $summary['payment']['address']['administrative_area'] = $this->addMarkup($address['administrative_area'], ['information-field']);
      $summary['payment']['address']['additional_name'] = $this->addMarkup($address['additional_name'], ['information-field']);
      $summary['payment']['address']['country'] = $this->addMarkup(\Drupal::service('address.country_repository')->get($address['country_code'])->getName(), ['information-field']);
    }

    return $summary;
  }

}
