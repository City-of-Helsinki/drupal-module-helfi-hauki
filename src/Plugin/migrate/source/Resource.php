<?php

declare(strict_types = 1);

namespace Drupal\helfi_tpr\Plugin\migrate\source;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\helfi_api_base\Plugin\migrate\source\HttpSourcePluginBase;

/**
 * Source plugin for retrieving data from Hauki.
 *
 * @MigrateSource(
 *   id = "hauki_resource"
 * )
 */
class Resource extends HttpSourcePluginBase implements ContainerFactoryPluginInterface {

  /**
   * Keep track of ignored rows to stop migrate after N ignored rows.
   *
   * @var int
   */
  protected int $ignoredRows = 0;

  /**
   * The total count.
   *
   * @var int
   */
  protected int $count = 0;

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    return 'HaukiOpeningHour';
  }

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return ['id' => ['type' => 'string']];
  }

  /**
   * {@inheritdoc}
   */
  public function count($refresh = FALSE) {
    if (!$this->count) {
      $this->count = count($this->getContent($this->configuration['url']));
    }
    return $this->count;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function next() {
    parent::next();

    // Check if the current row has changes and increment ignoredRows variable
    // to allow us to stop migrate early if we have no changes.
    if ($this->isPartialMigrate() && $this->currentRow && !$this->currentRow->changed()) {
      $this->ignoredRows++;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function initializeListIterator() : \Iterator {
    $content = $this->getContent($this->configuration['url']);
  }

}
