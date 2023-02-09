<?php

namespace Drupal\pb_devel_generate;

/**
 * Base interface definition for Demo generator plugins, used in the Devel Generate plugin implementation.
 *
 * Each generator should produce a single node, populated with a type of paragraphs or for some specific demonstration purpose
 */

interface DemoGeneratorInterface {
  /**
   * @param array $variation
   *  The combination of render mode (column width) and vertical alignmnet to use
   * @param array $values
   *  The values from the devel generate form
   * @return Node $node
   *  Returns the node generated or an array of nodes if multiple were produced
   */
  public static function generate($variation, $values);
}
