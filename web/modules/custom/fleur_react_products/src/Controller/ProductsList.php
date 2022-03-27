<?php

namespace Drupal\fleur_react_products\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller routines for page products react routes.
 */
class ProductsList extends ControllerBase {

  /**
   * Constructs a page with products list.
   *
   * @return array
   *   The page structure.
   */
  public function productsReact(): array {
    $page = [];
    $page['fleur_products_list'] = [
      '#markup' => '<div id="react-products-list"></div>',
      '#attached' => [
        'library' => 'fleur_react_products/products-list',
      ],
    ];

    return $page;
  }

}
