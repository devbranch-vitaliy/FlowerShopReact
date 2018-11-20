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
    $order = $event->getOrder();
    $order_state = $order->getState()->value;

    //    /** @var \Drupal\commerce_payment\PaymentStorageInterface $payment_storage */
    //    $payment_storage = \Drupal::entityTypeManager()->getStorage('commerce_payment');
    ////    $payment_storage = $this->entityTypeManager->getStorage('commerce_payment');
    //    // Confirm that only one payment was made.
    //    $payments = $payment_storage->loadMultipleByOrder($order);
    //
    //    $paid = TRUE;
    //    foreach ($payments as $payment){
    //      if ($payment->getState()->value!='completed'){
    //        $paid = FALSE;
    //      }
    //    }

    if ($order_state=='validation' && $order->isPaid()){
      $order->set('state', 'fulfillment');
      $order->save();
    }
  }

}
