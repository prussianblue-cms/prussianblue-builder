<?php

/**
 * @file LayoutNodeHelper class
 *  Provides functions to create nodes, populate them with layout paragraphs and
 *  provide useful functions related to both the nodes and the layouts
 **/

namespace Drupal\pb_devel_generate\GeneratorHelpers;

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

class LayoutNodeHelper {

  /**
   * Creates a new node of the given type and populates the target field
   * with layout paragraphs, of alternating themes between dark and light,
   * for the given width and balance
   * @param string $bundle the machine name of the content type
   * @param string $target_field the machine name of the field to populate
   * @param string $title_prefix a string to be used as prefix to the node title
   * @param array $variation an associative array with width and balance
   */
  public static function createNodeWithLayoutVariation($bundle, $target_field, $title_prefix, $variation) {

    $page_title = "$title_prefix - {$variation['width']} + {$variation['balance']}";
    $node_values = [
      'nid' => NULL,
      'type' => 'pb_page',
      'title' => $page_title,
      'uid' => 1,
      'revision' => mt_rand(0, 1),
      'moderation_state' => 'published',
      'status' => 1,
      'promote' => mt_rand(0, 1),
    ];

    // Create the Page node
    $node = Node::create($node_values);

    // Populate with the requested layout
    $layout_paragraphs = static::getLayoutParagraphsForVariation($variation);

    $field_content = [];
    foreach($layout_paragraphs as $paragraph) {
      $field_content[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId()
      ];
    }

    $node->set($target_field, $field_content);
    $node->save();

    return $node;
  }

  /**
   * Creates all the layout paragraphs for the requested variation and returns
   * them as an array
   */
  private static function getLayoutParagraphsForVariation($variation) {
    $default_langcode = \Drupal::service('language_manager')->getDefaultLanguage()->getId();
    $paragraphs_behavior_settings = static::getParagraphBehaviorsForVariation($variation);

    $paragraphs = [];
    foreach($paragraphs_behavior_settings as $paragraph_name => $paragraph_settings) {
      $values = [
        'type' => 'pb_layout',
        'langcode' => $default_langcode,
        'default_langcode' => 1,
      ];
      $paragraph = Paragraph::create($values);
      $paragraph->setAllBehaviorSettings($paragraph_settings);
      $paragraph->save();

      $paragraphs[$paragraph_name] = $paragraph;
    }

    return $paragraphs;
  }

  /**
   * Creates a flat array of behavior settings for the layout paragraphs needed
   * for the requested variation
   */
  private static function getParagraphBehaviorsForVariation($variation) {
    $paragraphs_behavior_settings = [];

    $layouts = ['pb_one_column', 'pb_two_columns', 'pb_three_columns', 'pb_four_columns'];
    $color_schemes = ['light', 'dark'];
    foreach($layouts as $layout) {
      foreach($color_schemes as $color_scheme) {
        $balance = ($layout != 'pb_one_column') ? $variation['balance'] : null;
        $paragraphs_behavior_settings[$layout . '-' . $color_scheme] = static::getLayoutParagraphsBehaviorForParameters($layout, $variation['width'], $color_scheme, $balance);
      }
    }
    return $paragraphs_behavior_settings;
  }

  /**
   * Produces a behavior parameters array as expected by Layout Paragraphs
   * from a given set of parameters, in simplified mode
   *
   * The source of these values is the layouts yaml in the pb_layouts module
   *
   * @param $layout (pb_one_column, pb_two_columns, pb_three_columns, pb_four_columns)
   * @param $width The width of the container and its background (wide, wide--full-background, narrow, narrow--full-background)
   * @param $color_scheme The theme for the layout, in simplified form ('light', 'dark')
   */
  private static function getLayoutParagraphsBehaviorForParameters($layout, $width, $color_scheme, $balance=null) {
    $behavior = [
     'layout_paragraphs' => [
       'region' => '',
       'parent_uuid' => '',
       'layout' => $layout,
       'config' => [
         'label' => '',
         'additional' => [
           'classes' => [
             'width' => 'layout--' . $width,
             'color_scheme' => 'layout--' . $color_scheme,
             'balance' => 'layout--' . $balance,
           ],
         ],
       ],
     ],
   ];

   return $behavior;
  }

  /**
   * Extracts the existing layout paragraphs in the target field of the provided entity as an array
   */
  public static function getLayoutParagraphs($entity, $target_field) {
    $layout_paragraphs = [];
    foreach($entity->$target_field as $item) {
      if($item->entity->type->target_id == 'pb_layout') {
        $layout_paragraphs[] = $item->entity;
      }
    }

    return $layout_paragraphs;
  }
}
