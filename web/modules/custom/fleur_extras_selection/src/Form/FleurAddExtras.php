<?php

namespace Drupal\fleur_extras_selection\Form;

use CommerceGuys\Intl\Formatter\CurrencyFormatterInterface;
use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_price\Price;
use Drupal\Component\Utility\Html;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Building add extras form.
 */
class FleurAddExtras extends FormBase {

  /**
   * The payment options builder.
   *
   * @var \Drupal\commerce\CommerceContentEntityStorage
   */
  protected $productVariationStorage;

  /**
   * The payment options builder.
   *
   * @var \Drupal\commerce_order\OrderItemStorageInterface
   */
  protected $orderItemStorage;

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
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The currency formatter.
   *
   * @var \CommerceGuys\Intl\Formatter\CurrencyFormatterInterface
   */
  protected $currencyFormatter;

  /**
   * The form storage.
   *
   * @var \Drupal\Core\Config\Entity\ConfigEntityStorage
   */
  protected $viewStorage;

  /**
   * The language manager interface.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $language;

  /**
   * The cart of user.
   *
   * @var \Drupal\commerce_order\Entity\OrderInterface
   */
  protected $cart;

  /**
   * The product types array.
   *
   * @var array
   */
  protected $productTypes;

  /**
   * Constructs of the add extras form.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \CommerceGuys\Intl\Formatter\CurrencyFormatterInterface $currency_formatter
   *   The currency formatter.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language
   *   The language manager interface.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, CartProviderInterface $cart_provider, CartManagerInterface $cart_manager, CurrencyFormatterInterface $currency_formatter, LanguageManagerInterface $language) {
    $this->productStorage = $entity_type_manager->getStorage('commerce_product');
    $this->productVariationStorage = $entity_type_manager->getStorage('commerce_product_variation');
    $this->orderItemStorage = $entity_type_manager->getStorage('commerce_order_item');
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
    $this->currencyFormatter = $currency_formatter;
    $this->viewStorage = $entity_type_manager->getStorage('entity_view_display');
    $this->language = $language;

    // Set static order types.
    $this->productTypes = [
      'containers' => [
        'weight' => 0,
        'title' => $this->t('Add containers:'),
        'description' => $this->t('Containers will be the choice from our store’s extensive range, selected at our discretion and suitable for the flowers ordered.'),
        'choose_type' => 'radios',
      ],
      'extras_selection' => [
        'weight' => 1,
        'title' => $this->t('Add specials:'),
        'choose_type' => 'checkboxes',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_price.currency_formatter'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'fleur_add_extras';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    // To find the cart of user.
    $carts = $this->cartProvider->getCarts();
    $carts = array_filter($carts, function ($cart) {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $cart */
      return $cart->hasItems();
    });
    if (empty($carts)) {
      throw new NotFoundHttpException();
    }

    // Current cart.
    $this->cart = reset($carts);

    // Create top submit buttons.
    $this->buildTopSubmitButtons($form);

    foreach ($this->productTypes as $product_type => $options) {

      // Get products id's array.
      $query = $this->productStorage->getQuery();
      $query->condition('type', $product_type)->sort('product_id');
      $query->condition('status', 1);
      $products_ids = $query->execute();

      // Don't show when product list is empty.
      if (empty($products_ids)) {
        continue;
      }

      /** @var \Drupal\Core\Entity\Entity\EntityViewDisplay $entity_view_display */
      $entity_view_display = $this->viewStorage->load('commerce_product.' . $product_type . '.' . 'default');

      // Create product form head.
      $this->buildProductHead($form, $product_type, $options);

      // Get variations from cart.
      $cart_variations = [];
      foreach ($this->cart->getItems() as $cart_item) {
        $cart_variations[] = $cart_item->getPurchasedEntityId();
      }

      // Add products elements.
      $default_values = [];
      $products_elements = [];
      $products_elements_img = [];
      $products_elements_weight = [];

      /** @var \Drupal\commerce_product\Entity\ProductInterface[] $products */
      $products = $this->productStorage->loadMultiple($products_ids);
      foreach ($products as $product) {
        $default_variation = $product->getDefaultVariation();
        if (!$default_variation) {
          // There's no default variation, so just skip.
          continue;
        }
        $default_price = $default_variation->getPrice();
        $variations = $product->getVariations();

        // Get product elements array.
        // If several variations.
        if (count($variations) > 1) {
          // For parent use a label because id can be the same as variation id.
          $element_id = "product_{$product->id()}";
          $products_elements_weight[$element_id] = (empty($product->get('field_weight')->getString())) ? "0" : $product->get('field_weight')->getString();
          $products_elements[$element_id]['title'] = $this->getProductLabel($product->getTitle(), $default_price);

          foreach ($variations as $variation) {
            $products_elements[$element_id]['variations'][$variation->id()] = ($default_price->equals($variation->getPrice())) ? $variation->getTitle() : $this->getProductLabel($variation->getTitle(), $variation->getPrice());

            if (in_array($variation->id(), $cart_variations)) {
              $default_values[] = $variation->id();
            }
          }
        }
        // If single variation.
        else {
          $default_variation = $product->getDefaultVariation();
          if (stripos($product->getTitle(), 'None') === 0) {
            $element_id = 'none';
            $products_elements[$element_id] = $product->getTitle();
          }
          else {
            $element_id = $default_variation->id();
            $products_elements[$element_id] = $this->getProductLabel($product->getTitle(), $default_price);
          }
          $products_elements_weight[$element_id] = (empty($product->get('field_weight')->getString())) ? "0" : $product->get('field_weight')->getString();
          if (in_array($element_id, $cart_variations)) {
            $default_values[] = $default_variation->id();
          }
        };

        // Image formatter.
        /** @var \Drupal\image\Plugin\Field\FieldFormatter\ImageFormatter $formatter */
        if ($formatter = $entity_view_display->getRenderer('field_image')) {
          $files = $product->get('field_image')->filterEmptyItems();
          // For each item set the 'loaded' flag.
          foreach ($files as $item) {
            $item->_loaded = TRUE;
            $item->_attributes = ['data-variation-id' => $element_id];
          }
          $products_elements_img[$element_id] = $formatter->viewElements($files, $this->language->getCurrentLanguage());
        }
      }

      // Create images containers.
      $this->buildImages($form, $product_type, $products_elements_img, $products_elements_weight);

      // Create radios/checkboxes elements.
      $this->buildRadiosCheckboxes($form, $product_type, $options['choose_type'], $products_elements, $default_values, $products_elements_weight);

    }

    // Create bottom submit buttons.
    $this->buildSubmitButtons($form);

    // Attached css library.
    $form['#attached']['library'][] = 'fleur_extras_selection/add_extras';

    return $form;
  }

  /**
   * Build product form head.
   *
   * @param array $form
   *   The form from build function.
   * @param string $product_type
   *   The product type.
   * @param array $options
   *   The option of containers.
   */
  protected function buildProductHead(array &$form, $product_type, array $options) {
    $form['add_extras_selection'][$product_type] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['product-container', Html::cleanCssIdentifier('product-type-container-' . $product_type)],
      ],
      '#weight' => $options['weight'],
    ];

    $form['add_extras_selection'][$product_type]['title'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['add-specials-product-title'],
      ],
      'text' => [
        '#plain_text' => $options['title'],
      ],
    ];

    if (!empty($options['description'])) {
      $form['add_extras_selection'][$product_type]['description'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['add-specials-product-description'],
        ],
        'text' => [
          '#plain_text' => $options['description'],
        ],
      ];
    }
  }

  /**
   * Build images of the variations.
   *
   * @param array $form
   *   The form from build function.
   * @param string $product_type
   *   The product type.
   * @param array $products_elements_img
   *   The images to render.
   * @param array $weights
   *   Weights of the elements.
   */
  protected function buildImages(array &$form, $product_type, array $products_elements_img, array $weights) {
    $form['add_extras_selection'][$product_type]['variations_img'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['extras_img'],
      ],
    ];

    foreach ($products_elements_img as $id => $value) {
      $form['add_extras_selection'][$product_type]['variations_img']["extras_img_wrapper_{$id}"] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['extras_img_wrapper'],
          'data-variation-id' => $id,
        ],
        '#weight' => $weights[$id],
      ];

      $form['add_extras_selection'][$product_type]['variations_img']["extras_img_wrapper_{$id}"][$id] = $value;
    }
  }

  /**
   * Build elements of the variations.
   *
   * @param array $form
   *   The form from build function.
   * @param string $product_type
   *   The product type.
   * @param string $choose_type
   *   The radios/checkboxes type.
   * @param array $products_elements
   *   The elements to render.
   * @param array $default_values
   *   The default values of elements.
   * @param array $weights
   *   Weights of the elements.
   */
  protected function buildRadiosCheckboxes(array &$form, $product_type, $choose_type, array $products_elements, array $default_values, array $weights) {
    $variations = [];

    // Radios variant.
    if ($choose_type == 'radios') {
      // If radios type then default None element.
      $default_values = (count($default_values)) ? end($default_values) : 'none';

      $variations['radios'] = [
        '#type' => $choose_type,
        '#default_value' => $default_values,
        '#multiple' => TRUE,
        '#options' => $products_elements,
      ];

      // Create wrapper to elements.
      foreach ($products_elements as $element_id => $element_value) {
        $variations['radios'][$element_id] = [
          '#attributes' => [
            'class' => ['extras_select_option'],
            'data-variation-id' => $element_id,
          ],
          '#weight' => $weights[$element_id],
        ];
      }
    }
    // Checkboxes variant.
    else {
      // Wrapper of checkboxes.
      $variations["variations_wrapper"] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['variations_wrapper'],
        ],
      ];

      foreach ($products_elements as $element_id => $element_value) {
        $variations["variations_wrapper"]["variation_wrapper_.{$element_id}"] = [
          '#type' => 'container',
          '#attributes' => [
            'class' => ['variation_wrapper'],
          ],
          '#weight' => $weights[$element_id],
        ];

        // If checkbox fas children checkboxes.
        if (is_array($element_value)) {
          // Checked of the parent.
          $has_checked = FALSE;

          // Parent element.
          $variations["variations_wrapper"]["variation_wrapper_.{$element_id}"][$element_id] = [
            '#type' => 'checkbox',
            '#title' => $element_value['title'],
            '#default_value' => (in_array($element_value, $default_values)) ? TRUE : FALSE,
            '#attributes' => [
              'class' => ['extras_select_option'],
              'data-variation-id' => $element_id,
            ],
          ];

          // Children elements wrapper.
          $variations["variations_wrapper"]["variation_wrapper_.{$element_id}"]['children_variations'] = [
            '#type' => 'container',
            '#attributes' => [
              'class' => ['children_variations'],
              'style' => 'display: none;',
            ],
          ];

          // Children elements.
          foreach ($element_value['variations'] as $child_id => $child_value) {
            $default_value = in_array($child_id, $default_values);
            $variations["variations_wrapper"]["variation_wrapper_.{$element_id}"]['children_variations'][$child_id] = [
              '#type' => 'checkbox',
              '#title' => $child_value,
              '#default_value' => $default_value,
              '#attributes' => [
                'class' => ['extras_select_child_option'],
                'data-variation-id' => $child_id,
              ],
            ];

            $has_checked = ($has_checked || $default_value);
          }

          // If children element has default values then check parent.
          $variations["variations_wrapper"]["variation_wrapper_.{$element_id}"][$element_id]['#default_value'] = ($has_checked) ? TRUE : FALSE;
        }
        // If checkbox is single.
        else {
          $variations["variations_wrapper"]["variation_wrapper_.{$element_id}"][$element_id] = [
            '#type' => 'checkbox',
            '#title' => $element_value,
            '#default_value' => (in_array($element_id, $default_values)) ? TRUE : FALSE,
            '#attributes' => [
              'class' => ['extras_select_option'],
              'data-variation-id' => $element_id,
            ],
          ];
        }
      }
    }

    // Insert variations to the form.
    $form['add_extras_selection'][$product_type]['variations'] = $variations;
  }

  /**
   * Getting product Label by parameters.
   *
   * @param string $title
   *   The product title.
   * @param \Drupal\commerce_price\Price $price
   *   The product price.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Displayed product label.
   */
  protected function getProductLabel($title, Price $price) {
    return $this->t('@title - @price', [
      '@title' => $title,
      '@price' => $this->currencyFormatter->format($price->getNumber(), $price->getCurrencyCode()),
    ]);

  }

  /**
   * Build submit buttons.
   *
   * @param array $form
   *   The form from build function.
   */
  protected function buildTopSubmitButtons(array &$form) {
    $form['top_actions'] = [
      '#type' => 'container',
    ];

    $form['top_actions']['next_empty'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Continue without adding specials'),
      '#submit' => ['::submitWithoutSpecials'],
      '#attributes' => [
        'class' => ['next-empty-mobile'],
      ],
    ];
  }

  /**
   * Build submit buttons.
   *
   * @param array $form
   *   The form from build function.
   */
  protected function buildSubmitButtons(array &$form) {
    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['next_empty'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Continue without adding specials'),
      '#submit' => ['::submitWithoutSpecials'],
      '#attributes' => [
        'class' => ['next-empty-desktop'],
      ],
    ];

    $form['actions']['next'] = [
      '#type' => 'submit',
      '#button_type' => 'primary',
      '#value' => $this->t('Checkout'),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $variations = array_filter($form_state->getValues(), function ($value, $key) {
      return is_numeric($key) && !empty($value);
    }, ARRAY_FILTER_USE_BOTH);

    $variations[$form_state->getValue('radios')] = 1;

    // Clean all extras.
    $this->cleanAllExtrasItems();

    foreach ($variations as $variation_id => $value) {
      if ($variation_id == 'none') {
        continue;
      }

      $purchased_entity = $this->productVariationStorage->load($variation_id);
      if (!$purchased_entity || !$purchased_entity instanceof PurchasableEntityInterface) {
        \Drupal::logger('fleur_extras_selection')->error(t("Not all items have been successfully copied"));
        continue;
      }
      $new_order_item = $this->orderItemStorage->createFromPurchasableEntity($purchased_entity, [
        'quantity' => 1,
      ]);
      $this->cartManager->addOrderItem($this->cart, $new_order_item);
    }

    // Redirect to the cart.
    $form_state->setRedirect('commerce_cart.page');
  }

  /**
   * {@inheritdoc}
   */
  public function submitWithoutSpecials(array &$form, FormStateInterface $form_state) {
    // Clean all extras.
    $this->cleanAllExtrasItems();

    // Redirect to the cart.
    $form_state->setRedirect('commerce_cart.page');
  }

  /**
   * To remove all extras from the cart.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function cleanAllExtrasItems() {
    /** @var \Drupal\commerce_bulk\Entity\BulkProductVariation $order_variation */
    foreach ($this->cart->getItems() as $order_item) {
      $product_type = $order_item->getPurchasedEntity()->getProduct()->bundle();
      if (isset($this->productTypes[$product_type])) {
        $this->cart->removeItem($order_item);
        $order_item->delete();
      }
    }
  }

}
