<?php

namespace Drupal\fleur_order_operations\Controller;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_shipping\ShipmentItem;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\physical\Weight;
use Drupal\physical\WeightUnit;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for new operation links.
 */
class OrderOperationsController extends ControllerBase {

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
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * Constructs a new controller object.
   *
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger.
   */
  public function __construct(CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, EntityTypeManagerInterface $entity_type_manager, MessengerInterface $messenger, LoggerInterface $logger) {
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
    $this->logger = $logger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('entity_type.manager'),
      $container->get('messenger'),
      $container->get('logger.factory')->get('Fleur order operations')
    );
  }

  /**
   * Create a Cart from current Order information.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Redirect page
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function createNewCartFromOrder(Request $request, RouteMatchInterface $route_match) {

    // Get target order.
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $route_match->getParameter('commerce_order');

    // Get current cart.
    $order_type_id = $order->bundle();
    $store = $order->getStore();
    $cart = $this->cartProvider->getCart($order_type_id, $store);

    // If cart don't exists then create it else remove previous options.
    if (!$cart) {
      $cart = $this->cartProvider->createCart($order_type_id, $store);
    }
    else {
      $this->cartManager->emptyCart($cart);
    }

    // Copy order items.
    foreach ($order->getItems() as $order_item) {
      $purchased_entity = $order_item->getPurchasedEntity();
      if (!$purchased_entity || !$purchased_entity instanceof PurchasableEntityInterface) {
        $this->logger->error(t("Not all items have been successfully copied"));
        continue;
      }
      $new_order_item = $this->entityTypeManager->getStorage('commerce_order_item')->createFromPurchasableEntity($purchased_entity, [
        'quantity' => $order_item->getQuantity(),
      ]);
      $this->cartManager->addOrderItem($cart, $new_order_item);
    }

    // Set billing profile.
    $cart->setBillingProfile($order->getBillingProfile());
    $cart->save();

    // Set shipments.
    $shipments = $this->getShipmentsFromOrder($order, $cart);
    $cart->set('shipments', $shipments);
    $cart->save();

    $this->messenger->addMessage(t("Order successfully copied"), MessengerInterface::TYPE_STATUS);
    return $this->redirect('commerce_cart.page');
  }

  /**
   * Create new cart shipments from order shipments.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   Commerce Order.
   * @param \Drupal\commerce_order\Entity\OrderInterface $cart
   *   Commerce Cart.
   *
   * @return array|\Drupal\commerce_shipping\Entity\ShipmentInterface[]
   *   Shipments to cart
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getShipmentsFromOrder(OrderInterface $order, OrderInterface $cart) {

    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface[] $shipments */
    $shipments = [];
    $shipment_storage = $this->entityTypeManager->getStorage('commerce_shipment');

    /** @var \Drupal\commerce_shipping\Entity\ShipmentInterface $shipment */
    foreach ($order->get('shipments')->referencedEntities() as $shipment) {
      $new_shipment = $shipment_storage->create([
        'type' => $shipment->bundle(),
        'state' => 'draft',
        'order_id' => $cart->id(),
      ]);
      $new_shipment->setPackageType($shipment->getPackageType());
      $new_shipment->setShippingMethod($shipment->getShippingMethod());
      $new_shipment->setShippingService($shipment->getShippingService());
      $new_shipment->setShippingProfile($shipment->getShippingProfile());
      $new_shipment->setTitle($shipment->getTitle());
      $new_shipment->setWeight($shipment->getWeight());
      $new_shipment->setAmount($shipment->getAmount());
      $new_shipment->set('field_delivery_date', $shipment->get('field_delivery_date')->getString());

      /** @var \Drupal\commerce_shipping\ShipmentItem $item */
      /** @var \Drupal\physical\Weight $weight */
      foreach ($cart->getItems() as $order_item) {
        $purchased_entity = $order_item->getPurchasedEntity();
        // Ship only shippable purchasable entity types.
        if (!$purchased_entity || !$purchased_entity->hasField('weight')) {
          continue;
        }
        // The weight will be empty if the shippable trait was added but the
        // existing entities were not updated.
        if ($purchased_entity->get('weight')->isEmpty()) {
          $purchased_entity->set('weight', new Weight(0, WeightUnit::GRAM));
        }

        $quantity = $order_item->getQuantity();
        $weight = $purchased_entity->get('weight')->first()->toMeasurement();

        $item = new ShipmentItem([
          'order_item_id' => $order_item->id(),
          'title' => $order_item->getTitle(),
          'quantity' => $quantity,
          'weight' => $weight->multiply($quantity),
          'declared_value' => $order_item->getUnitPrice()->multiply($quantity),
        ]);

        $new_shipment->addItem($item);
      }

      $new_shipment->save();
      $shipments[] = $new_shipment;
    }
    return $shipments;
  }

}
