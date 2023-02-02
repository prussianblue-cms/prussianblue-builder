<?php

namespace Drupal\pb_paragraphs_connector\Commands;

use Drush\Commands\DrushCommands;

/**
 * A Drush command file
 * @package Drupal\pb_paragraphs_connector\Commands
 */
class ParagraphsConnectorCommands extends DrushCommands {

  /**
   * Connect paragraphs with their target fields
   *
   * @command pb:paragraphs-connect
   * @aliases ppc
   * @usage pb:paragraphs-connect
   */
  public function paragraphsConnect() {
    $paragraph_recipients = [];
    $paragraphs = [];

    \Drupal::moduleHandler()->invokeAll('discover_paragraph_recipients', [&$paragraph_recipients]);
    \Drupal::moduleHandler()->invokeAll('discover_paragraphs', [&$paragraphs]);

    $existing_paragraph_types = \Drupal::entityTypeManager()->getStorage('paragraphs_type')->loadMultiple();
    $existing_paragraph_type_ids = array_keys($existing_paragraph_types);

    foreach($paragraph_recipients as $bundle => $bundle_target_fields) {
      foreach($bundle_target_fields as $target_field => $paragraphs_accepted) {

        $configuration_id = "field.field.node.$bundle.$target_field";
        $configuration = \Drupal::configFactory()->getEditable($configuration_id);
        $current_paragraphs = $configuration->get('settings.handler_settings.target_bundles');

        foreach($paragraphs_accepted as $index => $paragraph) {
          $in_current_paragraphs = !empty($current_paragraphs) && in_array($paragraph, $current_paragraphs);
          $paragraph_exists = in_array($paragraph, $existing_paragraph_type_ids);

          if(!$in_current_paragraphs && $paragraph_exists) {
            $dependencies = $configuration->get('dependencies.config');
            $dependencies[] = "paragraphs.paragraphs_type.$paragraph";

            $configuration->set('dependencies.config', $dependencies);
            $configuration->set('settings.handler_settings.target_bundles.'.$paragraph, $paragraph);
            $configuration->set('settings.handler_settings.target_bundles_drag_drop.'.$paragraph, ['enabled' => true, 'weight' => $index]);
            $configuration->save(TRUE);
          }
        }
      }
    }
  }
}
