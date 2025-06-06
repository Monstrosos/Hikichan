<?php

// vichan's routing mechanism

defined('TINYBOARD') or exit;

function route($path) { global $config;
  $entrypoints = array();

  $entrypoints['/%b/']                       = 'sb_board';
  $entrypoints['/%b/'.$config['file_index']] = 'sb_board';
  $entrypoints['/%b/'.$config['file_page']]  = 'sb_board';
  $entrypoints['/%b/%d.json']                = 'sb_api_board';
  if ($config['api']['enabled']) {
    $entrypoints['/%b/threads.json']         = 'sb_api';
    $entrypoints['/%b/catalog.json']         = 'sb_api';
  }

  // Support for live_date_path in thread URLs (e.g., /board/res/YYYY/MM/DD/123.html)
  // %p = optional date path (YYYY/MM/DD/ or similar)
  $entrypoints['/%b/'.$config['dir']['res'].'%p'.$config['file_page']]          = 'sb_thread_slugcheck';
  $entrypoints['/%b/'.$config['dir']['res'].'%p'.$config['file_page50']]        = 'sb_thread_slugcheck50';
  if ($config['slugify']) {
    $entrypoints['/%b/'.$config['dir']['res'].'%p'.$config['file_page_slug']]   = 'sb_thread_slugcheck';
    $entrypoints['/%b/'.$config['dir']['res'].'%p'.$config['file_page50_slug']] = 'sb_thread_slugcheck50';
  }
  if ($config['api']['enabled']) {
    $entrypoints['/%b/'.$config['dir']['res'].'%p%d.json']                      = 'sb_thread';
  }

  // Legacy routes without date path (for backward compatibility)
  $entrypoints['/%b/'.$config['dir']['res'].$config['file_page']]          = 'sb_thread_slugcheck';
  $entrypoints['/%b/'.$config['dir']['res'].$config['file_page50']]        = 'sb_thread_slugcheck50';
  if ($config['slugify']) {
    $entrypoints['/%b/'.$config['dir']['res'].$config['file_page_slug']]   = 'sb_thread_slugcheck';
    $entrypoints['/%b/'.$config['dir']['res'].$config['file_page50_slug']] = 'sb_thread_slugcheck50';
  }
  if ($config['api']['enabled']) {
    $entrypoints['/%b/'.$config['dir']['res'].'%d.json']                   = 'sb_thread';
  }

  $entrypoints['/*/']              = 'sb_ukko';
  $entrypoints['/*/index.html']    = 'sb_ukko';
  $entrypoints['/recent.html']     = 'sb_recent';
  $entrypoints['/%b/catalog.html'] = 'sb_catalog';
  $entrypoints['/%b/index.rss']    = 'sb_catalog';
  $entrypoints['/sitemap.xml']     = 'sb_sitemap';

  $entrypoints = array_merge($entrypoints, $config['controller_entrypoints']);

  $reached = false;

  list($request) = explode('?', $path);

  foreach ($entrypoints as $id => $fun) {
    $id = '@^' . preg_quote($id, '@') . '$@u';

    $id = str_replace('%b', '('.$config['board_regex'].')', $id);
    $id = str_replace('%d', '([0-9]+)',                     $id);
    $id = str_replace('%s', '[a-zA-Z0-9-]+',                $id);
    // %p: optional date path (YYYY/MM/DD/ or similar, can be empty)
    $id = str_replace('%p', '((?:[0-9]{4}/[0-9]{2}/[0-9]{2})/)?', $id);

    $matches = null;

    if (preg_match($id, $request, $matches)) {
      array_shift($matches);

      $reached = array($fun, $matches);

      break;
    }
  }

  return $reached;
}
