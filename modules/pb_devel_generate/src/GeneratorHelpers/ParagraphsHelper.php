<?php

/**
 * @file ParagraphsHelper class
 * Provides functions to create content paragraphs
 */
namespace Drupal\m3_page_builder\GeneratorHelpers;

use Drupal\paragraphs\Entity\Paragraph;
use Drupal\devel_generate\DevelGenerateBase;
use Drupal\Component\Utility\Random;

class ParagraphsHelper {
  private static function createBaseParagraph($type, $region, $parent_uuid) {
    $default_langcode = \Drupal::service('language_manager')->getDefaultLanguage()->getId();

    $values = [
      'type' => $type,
      'langcode' => $default_langcode,
      'default_langcode' => 1,
    ];

    $paragraph = Paragraph::create($values);

    $behavior_settings = [
      "layout_paragraphs" => [
        "region" => $region,
        "parent_uuid" => $parent_uuid,
        "layout" => "",
        "config" => [],
        "parent_delta" => 0,
      ],
    ];

    $paragraph->setAllBehaviorSettings($behavior_settings);
    return $paragraph;
  }

  public static function createHeadingParagraph($region, $parent_uuid, $code, $label) {
    $paragraph = static::createBaseParagraph('content_heading', $region, $parent_uuid);
    $paragraph->set('field_content_heading_code', $code);
    $paragraph->set('field_content_heading_label', $label);

    $paragraph->save();
    return $paragraph;
  }

  public static function createTextParagraph($region, $parent_uuid, $content=null) {
    $paragraph = static::createBaseParagraph('content_item', $region, $parent_uuid);

    if(is_null($content)) {
      DevelGenerateBase::populateFields($paragraph);
    }
    else {
      $paragraph->field_content_item_column->value = $content;
      $paragraph->field_content_item_column->format = 'formatted_text';
    }

    $paragraph->save();
    return $paragraph;
  }

  public static function createCardWithLinkParagraph($region, $parent_uuid, $title, $brief, $preview_image, $style) {
    $paragraph = static::createBaseParagraph('content_card_with_link', $region, $parent_uuid);

    $paragraph->set('field_card_brief', $brief);
    $paragraph->set('field_card_title', $title);
    $paragraph->set('field_card_preview', $preview_image->id());
    $paragraph->set('field_card_style', $style);
    $paragraph->set('field_linked_content', 'https://www.matrushka.com.mx');

    $paragraph->save();
    return $paragraph;
  }

  /**
   * Creates a dummy image paragraph and populates with a random image
   */
  public static function createImageParagraph($region, $parent_uuid) {
    // Create a random media image
    $media_image = MediaHelper::createMediaImage();
    return static::createImageParagraphFromMediaEntity($region, $parent_uuid, $media_image);
  }

  /**
   * Creates an image paragraph from a given media image entity
   */
  public static function createImageParagraphFromMediaEntity($region, $parent_uuid, $media_image) {
    $random = new Random();
    $paragraph = static::createBaseParagraph('content_embed_image', $region, $parent_uuid);

    $paragraph->set('field_embed_image', $media_image->id());
    $paragraph->set('field_optional_link', 'http://www.matrushka.com.mx');
    $paragraph->set('field_image_alt', $random->sentences(4));
    $paragraph->save();

    return $paragraph;
  }

  public static function createVideoParagraphFromMediaEntity($region, $parent_uuid, $media_video) {
    $paragraph = static::createBaseParagraph('content_embed_video', $region, $parent_uuid);
    $paragraph->set('field_media_video', $media_video->id());
    $paragraph->save();

    return $paragraph;
  }

  /**
   * Creates an URL embed paragraph for the given url
   * @param string $url
   */
  public static function createUrlEmbedParagraphFromUrl($region, $parent_uuid, $url) {
    $paragraph = static::createBaseParagraph('content_embed_url', $region, $parent_uuid);
    $paragraph->set('field_embedded_url', $url);
    $paragraph->save();
    return $paragraph;
  }

  /**
   * Creates a Slider paragraph, which includes creating demo contents for it: text, images and videos
   */
  public static function createSliderParagraph($region, $parent_uuid) {
    $image1 = static::createImageParagraph($region, $parent_uuid);
    $image2 = static::createImageParagraph($region, $parent_uuid);

    $text1_content = "<p>This is a simple paragraph.</p><p class='featured'>This is a featured paragraph.</p><h2 class='heading-1'>This is a Heading 2</h2><h3 class='heading-2'>This is a Heading 3</h3><h4 class='heading-3'>This is a Heading 4</h4><h5 class='heading-4'>This is a Heading 5</h5>";
    $text1 = static::createTextParagraph($region, $parent_uuid, $text1_content);

    $text2_content = "<blockquote><p>This is a very long quote nec turpis. Maecenas sed sem vel urna venenatis volutpat. Vestibulum in odio blandit, aliquet eros non, faucibus nunc.</p></blockquote><p class='quote-attribution-name'>Marcus Aurelius</p><p class='quote-attribution-position'>Roman emperor from 161 to 180</p>";
    $text2 = static::createTextParagraph($region, $parent_uuid, $text2_content);

    $media_video = MediaHelper::createMediaVideoFromUrl('https://www.youtube.com/watch?v=tBRgguvqTWU');
    $video1 = static::createVideoParagraphFromMediaEntity($region, $parent_uuid, $media_video);

    $content_paragraphs = [
      $image1,
      $text1,
      $image2,
      $text2,
      $video1
    ];

    // Since the entities generated by createBaseParagraph are originally meant to be used with layout paragraphs
    // they have behavior settings that do not apply to them, such as parent uuid and region
    // Strip the extra behaviors to prevent conflicts
    $field_content = [];
    foreach($content_paragraphs as $content_para) {
      $behavior = $content_para->setAllBehaviorSettings([]);

      $field_content[] = [
        'target_id' => $content_para->id(),
        'target_revision_id' => $content_para->getRevisionId()
      ];
    }

    $paragraph = static::createBaseParagraph('content_slider', $region, $parent_uuid);
    $paragraph->set('field_slider_content_items', $field_content);
    $paragraph->save();
    return $paragraph;
  }

  /**
   * Creates a Logo Carousel paragraph
   * @param array $images
   *    An array of image paragraphs to be used as logos. If ommitted, a random image set will be created
   */
  public static function createLogoCarouselParagraph($region, $parent_uuid, $max_columns=null, $images=null) {
    if(!$images) {
      $num_images = mt_rand(10, 20);
      $images = [];
      for($i=0; $i<$num_images; $i++) {
        $images[] = static::createImageParagraph($region, $parent_uuid);
      }
    }

    // Since the entities generated by createBaseParagraph are originally meant to be used with layout paragraphs
    // they have behavior settings that do not apply to them, such as parent uuid and region
    // Strip the extra behaviors to prevent conflicts
    $field_elements_list = [];
    foreach($images as $image_paragraph) {
      $behavior = $image_paragraph->setAllBehaviorSettings([]);

      $field_elements_list[] = [
        'target_id' => $image_paragraph->id(),
        'target_revision_id' => $image_paragraph->getRevisionId()
      ];
    }

    $paragraph = static::createBaseParagraph('content_logo_carrousel', $region, $parent_uuid);
    $paragraph->set('field_element_list', $field_elements_list);
    $max_columns = (is_null($max_columns)) ? mt_rand(3, 6) : $max_columns;
    $paragraph->set('field_column_count', $max_columns);
    $paragraph->save();

    return $paragraph;
  }
}
