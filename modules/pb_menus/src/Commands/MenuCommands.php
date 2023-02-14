<?php

namespace Drupal\pb_menus\Commands;

use Drush\Commands\DrushCommands;
use Drupal\menu_link_content\Entity\MenuLinkContent;

/**
 * A Drush command file
 * @package Drupal\pb_menus\Commands
 */
class MenuCommands extends DrushCommands {

  /**
   * Connect paragraphs with their target fields
   *
   * @command pb:populate-menus
   * @aliases ppm
   * @usage pb:populate-menus
   */
  public function populateMenus() {
    $items = [
      'With sub' => [
        'Sub 1',
        'Sub 2',
        'Sub 3',
      ],
      'Second',
      'Third',
      'Fourth page'
    ];

    $this->addItemsToMenu($items, 'main');

    // Populate secondary menu with
    // - facebook
    // - instagram
    // - youtube
    // - linkedin
    // - twitter
    // - vimeo
    // - mastodon
    $items = [
      'facebook',
      'instagram',
      'youtube',
      'linkedin',
      'twitter',
      'vimeo',
      'mastodon'
    ];

    $this->addItemsToMenu($items, 'secondary-navigation', null, true);
  }

  // Adds items to the menu, recursively
  // @param array $items An array of strings that represent the names of the links.
  // For nested items, the key will be the name of the parent link and the
  // strings in the array will be the children.
  //
  // $items = [
  //   'With sub' => [
  //     'Sub 1',
  //     'Sub 2',
  //     'Sub 3',
  //   ],
  //   'Second',
  //   'Third',
  //   'Fourth page'
  // ];
  //
  // @param str $menu_name The machine name of the menu in which the item should reside
  // @param bool $class_from_title Whether to generate class names from link titles
  private function addItemsToMenu($items, $menu_name, $parent_item_id = null, $class_from_title = false) {
    foreach($items as $key => $item) {

      // If the item is an array, then the key is the
      // title of the parent item and the array
      // contains the children
      if(is_array($item)) {
        $menu_item = $this->createMenuItem($menu_name, $key, $parent_item_id, $class_from_title);
        $menu_item_id = $menu_item->getPluginId();

        $this->addItemsToMenu($item, $menu_name, $menu_item_id, $class_from_title);
      }

      // If the item is not an array, it is assumed to
      // be a string that represents the title of the link
      else {
        $this->createMenuItem($menu_name, $item, $parent_item_id, $class_from_title);
      }
    }
  }

  private function createMenuItem($menu_name, $title, $parent_id = null, $class_from_title = false) {
    $menu_link = MenuLinkContent::create([
      'title' => $title,
      'link' => ['uri' => 'internal:/'],
      'menu_name' => $menu_name,
      'expanded' => TRUE,
    ]);
    $menu_link->save();

    if($parent_id) {
      $menu_link->set('parent', $parent_id);
    }

    $menu_link->save();

    if($class_from_title) {
      $class_name = str_replace(' ', '-', strtolower($title));
      $menu_link->link->first()->options = [
        'attributes' => [
          'class' => [$class_name]
        ]
      ];
    }

    $menu_link->save();

    return $menu_link;
  }
}
