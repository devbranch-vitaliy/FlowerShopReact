<?php

namespace Drupal\fleur_operations\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for new operation links.
 */
class OperationsController extends ControllerBase {

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
   */
  public function createNewCartFromOrder(Request $request, RouteMatchInterface $route_match) {

    // Default message.
    $message = "Order successfully copied";
    $message_type = MessengerInterface::TYPE_STATUS;

    $messenger = \Drupal::messenger();
    $messenger->addMessage($message, $message_type);
    return $this->redirect('commerce_cart.page');
  }

}