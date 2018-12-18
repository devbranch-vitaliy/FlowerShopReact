<?php

namespace Drupal\fleur_notifications\EventSubscriber;

use Drupal\commerce_order\Entity\Order;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Event\OrderEvent;
use Drupal\commerce_order\OrderTotalSummaryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\Renderer;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface;
use Drupal\entity_print\PrintBuilderInterface;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sends a receipt email when an order is placed.
 */
class OrderReceiptSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The order type entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $orderOrderStorage;

  /**
   * The order total summary.
   *
   * @var \Drupal\commerce_order\OrderTotalSummaryInterface
   */
  protected $orderTotalSummary;

  /**
   * The entity view builder for profiles.
   *
   * @var \Drupal\profile\ProfileViewBuilder
   */
  protected $profileViewBuilder;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The mail manager.
   *
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The Print builder.
   *
   * @var \Drupal\entity_print\PrintBuilderInterface
   */
  protected $printBuilder;

  /**
   * The plugin manager for our Print engines.
   *
   * @var \Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface
   */
  protected $pluginManager;

  /**
   * File system service.
   *
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * Constructs a new OrderReceiptSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager.
   * @param \Drupal\commerce_order\OrderTotalSummaryInterface $order_total_summary
   *   The order total summary.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer.
   * @param \Drupal\entity_print\PrintBuilderInterface $print_builder
   *   The print $print_builder.
   * @param \Drupal\entity_print\Plugin\EntityPrintPluginManagerInterface $plugin_manager
   *   The print engine.
   * @param \Drupal\Core\File\FileSystem $file_system
   *   File system service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, MailManagerInterface $mail_manager, OrderTotalSummaryInterface $order_total_summary, Renderer $renderer, PrintBuilderInterface $print_builder, EntityPrintPluginManagerInterface $plugin_manager, FileSystem $file_system) {
    $this->orderOrderStorage = $entity_type_manager->getStorage('commerce_order');;
    $this->orderTotalSummary = $order_total_summary;
    $this->profileViewBuilder = $entity_type_manager->getViewBuilder('profile');
    $this->languageManager = $language_manager;
    $this->mailManager = $mail_manager;
    $this->renderer = $renderer;
    $this->printBuilder = $print_builder;
    $this->pluginManager = $plugin_manager;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = [
      'commerce_order.place.pre_transition' => ['sendOrderPlaced', -100],
      'commerce_order.order.paid' => ['sendOrderPaid', -100],
      'commerce_order.fulfill.post_transition' => ['sendOrderCompleted', -100],
    ];
    return $events;
  }

  /**
   * Sends an order receipt email.
   *
   * Sending when order is placed.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The event we subscribed to.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function sendOrderPlaced(WorkflowTransitionEvent $event) {
    $this->sendNotification($event->getEntity(), 'fleur_order_placed');
  }

  /**
   * Sends an order receipt email.
   *
   * Sending when order is paid.
   *
   * @param \Drupal\commerce_order\Event\OrderEvent $event
   *   The event we subscribed to.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function sendOrderPaid(OrderEvent $event) {
    $this->sendNotification($event->getOrder(), 'fleur_order_paid');
  }

  /**
   * Sends an order receipt email.
   *
   * Sending when order is completed.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The event we subscribed to.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function sendOrderCompleted(WorkflowTransitionEvent $event) {
    $this->sendNotification($event->getEntity(), 'fleur_order_completed');
  }

  /**
   * Sends an order receipt email.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The commerce order.
   * @param string $notification_type
   *   A type of notification template.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function sendNotification(OrderInterface $order, $notification_type) {
    $to = $order->getEmail();
    if (!$to) {
      // The email should not be empty, unless the order is malformed.
      return;
    }

    $subject = $this->getSubject($order, $notification_type);

    $params = [
      'headers' => [
        'Content-Type' => 'text/html; charset=UTF-8;',
        'Content-Transfer-Encoding' => '8Bit',
      ],
      'from' => $order->getStore()->getEmail(),
      'subject' => $subject,
      'order' => $order,
    ];

    $build = [
      '#theme' => $notification_type,
      '#order_entity' => $order,
    ];

    if ($notification_type == 'fleur_order_completed') {
      /** @var \Drupal\commerce_shipping\Entity\Shipment[] $shipments */
      $shipments = $order->get('shipments')->referencedEntities();

      if (count($shipments)) {
        /** @var \Drupal\address\Plugin\Field\FieldType\AddressItem $address */
        $address = current($shipments)->getShippingProfile()->get('address')->get(0);
        $build['#address'] = $address;
      }
    }

    $params['body'] = $this->renderer->executeInRenderContext(new RenderContext(), function () use ($build) {
      return $this->renderer->render($build);
    });

    // Create PDF invoice when order was paid.
    if ($notification_type == 'fleur_order_paid') {
      $print_engine = $this->pluginManager->createInstance('tcpdfv1');
      $uri = "";
      if (isset($print_engine)) {
        // Print builder generates a filename for us.
        $uri = $this->printBuilder->savePrintable([$order], $print_engine);
        if (!empty($uri)) {
          $params['attachments'][] = $this->fileSystem->realpath($uri);
        }
      }
    }

    // Replicated logic from EmailAction and contact's MailHandler.
    $customer = $order->getCustomer();
    if ($customer->isAuthenticated()) {
      $langcode = $customer->getPreferredLangcode();
    }
    else {
      $langcode = $this->languageManager->getDefaultLanguage()->getId();
    }

    $this->mailManager->mail('fleur_notifications', 'receipt', $to, $langcode, $params);

    // Unlink PDF file after email sending.
    if ($notification_type == 'fleur_order_paid' && !empty($uri)) {
      $this->fileSystem->unlink($uri);
    }
  }

  /**
   * Geting subject text.
   *
   * @param \Drupal\commerce_order\Entity\Order $order
   *   The type of notification.
   * @param string $notification_type
   *   The type of notification.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   The subject.
   */
  protected function getSubject(Order $order, $notification_type) {
    switch ($notification_type) {
      case 'fleur_order_placed':
        $subject = $this->t('Awaiting your payment');
        break;

      case 'fleur_order_paid':
        $subject = $this->t("IT'S TIME TO GET EXCITED!");
        break;

      case 'fleur_order_completed':
        $subject = $this->t('Order #@number completed', ['@number' => $order->id()]);
        break;

      default:
        $subject = $this->t('Order #@number information', ['@number' => $order->id()]);
    }

    return $subject;
  }

}
