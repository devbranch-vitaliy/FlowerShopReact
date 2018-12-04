<?php

namespace Drupal\fleur_checkout\Plugin\Commerce\CheckoutPane;

use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the confirmation checkbox pane.
 *
 * @CommerceCheckoutPane(
 *   id = "fleur_add_specials",
 *   label = @Translation("Fleur - Add specials"),
 *   admin_label = @Translation("Fleur - Add specials"),
 * )
 */
class AddSpecials extends CheckoutPaneBase implements CheckoutPaneInterface {

  /**
   * The payment options builder.
   *
   * @var \Drupal\commerce_product\Entity\ProductTypeInterface
   */
  protected $productTypeManager;

  /**
   * The payment options builder.
   *
   * @var \Drupal\commerce_product\Entity\ProductInterface
   */
  protected $productManager;

  /**
   * AddSpecials constructor.
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

    $this->productTypeManager = $entity_type_manager->getStorage('commerce_product_type');
    $this->productManager = $entity_type_manager->getStorage('commerce_product');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow = NULL) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $checkout_flow,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function isVisible() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationSummary() {
    $summary = parent::buildConfigurationSummary();

    $products_selected = FALSE;

    $product_types = $this->productTypeManager->loadMultiple();
    foreach ($product_types as $product_type) {
      $type = $product_type->id();
      if ($type == 'default') {
        continue;
      };

      if (!empty($this->configuration['product_types'][$type]) && $this->configuration['product_types'][$type]['selected'] == '1') {

        if (!$products_selected) {
          $products_selected = TRUE;
          $summary .= $this->t('Chosen products:');
          $summary .= '<ul>';
        }

        $summary .= '<li>' . $product_type->label() . '</li>';
      }
    };

    if (!$products_selected) {
      $summary .= $this->t('No products selected.');
    }
    else {
      $summary .= '</ul>';
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $product_types = $this->productTypeManager->loadMultiple();

    $form['fleur_add_specials'] = [
      '#type' => 'label',
      '#title' => t('Choose a product type'),
    ];

    foreach ($product_types as $product_type) {
      $type = $product_type->id();
      $label = $product_type->label();

      if ($type == 'default') {
        continue;
      };

      $form['product_types'][$type]['selected'] = [
        '#type' => 'checkbox',
        '#title' => $label,
        '#default_value' => !empty($this->configuration['product_types'][$type]['selected']) ? $this->configuration['product_types'][$type]['selected'] : '0',
      ];

      $form['product_types'][$type]['options'] = [
        '#type' => 'details',
        '#title' => $this->t("Options of @label:", ['@label' => $label]),
        '#open' => TRUE,
        '#states' => [
          'visible' => [
            ':input[name="configuration[panes][fleur_add_specials][configuration][product_types][' . $type . '][selected]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $opt_conf = $this->configuration['product_types'][$type]['options'];

      $form['product_types'][$type]['options']['weight'] = [
        '#type' => 'number',
        '#title' => $this->t('Weight'),
        '#default_value' => !empty($opt_conf['weight']) ? $opt_conf['weight'] : 0,
      ];

      $form['product_types'][$type]['options']['title'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Title'),
        '#default_value' => !empty($opt_conf['title']) ? $opt_conf['title'] : $product_type->label(),
        '#required' => TRUE,
      ];

      $form['product_types'][$type]['options']['description'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Description'),
        '#default_value' => !empty($opt_conf['description']) ? $opt_conf['description'] : '',
      ];

      $form['product_types'][$type]['options']['choose_type'] = [
        '#type' => 'radios',
        '#title' => $this->t('Choose type'),
        '#default_value' => !empty($opt_conf['choose_type']) ? $opt_conf['choose_type'] : 'radios',
        '#options' => [
          'radios' => $this->t('Radios'),
          'checkboxes' => $this->t('Checkboxes'),
        ],
        '#states' => [
          'required' => [
            ':input[name="configuration[panes][fleur_add_specials][configuration][product_types][' . $type . '][selected]"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form['product_types'][$type]['options']['sort_by'] = [
        '#type' => 'radios',
        '#title' => $this->t('Sort by'),
        '#default_value' => !empty($opt_conf['sort_by']) ? $opt_conf['sort_by'] : 'product_id',
        '#options' => [
          'product_id' => $this->t('ID'),
          'title' => $this->t('Title'),
        ],
        '#states' => [
          'required' => [
            ':input[name="configuration[panes][fleur_add_specials][configuration][product_types][' . $type . '][selected]"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    if (!$form_state->getErrors()) {
      $values = $form_state->getValue($form['#parents']);

      if (!empty($values['product_types'])) {
        $this->configuration['product_types'] = $values['product_types'];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    // Don't show without configure.
    if (empty($this->configuration['product_types'])) {
      return $pane_form;
    }

    $product_types = $this->configuration['product_types'];
    $selected_types = [];
    foreach ($product_types as $key => $value) {
      if ($value['selected'] == '1') {
        $selected_types[intval($value['options']['weight'])] = $key;
      }
    }

    // Don't show without selected product types.
    if (!count($selected_types)) {
      return $pane_form;
    }

    // Sort by weight.
    ksort($selected_types);

    // Create form elements.
    $pane_form['fleur_add_specials'] = [
      '#type' => 'html_tag',
      '#value' => t('Add specials to your order'),
      '#tag' => 'div',
      '#attributes' => [
        'class' => ['add-specials-title'],
      ],
    ];

    foreach ($selected_types as $product_type) {
      // Get products id's array.
      $query = $this->productManager->getQuery();
      $query->condition('type', $product_type)->sort($product_types[$product_type]['options']['sort_by']);
      $products_ids = $query->execute();

      // Don't show when product list is empty.
      if (!count($products_ids)) {
        continue;
      }

      $pane_form[$product_type] = [
        '#type' => 'container',
        '#tree' => TRUE,
        '#attributes' => [
          'class' => ['product-container', $product_type],
        ],
      ];

      $pane_form[$product_type]['title'] = [
        '#type' => 'html_tag',
        '#value' => $product_types[$product_type]['options']['title'],
        '#tag' => 'div',
        '#attributes' => [
          'class' => ['add-specials-product-title'],
        ],
      ];

      if (!empty($product_types[$product_type]['options']['description'])) {
        $pane_form[$product_type]['description'] = [
          '#type' => 'html_tag',
          '#value' => $product_types[$product_type]['options']['description'],
          '#tag' => 'div',
          '#attributes' => [
            'class' => ['add-specials-product-description'],
          ],
        ];
      }

      // Add products elements.
      $products_elements = [];

      // If radios type then add None element.
      if ($product_types[$product_type]['options']['choose_type'] == 'radios') {
        $products_elements['none'] = 'None';
      }

      $currency = $this->order->getStore()->getDefaultCurrency()->getSymbol();
      $products = $this->productManager->loadMultiple($products_ids);
      foreach ($products as $product) {
        /** @var \Drupal\commerce_product\Entity\ProductVariationInterface[] $variations */
        /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $variation */
        $price = 0;
        $prices = [];
        $variations = $product->getVariations();
        foreach ($variations as $variation) {
          $prices[] = floatval($variation->getPrice()->getNumber());
        }

        // Get average price.
        if (count($prices)) {
          $price = array_sum($prices) / count($prices);
        }

        // Get products array.
        if (count($variations) > 1) {
          $label = $product->label();
          $products_elements[$label] = [];
          foreach ($variations as $variation) {
            $products_elements[$label][$variation->id()] = $this->t('@title - @currency@price', [
              '@title' => $variation->getTitle(),
              '@currency' => $currency,
              '@price' => number_format($price, 2),
            ]);
          }
        }
        else {
          $products_elements[$variations[0]->id()] = $this->t('@title - @currency@price', [
            '@title' => $product->getTitle(),
            '@currency' => $currency,
            '@price' => number_format($price, 2),
          ]);
        };
      }

      $pane_form[$product_type]['products'] = [
        '#type' => $product_types[$product_type]['options']['choose_type'],
        '#default_value' => !empty($products_elements['none']) ? $this->t('none') : '',
        '#multiple' => TRUE,
        '#tree' => TRUE,
        '#options' => $products_elements,
      ];

    }

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $values = $form_state->getValue($pane_form['#parents']);
  }

}
