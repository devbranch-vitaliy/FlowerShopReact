<?php

namespace Drupal\fleur_notifications\Renderer;

use CommerceGuys\Addressing\Country\CountryRepositoryInterface;
use Drupal\commerce_order\OrderTotalSummaryInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface as CoreRendererInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_order\Renderer\OrderRenderer;
use Drupal\entity_print\Asset\AssetRendererInterface;
use Drupal\entity_print\FilenameGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Provides a custom entity_print renderer for orders.
 *
 * Uses the commerce_order_receipt template for the document contents.
 */
class FleurOrderRenderer extends OrderRenderer {

  /**
   * The country repository.
   *
   * @var \CommerceGuys\Addressing\Country\CountryRepositoryInterface
   */
  protected $countryRepository;

  /**
   * Constructs a new OrderEntityRenderer object.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The core render service.
   * @param \Drupal\entity_print\Asset\AssetRendererInterface $asset_renderer
   *   The entity print asset renderer.
   * @param \Drupal\entity_print\FilenameGeneratorInterface $filename_generator
   *   A filename generator.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   The event dispatcher.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\commerce_order\OrderTotalSummaryInterface $order_total_summary
   *   The order total summary service.
   * @param \CommerceGuys\Addressing\Country\CountryRepositoryInterface $country_repository
   *   The country repository.
   */
  public function __construct(CoreRendererInterface $renderer, AssetRendererInterface $asset_renderer, FilenameGeneratorInterface $filename_generator, EventDispatcherInterface $event_dispatcher, EntityTypeManagerInterface $entity_type_manager, OrderTotalSummaryInterface $order_total_summary, CountryRepositoryInterface $country_repository) {
    parent::__construct($renderer, $asset_renderer, $filename_generator, $event_dispatcher, $entity_type_manager, $order_total_summary);

    $this->countryRepository = $country_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static (
      $container->get('renderer'),
      $container->get('entity_print.asset_renderer'),
      $container->get('entity_print.filename_generator'),
      $container->get('event_dispatcher'),
      $container->get('entity_type.manager'),
      $container->get('commerce_order.order_total_summary'),
      $container->get('address.country_repository')
    );
  }

  /**
   * Builds a print ready render array for a single order.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface $order
   *   The order entity.
   *
   * @return array
   *   The render array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function renderSingle(OrderInterface $order) {
    $build = [
      '#theme' => 'fleur_order_receipt',
      '#order_entity' => $order,
      '#totals' => $this->orderTotalSummary->buildTotals($order),
    ];
    /** @var \Drupal\profile\Entity\Profile $billing_profile */
    if ($billing_profile = $order->getBillingProfile()) {
      $build['#billing_information'] = $billing_profile;
      $build['#billing_address'] = $billing_profile->get('address')->first();
      $build['#billing_country'] = $this->countryRepository->get($build['#billing_address']->getCountryCode());
    }
    if (!$order->get('payment_method')->isEmpty()) {
      $build['#payment_method'] = [
        '#plain_text' => $order->get('payment_method')->first()->entity->label(),
      ];
    }

    /** @var \Drupal\commerce_payment\Entity\Payment $payments */
    $payments = $this->entityTypeManager->getStorage('commerce_payment')->loadByProperties(['order_id' => $order->id()]);
    if (count($payments)) {
      $build['#paid_time'] = end($payments)->getCompletedTime();
    }
    return $build;
  }

}
