<?php

namespace Drupal\fleur_node_pager\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Url;
use Drupal\node\Entity\Node;

/**
 * Created block with pager for node page.
 *
 * @Block(
 *   id = "fleur_node_pager",
 *   admin_label = @Translation("Node pager"),
 *   category = @Translation("Fleur node pager"),
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
    /** @var \Drupal\node\Entity\Node $node */
    $node = $this->getContextValue('node');

    // Get previous nodes.
    $query = \Drupal::entityQuery('node')
      ->condition('type', $node->getType())
      ->condition('changed', $node->get('changed')->value, '<')
      ->sort('changed', 'DESC')
      ->sort('nid', 'DESC')
      ->pager(1);

    $previous = $query->execute();

    // Get next nodes.
    $query = \Drupal::entityQuery('node')
      ->condition('type', $node->getType())
      ->condition('changed', $node->get('changed')->value, '>')
      ->sort('changed', 'ASC')
      ->sort('nid', 'ASC')
      ->pager(1);

    $next = $query->execute();

    // Create cacheable metadata.
    $cache = new CacheableMetadata();

    // Create elements.
    $block['pager_links'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['pager-links'],
      ],
    ];

    if ($previous) {
      $id = reset($previous);
      $link_node = Node::load($id);
      $cache->addCacheableDependency($link_node);

      $block['pager_links']['previous_link'] = [
        '#title' => $link_node->getTitle(),
        '#type' => 'link',
        '#url' => Url::fromRoute('entity.node.canonical', ['node' => $id], ['absolute' => TRUE]),
        '#attributes' => [
          'class' => ['previous-links'],
        ],
      ];
    }

    if ($next) {
      $id = reset($next);
      $link_node = Node::load($id);
      $cache->addCacheableDependency($link_node);

      $block['pager_links']['next_link'] = [
        '#title' => Node::load($id)->getTitle(),
        '#type' => 'link',
        '#url' => Url::fromRoute('entity.node.canonical', ['node' => $id], ['absolute' => TRUE]),
        '#attributes' => [
          'class' => ['next-links'],
        ],
      ];
    }

    $cache->applyTo($block);

    return $block;
  }

}
