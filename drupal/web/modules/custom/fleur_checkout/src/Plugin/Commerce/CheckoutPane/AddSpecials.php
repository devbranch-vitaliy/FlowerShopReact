<?php

namespace Drupal\fleur_checkout\Plugin\Commerce\CheckoutPane;

use CommerceGuys\Intl\Formatter\CurrencyFormatterInterface;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutFlow\CheckoutFlowInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneInterface;
use Drupal\commerce_price\Price;
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
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorage
   */
  protected $productTypeStorage;

  /**
   * The payment options builder.
   *
   * @var \Drupal\commerce\CommerceContentEntityStorage
   */
  protected $productVariationStorage;

  /**
   * The payment options builder.
   *
   * @var \Drupal\commerce\CommerceContentEntityStorage
   */
  protected $productStorage;

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * The currency formatter.
   *
   * @var \CommerceGuys\Intl\Formatter\CurrencyFormatterInterface
   */
  protected $currencyFormatter;

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
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \CommerceGuys\Intl\Formatter\CurrencyFormatterInterface $currency_formatter
   *   The currency formatter.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CheckoutFlowInterface $checkout_flow, EntityTypeManagerInterface $entity_type_manager, CartManagerInterface $cart_manager, CurrencyFormatterInterface $currency_formatter) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $checkout_flow, $entity_type_manager);

    $this->productTypeStorage = $entity_type_manager->getStorage('commerce_product_type');
    $this->productStorage = $entity_type_manager->getStorage('commerce_product');
    $this->productVariationStorage = $entity_type_manager->getStorage('commerce_product_variation');
    $this->cartManager = $cart_manager;
    $this->currencyFormatter = $currency_formatter;
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
      $container->get('entity_type.manager'),
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_price.currency_formatter')
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

    $products_list = [];

    $product_types = $this->productTypeStorage->loadMultiple();
    foreach ($product_types as $product_type) {
      $type = $product_type->id();
      if ($type == 'default') {
        continue;
      };

      if (!empty($this->configuration['product_types'][$type]['selected'])) {
        $products_list[] = $product_type->label();
      }
    };

    if (count($products_list)) {
      $summary .= $this->t('Selected products:');
      $summary .= ' ' . implode(', ', $products_list);
    }
    else {
      $summary .= $this->t('No products selected.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $product_types = $this->productTypeStorage->loadMultiple();

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
        $selected_types[$key] = $value['options'];
      }
    }

    // Create form elements.
    $pane_form['fleur_add_specials'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['add-specials-title'],
      ],
      'text' => [
        '#plain_text' => $this->t('Add specials to your order'),
      ],
    ];

    foreach ($selected_types as $product_type => $options) {
      // Get products id's array.
      $query = $this->productStorage->getQuery();
      $query->condition('type', $product_type)->sort($options['sort_by']);
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
        '#weight' => $options['weight'],
      ];

      $pane_form[$product_type]['title'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['add-specials-product-title'],
        ],
        'text' => [
          '#plain_text' => $options['title'],
        ],
      ];

      if (!empty($options['description'])) {
        $pane_form[$product_type]['description'] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['add-specials-product-description'],
          ],
          'text' => [
            '#plain_text' => $options['description'],
          ],
        ];
      }

      // Get variations from order.
      $order_variations = [];
      foreach ($this->order->getItems() as $orderItem) {
        $order_variations[] = $orderItem->getPurchasedEntityId();
      }

      // Add products elements.
      $default_values = [];
      $products_elements = [];
      $products_elements_options = [];

      // If radios type then add None element.
      if ($options['choose_type'] == 'radios') {
        $products_elements['none'] = $this->t('None');
        $products_elements_options['none'] = ['class' => 'listbox-level-1', 'disabled' => FALSE];
      }

      $currency = $this->order->getStore()->getDefaultCurrency()->getSymbol();
      /** @var \Drupal\commerce_product\Entity\ProductInterface[] $products */
      $products = $this->productStorage->loadMultiple($products_ids);
      foreach ($products as $product) {
        $default_variation = $product->getDefaultVariation();
        $default_price = $default_variation->getPrice();
        $variations = $product->getVariations();

        // Get products array.
        if (count($variations) > 1) {
          // For parent use a label because id can be the same as variation id.
          $label = $product->label();

          $products_elements[$label] = $this->getProductLabel($product->getTitle(), $default_price);
          $products_elements_options[$label] = ['class' => 'listbox-level-1', 'disabled' => TRUE];

          foreach ($variations as $variation) {
            $products_elements[$variation->id()] = ($default_price->equals($variation->getPrice())) ? $variation->getTitle() : $this->getProductLabel($variation->getTitle(), $variation->getPrice());
            $products_elements_options[$variation->id()] = ['class' => 'listbox-level-2', 'disabled' => FALSE];

            if (in_array($variation->id(), $order_variations)) {
              $default_values[] = $variation->id();
            }
          }
        }
        else {
          $default_variation = $product->getDefaultVariation();
          $products_elements[$default_variation->id()] = $this->getProductLabel($product->getTitle(), $default_price);
          $products_elements_options[$default_variation->id()] = ['class' => 'listbox-level-1', 'disabled' => FALSE];

          if (in_array($default_variation->id(), $order_variations)) {
            $default_values[] = $default_variation->id();
          }
        };
      }

      if ($options['choose_type'] == 'radios') {
        $default_values = (count($default_values)) ? end($default_values) : 'none';
      };

      // Create radios/checkboxes.
      $pane_form[$product_type]['products'] = [
        '#type' => $options['choose_type'],
        '#default_value' => $default_values,
        '#multiple' => TRUE,
        '#options' => $products_elements,
      ];

      // Create wrapper to elements.
      foreach ($products_elements_options as $element_key => $element_option) {
        $pane_form[$product_type]['products'][$element_key] = [
          '#prefix' => '<div class="' . $element_option['class'] . '">',
          '#suffix' => '</div>',
          '#disabled' => $element_option['disabled'],
        ];
      }
    }

    // Attached css library.
    $pane_form['#attached']['library'][] = 'fleur_checkout/add_specials';

    return $pane_form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitPaneForm(array &$pane_form, FormStateInterface $form_state, array &$complete_form) {
    $message = $form_state->getValue('fleur_add_specials');
    $variations = [];
    $product_types = [];
    foreach ($message as $product_type => $options) {
      if (empty($this->productTypeStorage->load($product_type))) {
        continue;
      }

      $product_types[] = $product_type;

      if (is_array($options['products'])) {
        foreach ($options['products'] as $id => $checked) {
          if (!empty($checked)) {
            $variations[$id] = $id;
          }
        }
      }
      else {
        $variations[$options['products']] = $options['products'];
      }
    }

    /** @var \Drupal\commerce_bulk\Entity\BulkProductVariation $order_variation */
    foreach ($this->order->getItems() as $orderItem) {
      $order_variation = $orderItem->getPurchasedEntity();
      $order_variation_id = $order_variation->id();

      if (in_array($order_variation_id, $variations)) {
        unset($variations[$order_variation_id]);
      }
      else {
        $product_type = $order_variation->getProduct()->get('type')->getString();
        if (in_array($product_type, $product_types)) {
          $this->order->removeItem($orderItem);
          $orderItem->delete();
        }
      }
    }

    foreach ($variations as $variation_id) {
      if ($variation_id == 'none') {
        continue;
      }

      $purchased_entity = $this->productVariationStorage->load($variation_id);
      if (!$purchased_entity || !$purchased_entity instanceof PurchasableEntityInterface) {
        \Drupal::logger('fleur_checkout')->error(t("Not all items have been successfully copied"));
        continue;
      }
      $new_order_item = $this->entityTypeManager->getStorage('commerce_order_item')->createFromPurchasableEntity($purchased_entity, [
        'quantity' => 1,
      ]);
      $this->cartManager->addOrderItem($this->order, $new_order_item);

    }
  }

  /**
   * Getting product Label by parameters.
   */
  protected function getProductLabel($title, Price $price) {
    return $this->t('@title - @price', [
      '@title' => $title,
      '@price' => $this->currencyFormatter->format($price->getNumber(), $price->getCurrencyCode()),
    ]);

  }

}
