<?php

namespace Drupal\m3_page_builder\DemoGenerators;

use Drupal\m3_page_builder\DemoGeneratorInterface;
use Drupal\m3_page_builder\GeneratorHelpers\LayoutNodeHelper;
use Drupal\m3_page_builder\GeneratorHelpers\MediaHelper;
use Drupal\m3_page_builder\GeneratorHelpers\ParagraphsHelper;
use Drupal\Component\Utility\Random;

class BackgroundTestsGenerator implements DemoGeneratorInterface {

  /**
   * {@inheritdoc}
   */
  public static function generate($variation, $values) {
    $target_field = $values['pages_target_field'];

    $test_image_sizes = [
      '400x800',
      '400x400',
      '1000x800',
      '1600x1000',
    ];

    $pages = [];
    foreach($test_image_sizes as $test_size) {
      $test_image = MediaHelper::createMediaImageWithResolution($test_size, $test_size) ;

      $page = TextStylesGenerator::generate($variation, $values);
      $page->title->value = $page->title->value . ' background: ' . $test_size;
      $page->save();

      foreach($page->$target_field as $field_item) {
        $paragraph = $field_item->entity;
        $paragraph_type = $paragraph->type->target_id;

        if($paragraph_type == 'content_layout') {
          $paragraph->set('field_background_image', $test_image->id());
          $paragraph->save();
        }
      }

      $pages[] = $page;
    }

    return $pages;
  }
}
