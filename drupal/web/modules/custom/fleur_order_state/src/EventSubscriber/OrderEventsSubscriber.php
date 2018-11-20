<?php

namespace Drupal\fleur_order_state\EventSubscriber;

use Drupal\commerce_order\Event\OrderEvent;
use Drupal\commerce_order\Event\OrderEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class OrderEventsSubscriber implements EventSubscriberInterface{

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      OrderEvents::ORDER_UPDATE => 'onCreateUpdateOrder',
      OrderEvents::ORDER_CREATE => 'onCreateUpdateOrder',
    ];
  }

  /**
   * Checking state of the order and the payment. If order has state 'Validation' and payment - 'Complete'
   * then the order state will change to 'Fulfillment'
   *
   * @param \Drupal\commerce_order\Event\OrderEvent $event
   *   The event.
   */
  public function onCreateUpdateOrder(OrderEvent $event) {

  }

}
