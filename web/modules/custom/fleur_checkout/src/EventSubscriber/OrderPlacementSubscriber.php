<?php

namespace Drupal\fleur_checkout\EventSubscriber;

use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes user for a mailing list when the order is placed.
 */
class OrderPlacementSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'commerce_order.place.pre_transition' => ['subscribeUser', 0],
    ];
    return $events;
  }

  /**
   * Subscribes user to a mailing list, if set.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The event we subscribed to.
   */
  public function subscribeUser(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    if (!($mailchimp_list = $order->getData('mailchimp_list'))) {
      // No Mailchimp list is set. Just skip.
      return;
    }
    $double_opt_in = boolval($order->getData('mailchimp_double_opt_in'));
    mailchimp_subscribe($mailchimp_list, $order->getEmail(), NULL, [], $double_opt_in);
  }

}
