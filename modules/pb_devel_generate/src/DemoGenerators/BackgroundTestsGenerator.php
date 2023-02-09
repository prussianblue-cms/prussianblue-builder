<?php

namespace Drupal\pb_devel_generate\DemoGenerators;

use Drupal\pb_devel_generate\DemoGeneratorInterface;
use Drupal\pb_devel_generate\GeneratorHelpers\LayoutNodeHelper;
use Drupal\pb_devel_generate\GeneratorHelpers\MediaHelper;
use Drupal\pb_devel_generate\GeneratorHelpers\ParagraphsHelper;
use Drupal\Component\Utility\Random;

class BackgroundTestsGenerator implements DemoGeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public static function generate($variation, $values) {
    $target_field = $values['target_field'];

    $test_image_sizes = [
      '400x800',
      '400x400',
      '1000x800',
      '1600x1000',
    ];

    $pages = [];
    foreach($test_image_sizes as $test_size) {
      $test_image = MediaHelper::createMediaImageWithResolution($test_size, $test_size) ;

      $page = RichTextGenerator::generate($variation, $values);
      $page->title->value = $page->title->value . ' background: ' . $test_size;
      $page->save();

      foreach($page->$target_field as $field_item) {
        $paragraph = $field_item->entity;
        $paragraph_type = $paragraph->type->target_id;

        if($paragraph_type == 'pb_layout') {
          $paragraph->set('field_pb_image', $test_image->id());
          $paragraph->save();
        }
      }

      $pages[] = $page;
    }

    return $pages;
  }
}
