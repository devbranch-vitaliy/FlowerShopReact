<?php

namespace Drupal\fleur_node_pager\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Created block with pager for node page.
 *
 * @Block(
 *   id = "fleur_node_pager",
 *   admin_label = @Translation("Node pager"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node")),
 *   }
 * )
 */
class NodePager extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = $this->getContextValue('node');

    $block = [
      '#type' => 'markup',
      '#markup' => $this->t('test text'),
    ];
    return $block;
  }

}
