<?php

namespace Drupal\m3_page_builder\DemoGenerators;

use Drupal\m3_page_builder\DemoGeneratorInterface;
use Drupal\m3_page_builder\GeneratorHelpers\LayoutNodeHelper;
use Drupal\m3_page_builder\GeneratorHelpers\MediaHelper;
use Drupal\m3_page_builder\GeneratorHelpers\ParagraphsHelper;
use Drupal\Component\Utility\Random;

class LogoCarouselGenerator implements DemoGeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public static function generate($variation, $values) {
    $messenger = \Drupal::messenger();
    $random = new Random();

    $target_field = $values['pages_target_field'];
    $page = LayoutNodeHelper::createNodeWithLayoutVariation('page', $target_field, 'Logo Carousels - ', $variation);

    $page->set('field_content_brief', $random->sentences(4));
    $preview_image_media = MediaHelper::createMediaImage();
    $page->field_preview_image->target_id = $preview_image_media->id();
    $page->save();

    // Populate each of the regions of each of the layout paragraphs with styles demo content
    $content_paragraphs = [];
    $population_distribution = [
      'prefix' => 'heading',
      'column_a' => 'logo-carousel',
      'suffix' => 'text-styles-demo',
    ];

    $layout_paragraphs = LayoutNodeHelper::getLayoutParagraphs($page, $target_field);

    foreach($layout_paragraphs as $layout_paragraph_index => $layout_paragraph) {
      $paragraph_behavior_settings = $layout_paragraph->getAllBehaviorSettings();
      $paragraph_layout_name = $layout_paragraph->getBehaviorSetting('layout_paragraphs', 'layout');
      $paragraph_layout_definition = \Drupal::service('plugin.manager.core.layout')->getDefinition($paragraph_layout_name);
      $paragraph_regions = $paragraph_layout_definition->getRegions();
      $paragraph_uuid = $layout_paragraph->uuid->value;

      foreach($paragraph_regions as $region_name => $region_label) {

        // We really don't want to generate a zillion carousels in tiny columns here.
        if($paragraph_layout_name !== 'm3_one_column') {
          continue;
        }

        $populate_with = $population_distribution[$region_name];
        switch($populate_with) {
          case 'heading':
            $paragraph_code = "$paragraph_layout_name--{$paragraph_behavior_settings['layout_paragraphs']['config']['additional']['classes']['custom_style']}";
            $content_paragraphs[] = ParagraphsHelper::createHeadingParagraph($region_name, $paragraph_uuid, $paragraph_code, $paragraph_code);
            break;
          case 'logo-carousel':
            // Create a fully random carousel
            $content_paragraphs[] = ParagraphsHelper::createLogoCarouselParagraph($region_name, $paragraph_uuid);

            // Create a carousel populated with 4:3 aspect ratio images
            $num_images = mt_rand(12, 20);
            $images_4_3 = [];
            $media_image = MediaHelper::createMediaImageFromUrl("https://dummyimage.com/300x225/ED003A/fff.png", "300x225.png", '300x225') ;
            for($i=0; $i<$num_images; $i++) {
              $images_4_3[] = ParagraphsHelper::createImageParagraphFromMediaEntity($region_name, $paragraph_uuid, $media_image);
            }
            // Max 3 columns
            $content_paragraphs[] = ParagraphsHelper::createLogoCarouselParagraph($region_name, $paragraph_uuid, 3, $images_4_3);

            // Max 4 columns
            $content_paragraphs[] = ParagraphsHelper::createLogoCarouselParagraph($region_name, $paragraph_uuid, 4, $images_4_3);

            // Max 5 columns
            $content_paragraphs[] = ParagraphsHelper::createLogoCarouselParagraph($region_name, $paragraph_uuid, 5, $images_4_3);

            // Max 6 columns
            $content_paragraphs[] = ParagraphsHelper::createLogoCarouselParagraph($region_name, $paragraph_uuid, 6, $images_4_3);
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

    $page->set($target_field, $field_content);
    $page->save();

    return $page;
  }
}


