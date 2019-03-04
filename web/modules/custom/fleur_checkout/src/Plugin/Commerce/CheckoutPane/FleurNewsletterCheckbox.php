<?php

namespace Drupal\fleur_checkout\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the confirmation checkbox pane.
 *
 * @CommerceCheckoutPane(
 *   id = "fleur_commerce_checkout_newsletter_checkbox",
 *   label = @Translation("Fleur - Newsletter checkbox"),
 *   admin_label = @Translation("Fleur - Newsletter checkbox"),
 * )
 */
class FleurNewsletterCheckbox extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $parent = parent::defaultConfiguration();
    $default = [
      'mailchimp_list' => '',
      'checkbox_title' => $this->t('I would like to receive the latest news, deals and special reminders'),
      'require_double_opt_in' => FALSE,
    ];
    return $parent + $default;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
    $summary = parent::buildConfigurationSummary();
    $summary .= '<br>';

    if (!empty($this->configuration['mailchimp_list'])
      && $list = mailchimp_get_list($this->configuration['mailchimp_list'])) {
      $summary .= $this->t('Mailchimp list: @title', ['@title' => $list->name]);
      $summary .= '<br>';
    }

    $summary .= $this->t('Require subscribers to Double Opt-in: @value', [
      '@value' => !empty($this->configuration['require_double_opt_in']) ? $this->t('Yes') : $this->t('No'),
    ]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['mailchimp_list'] = [
      '#type' => 'select',
      '#title' => $this->t('Mailchimp list'),
      '#required' => TRUE,
      '#description' => $this->t('Select the Mailchimp list.'),
      '#default_value' => !empty($this->configuration['mailchimp_list']) ? $this->configuration['mailchimp_list'] : '',
      '#options' => $this->getMailchimpListsOptions(),
    ];

    $form['checkbox_title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Checkbox title'),
      '#required' => TRUE,
      '#default_value' => !empty($this->configuration['checkbox_title']) ? $this->configuration['checkbox_title'] : $this->t('I would like to receive the latest news, deals and special reminders'),
    ];

    $form['require_double_opt_in'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Double Opt-in'),
      '#description' => $this->t('Require subscribers to Double Opt-in.'),
      '#default_value' => !empty($this->configuration['require_double_opt_in']),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);
      $this->configuration['mailchimp_list'] = $values['mailchimp_list'];
      $this->configuration['checkbox_title'] = $values['checkbox_title'];
      $this->configuration['require_double_opt_in'] = $values['require_double_opt_in'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    if (!empty($this->configuration['mailchimp_list'])) {
      $pane_form['newsletter_checkbox'] = [
        '#type' => 'checkbox',
        '#default_value' => boolval($this->order->getData('mailchimp_list', NULL)),
        '#title' => $this->configuration['checkbox_title'],
      ];
    }

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
    $value = !empty($values['newsletter_checkbox']);
    $this->order->setData('mailchimp_list', $value ? $this->configuration['mailchimp_list'] : NULL);
    $this->order->setData('mailchimp_double_opt_in', $value ? $this->configuration['require_double_opt_in'] : NULL);
  }

  /**
   * Returns the list of Mailchimp lists.
   *
   * @return array
   *   The list of Mailchimp lists names, keyed by ID.
   */
  protected function getMailchimpListsOptions() {
    $lists = [];
    foreach (mailchimp_get_lists() as $list) {
      $lists[$list->id] = $list->name;
    }
    return $lists;
  }

}
