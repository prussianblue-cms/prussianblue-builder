<?php

/**
 * @file
 * Hooks provided by the m3_page_builder module
 */

/**
 * Discover available generators for the M3 Devel Generate implementation
 * @return array $generators
 *  An array of generator classes and their labels
 */
function hook_pb_get_demo_generators() {
  return [
    'pb_example_module--text-styles' => [
      'label' => 'Text Styles',
      'class' => \Drupal\pb_example_module\DemoGenerators\TextStylesGenerator::class
    ],
  ];
}
