<?php

namespace Drupal\fleur_news_pager\Plugin\NewsPager;

use Drupal\Core\Block\BlockBase;

/**
 * Created block with pager for news page.
 *
 * @Block(
 *   id = "fleur_news_pager"
 *   admin_label = @Translation("News pager")
 *   context = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("News Node"))
 *   }
 * )
 */
class NewsPager extends BlockBase {

  /**
   * Builds and returns the renderable array for this block plugin.
   *
   * @return array
   *   A renderable array representing the content of the block.
   *
   * @see \Drupal\block\BlockViewBuilder
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function build() {
    $node = $this->getContextValue('node');

    $block = [
      '#type' => 'markup',
      '#markup' => "test text",
    ];
    return $block;
  }

}
