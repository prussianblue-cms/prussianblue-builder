<?php

namespace Drupal\pb_devel_generate\DemoGenerators;

use Drupal\pb_devel_generate\DemoGeneratorInterface;
use Drupal\pb_devel_generate\GeneratorHelpers\LayoutNodeHelper;
use Drupal\pb_devel_generate\GeneratorHelpers\MediaHelper;
use Drupal\pb_devel_generate\GeneratorHelpers\ParagraphsHelper;
use Drupal\Component\Utility\Random;

class EmbedUrlGenerator implements DemoGeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public static function generate($variation, $values) {
    $messenger = \Drupal::messenger();
    $random = new Random();

    $target_field = $values['target_field'];
    $page = LayoutNodeHelper::createNodeWithLayoutVariation('pb_page', $target_field, 'Embed URLs - ', $variation);

    $content_paragraphs = [];
    $population_distribution = [
      'header' => 'heading',
      'column_a' => 'embed-url',
      'column_b' => 'embed-url',
      'column_c' => 'embed-url',
      'column_d' => 'embed-url',
      'footer' => null,
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
            $paragraph_code = "$paragraph_layout_name--{$paragraph_behavior_settings['layout_paragraphs']['config']['additional']['classes']['color_scheme']}";
            $content_paragraphs[] = ParagraphsHelper::createHeadingParagraph($region_name, $paragraph_uuid, $paragraph_code, $paragraph_code);
            break;
          case 'embed-url':
            $content_paragraphs[] = ParagraphsHelper::createEmbedUrlParagraph($region_name, $paragraph_uuid, 'https://twitter.com/NaturePortfolio/status/1567567628238979074');
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

    $target_field = $values['target_field'];
    $page->set($target_field, $field_content);
    $page->save();

    return $page;
  }
}
