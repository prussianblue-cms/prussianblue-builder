<?php

/**
 * @file
 * Hooks provided by the pb_paragraphs_connector module.
 */

/**
 * Discover available recipients for paragraphs
 *
 * Modules may implement this hook if they want to enable paragraphs, regardles of origin
 * (including the module implementing the hook) in their paragraphs fields.
 *
 * @param array[] $paragraph_recipients
 *   The array of recipient content types, their paragraph fields and the paragraph types they receive
 *
 */
function hook_discover_paragraph_recipients(&$paragraph_recipients){
  $paragraph_recipients['pb_page'] = [
    'field_pb_paragraphs_content' => [
      'pb_card_with_link',
      'pb_rich_text',
      'freeform',
      'some_para_from_contrib_module',
    ]
  ];
}
