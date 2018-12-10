<?php

namespace Drupal\fleur_send_sms\EventSubscriber;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\sms\Message\SmsMessage;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\sms\Direction;
use Drupal\sms\Entity\SmsGateway;

/**
 * A subscriber for events of order.
 */
class SendSMS implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      'commerce_order.place.pre_transition' => ['onPlaceTransition', -100],
    ];
  }

  /**
   * Send SMS when order was placed.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The event.
   */
  public function onPlaceTransition(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();

    drupal_register_shutdown_function([$this, 'send'], $order);
  }

  /**
   * The shutdown function for sending sms.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The commerce order.
   */
  public function send(OrderInterface $order) {
    $sms_message = t('Thank you for placing your order with Palette de Fleur. Your order number is @number.', ['@number' => $order->getOrderNumber()]);
    $phone_number = $order->getBillingProfile()->get('field_telephone')->getString();

    if (strpos($phone_number) !== FALSE) {
      $phone_number = str_replace('+', '', $phone_number);
    }

    /** @var \Drupal\sms\Provider\SmsProviderInterface $sms_service */
    $sms_service = \Drupal::service('sms.provider');

    $sms = (new SmsMessage())
      ->setMessage($sms_message)
      ->addRecipient($phone_number)
      ->setDirection(Direction::OUTGOING)
      ->setGateway(SmsGateway::load('sms_clickatell'));

    $sms_service->queue($sms);
  }

}

