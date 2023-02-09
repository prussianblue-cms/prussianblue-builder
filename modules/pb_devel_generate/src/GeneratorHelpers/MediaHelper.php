<?php

/**
 * @file MediaHelper class
 *  Provides functions to create media items to be used in M3 content generators
 **/

namespace Drupal\pb_devel_generate\GeneratorHelpers;

use Drupal\Component\Utility\Random;
use Drupal\devel_generate\DevelGenerateBase;
use Drupal\Core\File\FileSystemInterface;
use Drupal\media\Entity\Media;

class MediaHelper {
  public static function createMediaImage() {
    return static::createMedia('image');
  }

  public static function createMediaImageWithResolution($min_resolution, $max_resolution) {
    $random = new Random();
    $file_system = \Drupal::service('file_system');
    $file_repository = \Drupal::service('file.repository');
    $media_image = static::createMedia('image');

    $image_name = "between-$min_resolution-and-$max_resolution";
    $filename = "$image_name.jpg";

    $container_directory = 'public://test-media-images/';
    $file_system->prepareDirectory($container_directory, FileSystemInterface::CREATE_DIRECTORY || FileSystemInterface::MODIFY_PERMISSIONS);
    $file_path = $file_system->realpath('public://test-media-images/' . $filename);
    $random->image($file_path, $min_resolution, $max_resolution);

    $image_data = file_get_contents($file_path);
    $file = $file_repository->writeData($image_data, $container_directory.$filename);
    $media_image->set('field_media_image', $file->id());

    $media_image->set('name', $image_name);
    $media_image->save();
    return $media_image;
  }

  /**
   * Creates a media image and populates it with the file provided in the URL
   * @param string $image_url
   * @param string $filename
   * @param string $name
   *    Optional, the name for the media item
   */
  public static function createMediaImageFromUrl($image_url, $filename, $name=null) {
    $file_system = \Drupal::service('file_system');
    $file_repository = \Drupal::service('file.repository');
    $media_image = static::createMedia('image');

    // Create a new image file with the given URL
    // Populate the media item image field with the image we just created
    // save the media item
    // return it
    $image_data = file_get_contents($image_url);
    $container_directory = 'public://test-media-images/';
    $file_system->prepareDirectory($container_directory, FileSystemInterface::CREATE_DIRECTORY || FileSystemInterface::MODIFY_PERMISSIONS);
    $file = $file_repository->writeData($image_data, $container_directory.$filename);
    $media_image->set('field_media_image', $file->id());

    if($name) {
      $media_image->set('name', $name);
    }

    $media_image->save();
    return $media_image;
  }

  /**
   * Creates a video entity to embed the given URL, as long as it is from an oembed source
   * (not 100% sure about that, but YouTube and Vimeo should work)
   */
  public static function createMediaVideoFromUrl($video_url) {
    $media_video = static::createMedia('remote_video');
    $media_video->set('field_media_oembed_video', $video_url);
    $media_video->save();
    return $media_video;
  }

  /**
   * Generates a Media item of the requested type and populates its
   * fields with sample items
   */
  public static function createMedia($type) {
    $random = new Random();
    // TODO: enable dummy content translation
    $default_langcode = \Drupal::service('language_manager')->getDefaultLanguage()->getId();

    $media = Media::create([
      'bundle' => $type,
      'name' => $random->sentences(4),
      'uid' => 1,
      'revision' => mt_rand(0, 1),
      'status' => TRUE,
      'langcode' => $default_langcode,
    ]);

    DevelGenerateBase::populateFields($media);

    $media->save();

    return $media;
  }
}
