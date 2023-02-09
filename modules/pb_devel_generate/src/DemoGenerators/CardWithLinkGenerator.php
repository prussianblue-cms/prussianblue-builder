<?php

namespace Drupal\pb_devel_generate\DemoGenerators;

use Drupal\pb_devel_generate\DemoGeneratorInterface;
use Drupal\pb_devel_generate\GeneratorHelpers\LayoutNodeHelper;
use Drupal\pb_devel_generate\GeneratorHelpers\MediaHelper;
use Drupal\pb_devel_generate\GeneratorHelpers\ParagraphsHelper;
use Drupal\Component\Utility\Random;

class CardWithLinkGenerator implements DemoGeneratorInterface {
  /**
   * {@inheritdoc}
   */
  public static function generate($variation, $values) {
    $random = new Random();
    $target_field = $values['target_field'];
    $page = LayoutNodeHelper::createNodeWithLayoutVariation('pb_page',  $target_field, 'Card with Link - ', $variation);

    // Create a few card preview images to be selected at random
    $preview_images = [];
    while(count($preview_images) < 5) {
      $preview_images[] = MediaHelper::createMediaImage();
    }

    $layout_paragraphs = LayoutNodeHelper::getLayoutParagraphs($page, $target_field);
    $content_paragraphs = [];

    foreach($layout_paragraphs as $layout_paragraph_name => $layout_paragraph) {
      $paragraph_behavior_settings = $layout_paragraph->getAllBehaviorSettings();
      $paragraph_layout_name = $layout_paragraph->getBehaviorSetting('layout_paragraphs', 'layout');
      $paragraph_layout_definition = \Drupal::service('plugin.manager.core.layout')->getDefinition($paragraph_layout_name);
      $paragraph_regions = $paragraph_layout_definition->getRegions();
      $paragraph_uuid = $layout_paragraph->uuid->value;

      // These values must match the ones available in the field_pb_card_style
      // field of the pb_card_with_link paragraph
      $card_styles = ['title_only', 'image_title', 'full', 'full_horizontal'];
      foreach($paragraph_regions as $region_name => $region_label) {
        // If region is prefix, just add a heading
        if($region_name == 'header') {
          $paragraph_code = "$paragraph_layout_name--{$paragraph_behavior_settings['layout_paragraphs']['config']['additional']['classes']['color_scheme']}";
          $content_paragraphs[] = ParagraphsHelper::createHeadingParagraph($region_name, $paragraph_uuid, $paragraph_code, $paragraph_code);
        }

        else if(strpos($region_name, 'column') !== false) {
          // If region is a column, populate with cards in all styles
          $title = $random->sentences(2);
          $brief = $random->sentences(16);

          // Iterate over card styles
          // Create a card paragraph for each and assign to the region
          foreach($card_styles as $style) {
            $preview_image_entity = $preview_images[array_rand($preview_images)];
            $card = ParagraphsHelper::createCardWithLinkParagraph($region_name, $paragraph_uuid, $title, $brief, $preview_image_entity, $style);
            $content_paragraphs[] = $card;
          }
        }
      }
    }

    $field_content = [];
    foreach($layout_paragraphs as $para) {
      $field_content[] = [
        'target_id' => $para->id(),
        'target_revision_id' => $para->getRevisionId()
      ];
    }

    foreach($content_paragraphs as $para) {
      $field_content[] = [
        'target_id' => $para->id(),
        'target_revision_id' => $para->getRevisionId()
      ];
    }

    $target_field = $values['target_field'];
    $page->set($target_field, $field_content);
    $page->save();

    return $page;
  }
}
