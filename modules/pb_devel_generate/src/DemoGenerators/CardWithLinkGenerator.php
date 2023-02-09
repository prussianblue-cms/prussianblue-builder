<?php

namespace Drupal\m3_page_builder\DemoGenerators;

use Drupal\m3_page_builder\DemoGeneratorInterface;
use Drupal\m3_page_builder\GeneratorHelpers\LayoutNodeHelper;
use Drupal\m3_page_builder\GeneratorHelpers\MediaHelper;
use Drupal\m3_page_builder\GeneratorHelpers\ParagraphsHelper;
use Drupal\Component\Utility\Random;

class CardWithLinkGenerator implements DemoGeneratorInterface {
  /**
   * {@inheritdoc}
   */
  public static function generate($variation, $values) {
    $random = new Random();
    $target_field = $values['pages_target_field'];
    $page = LayoutNodeHelper::createNodeWithLayoutVariation('page',  $target_field, 'Cards with Link - ', $variation);

    $page->set('field_content_brief', $random->sentences(4));
    $preview_image_media = MediaHelper::createMediaImage();
    $page->field_preview_image->target_id = $preview_image_media->id();
    $page->save();

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

      $card_styles = ['minimal', 'regular', 'large', 'horizontal'];
      foreach($paragraph_regions as $region_name => $region_label) {
        // If region is prefix, just add a heading
        if($region_name == 'prefix') {
          $paragraph_code = "$paragraph_layout_name--{$paragraph_behavior_settings['layout_paragraphs']['config']['additional']['classes']['custom_style']}";
          $content_paragraphs[] = ParagraphsHelper::createHeadingParagraph($region_name, $paragraph_uuid, $paragraph_code, $paragraph_code);
        }

        else if(substr($region_name, 0, 6) == 'column') {
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
      // HACK ALERT: hardcoded filtering of compatible paragraph types
      // TODO: programmatically exclude the paragraph types that are not compatible with the field definition
      // and move all this code that repeats in every generator to a base implementation
      $para_is_heading = $para->type->target_id == 'content_heading';
      $field_is_intro = $target_field == 'field_intro';
      if(!($para_is_heading && $field_is_intro)) {
        $field_content[] = [
          'target_id' => $para->id(),
          'target_revision_id' => $para->getRevisionId()
        ];
      }
    }

    $target_field = $values['pages_target_field'];
    $page->set($target_field, $field_content);
    $page->save();

    return $page;
  }
}
