<?php

namespace Drupal\m3_page_builder\DemoGenerators;

use Drupal\m3_page_builder\DemoGeneratorInterface;
use Drupal\m3_page_builder\GeneratorHelpers\LayoutNodeHelper;
use Drupal\m3_page_builder\GeneratorHelpers\MediaHelper;
use Drupal\m3_page_builder\GeneratorHelpers\ParagraphsHelper;
use Drupal\Component\Utility\Random;

class ImagesGenerator implements DemoGeneratorInterface {
  /**
   * {@inheritdoc}
   **/
  public static function generate($variation, $values) {
    $messenger = \Drupal::service('messenger');
    $random = new Random();

    $target_field = $values['pages_target_field'];
    $page = LayoutNodeHelper::createNodeWithLayoutVariation('page', $target_field, 'Image Sizes - ', $variation);

    $page->set('field_content_brief', $random->sentences(4));
    $preview_image_media = MediaHelper::createMediaImage();
    $page->field_preview_image->target_id = $preview_image_media->id();
    $page->save();

    $test_image_sizes = [
      '100x100',
      '256x256',
      '400x300',
      '200x400',
      '800x300'
    ];

    $test_images = [];
    foreach($test_image_sizes as $test_size) {
      $test_images[] = MediaHelper::createMediaImageFromUrl("https://dummyimage.com/$test_size/ED003A/fff.png", "$test_size.png", $test_size) ;
    }


    // Populate each of the regions of each of the layout paragraphs with styles demo content
    $content_paragraphs = [];
    $population_distribution = [
      'prefix' => 'heading',
      'column_a' => 'images',
      'column_b' => 'images',
      'column_c' => 'images',
      'column_d' => 'images',
      'suffix' => null,
    ];

    $layout_paragraphs = LayoutNodeHelper::getLayoutParagraphs($page, $target_field);

    foreach($layout_paragraphs as $layout_paragraph_index => $layout_paragraph) {
      $paragraph_behavior_settings = $layout_paragraph->getAllBehaviorSettings();
      $paragraph_layout_name = $layout_paragraph->getBehaviorSetting('layout_paragraphs', 'layout');
      $paragraph_layout_definition = \Drupal::service('plugin.manager.core.layout')->getDefinition($paragraph_layout_name);
      $paragraph_regions = $paragraph_layout_definition->getRegions();
      $paragraph_uuid = $layout_paragraph->uuid->value;

      foreach($paragraph_regions as $region_name => $region_label) {
        $populate_with = $population_distribution[$region_name];
        switch($populate_with) {
          case 'heading':
            $paragraph_code = "$paragraph_layout_name--{$paragraph_behavior_settings['layout_paragraphs']['config']['additional']['classes']['custom_style']}";
            $content_paragraphs[] = ParagraphsHelper::createHeadingParagraph($region_name, $paragraph_uuid, $paragraph_code, $paragraph_code);
            break;
          case 'images':
            foreach($test_images as $test_image) {
              $content_paragraphs[] = ParagraphsHelper::createImageParagraphFromMediaEntity($region_name, $paragraph_uuid, $test_image);
            }
            break;
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
