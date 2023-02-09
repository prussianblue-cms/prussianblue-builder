<?php

namespace Drupal\pb_devel_generate\DemoGenerators;

use Drupal\pb_devel_generate\DemoGeneratorInterface;
use Drupal\pb_devel_generate\GeneratorHelpers\LayoutNodeHelper;
use Drupal\pb_devel_generate\GeneratorHelpers\MediaHelper;
use Drupal\pb_devel_generate\GeneratorHelpers\ParagraphsHelper;
use Drupal\Component\Utility\Random;

class LogoStripGenerator implements DemoGeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public static function generate($variation, $values) {
    $messenger = \Drupal::messenger();
    $random = new Random();

    $target_field = $values['target_field'];
    $page = LayoutNodeHelper::createNodeWithLayoutVariation('page', $target_field, 'Logo Strips - ', $variation);

    // Populate each of the regions of each of the layout paragraphs with styles demo content
    $content_paragraphs = [];
    $population_distribution = [
      'header' => 'heading',
      'column_a' => 'logo-strip',
      'footer' => 'rich-text',
    ];

    $layout_paragraphs = LayoutNodeHelper::getLayoutParagraphs($page, $target_field);

    foreach($layout_paragraphs as $layout_paragraph_index => $layout_paragraph) {
      $paragraph_behavior_settings = $layout_paragraph->getAllBehaviorSettings();
      $paragraph_layout_name = $layout_paragraph->getBehaviorSetting('layout_paragraphs', 'layout');
      $paragraph_layout_definition = \Drupal::service('plugin.manager.core.layout')->getDefinition($paragraph_layout_name);
      $paragraph_regions = $paragraph_layout_definition->getRegions();
      $paragraph_uuid = $layout_paragraph->uuid->value;

      foreach($paragraph_regions as $region_name => $region_label) {

        // We really don't want to generate a zillion strips in tiny columns here.
        if($paragraph_layout_name !== 'pb_one_column') {
          continue;
        }

        $populate_with = $population_distribution[$region_name];
        switch($populate_with) {
          case 'heading':
            $paragraph_code = "$paragraph_layout_name--{$paragraph_behavior_settings['layout_paragraphs']['config']['additional']['classes']['color_scheme']}";
            $content_paragraphs[] = ParagraphsHelper::createHeadingParagraph($region_name, $paragraph_uuid, $paragraph_code, $paragraph_code);
            break;
          case 'logo-strip':
            // Create a fully random strip
            $content_paragraphs[] = ParagraphsHelper::createLogoStripParagraph($region_name, $paragraph_uuid);

            // Create a strip populated with 4:3 aspect ratio images
            $num_images = mt_rand(12, 20);
            $images_4_3 = [];
            $media_image = MediaHelper::createMediaImageFromUrl("https://dummyimage.com/300x225/ED003A/fff.png", "300x225.png", '300x225') ;
            for($i=0; $i<$num_images; $i++) {
              $images_4_3[] = ParagraphsHelper::createImageParagraphFromMediaEntity($region_name, $paragraph_uuid, $media_image);
            }

            // Grid style
            $content_paragraphs[] = ParagraphsHelper::createLogoStripParagraph($region_name, $paragraph_uuid, 'grid', $images_4_3);

            // Strip style
            $content_paragraphs[] = ParagraphsHelper::createLogoStripParagraph($region_name, $paragraph_uuid, 'strip', $images_4_3);
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
      $field_content[] = [
        'target_id' => $para->id(),
        'target_revision_id' => $para->getRevisionId()
      ];
    }

    $page->set($target_field, $field_content);
    $page->save();

    return $page;
  }
}


