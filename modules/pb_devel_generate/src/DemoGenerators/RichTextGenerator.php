<?php

namespace Drupal\pb_devel_generate\DemoGenerators;

use Drupal\pb_devel_generate\DemoGeneratorInterface;
use Drupal\pb_devel_generate\GeneratorHelpers\LayoutNodeHelper;
use Drupal\pb_devel_generate\GeneratorHelpers\MediaHelper;
use Drupal\pb_devel_generate\GeneratorHelpers\ParagraphsHelper;
use Drupal\Component\Utility\Random;

class RichTextGenerator implements DemoGeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public static function generate($variation, $values) {
    $messenger = \Drupal::messenger();
    $random = new Random();

    $target_field = $values['target_field'];
    $page = LayoutNodeHelper::createNodeWithLayoutVariation('page', $target_field, 'Rich Text - ', $variation);

    // TODO: move rich text styles to a file to make this more readable
    $text_styles_demo_content = "<h2 class='heading-1'>This is a Heading 2</h2><h3 class='heading-2'>This is a Heading 3</h3><h4 class='heading-3'>This is a Heading 4</h4><h5 class='heading-4'>This is a Heading 5</h5><p>Duis ornare feugiat blandit. Curabitur finibus nec lectus vel lacinia. Etiam ultrices molestie porta. In porttitor nisl ac orci gravida blandit. Mauris vitae sem eros. Praesent placerat diam leo, sit amet cursus enim elementum sed. Etiam sed felis et diam iaculis iaculis eu eu purus.</p><p class='featured'>Morbi massa massa, pharetra quis sollicitudin ut, rhoncus vel enim. Etiam nec pharetra magna. Sed vel quam quis mauris mollis commodo non vitae enim. </p><p>Vivamus congue diam ac neque faucibus molestie. Vestibulum id orci convallis eros tempus finibus eget a elit. Phasellus nec condimentum libero. Interdum et malesuada fames ac ante ipsum primis in faucibus. Nunc a erat eu purus accumsan ornare. Cras rhoncus quis leo sit amet mattis. </p><blockquote><p>This is a very long quote nec turpis. Maecenas sed sem vel urna venenatis volutpat. Vestibulum in odio blandit, aliquet eros non, faucibus nunc.</p></blockquote><p class='quote-attribution-name'>Marcus Aurelius</p><p class='quote-attribution-position'>Roman emperor from 161 to 180</p>";

    // Populate each of the regions of each of the layout paragraphs with styles demo content
    $content_paragraphs = [];
    $population_distribution = [
      'header' => 'heading',
      'column_a' => 'rich-text',
      'column_b' => 'rich-text',
      'column_c' => 'rich-text',
      'column_d' => 'rich-text',
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
        $populate_with = $population_distribution[$region_name];
        switch($populate_with) {
          case 'heading':
            $paragraph_code = "$paragraph_layout_name--{$paragraph_behavior_settings['layout_paragraphs']['config']['additional']['classes']['color_scheme']}";
            $content_paragraphs[] = ParagraphsHelper::createHeadingParagraph($region_name, $paragraph_uuid, $paragraph_code, $paragraph_code);
            break;
          case 'rich-text':
            $content_paragraphs[] = ParagraphsHelper::createTextParagraph($region_name, $paragraph_uuid, $text_styles_demo_content);
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
