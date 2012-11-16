<?php

/**
 * Implements template_preprocess_page().
 */
function unl_og_preprocess_page(&$vars, $hook) {
  if (module_exists('og')) {
    // Set site_name to Group's display name.
    if (!empty($vars['node'])) {
      $group_context = og_context();

      // Make sure that the current page has a group associated with it.
      if ($group = node_load($group_context['gid'])) {
        $vars['site_name'] = $group->title;
      }
    }
//     //if not dealing with a node, Are we still in group context - views?
//     if(!$vars['og_id'] && $group = og_get_group_context()){
//         $vars['site_name'] = print_r($vars['site_name'],true);
//         $vars['og'] =$group->title;
//         $vars['og_id'] = $group->nid;
//     }
//     if ($vars['og_id']) {
//       $vars['site_name'] = $vars['og'] . ' <span>&nbsp;' . $vars['site_name'] . '</span>';
//     }
  }
}

/**
 * Implements hook_menu_breadcrumb_alter().
 */
function unl_og_menu_breadcrumb_alter(&$active_trail, $item) {
  $active_trail[0]['title'] = 'UNL';

  if (module_exists('og')) {
    $group_context = og_context();
    // This is the current node's parent group node
    $node = node_load($group_context['gid']);

    // Get the nid of the front page
    $front_url = drupal_get_normal_path(variable_get('site_frontpage', 'node'));
    $front_url = trim($front_url, '/');
    $front = explode('/', $front_url);
    if ($front[0]=='node' && ctype_digit($front[1])) {
      $front_nid = $front[1];
    }

    // Only splice in the current group if the current group is not the main/front group.
    if (isset($node) && isset($front_nid) && (int)$node->nid !== (int)$front_nid) {
      $group_breadcrumb = array(
        'title' => $node->title,
        'href' => 'node/' . $node->nid,
        'link_path' => '',
        'localized_options' => array(),
        'type' => 0,
      );
      array_splice($active_trail, 1, 0, array($group_breadcrumb));
    }
  }
}

/**
 * Implements theme_breadcrumb().
 */
function unl_og_breadcrumb($variables) {
  // Append title of current page -- http://drupal.org/node/133242
  if (!drupal_is_front_page()) {
    $variables['breadcrumb'][] = drupal_get_title();
  }
  // Add breadcrumb on front page. unl_og_menu_breadcrumb_alter is not called on front page.
  else {
    $variables['breadcrumb'][] = '<a href="' . url('<front>') . '">UNL</a>';
  }

  $html = '<ul>' . PHP_EOL;
  foreach ($variables['breadcrumb'] as $breadcrumb) {
    $html .= '<li>' .  $breadcrumb . '</li>' . PHP_EOL;
  }
  $html .= '</ul>';

  return $html;
}
