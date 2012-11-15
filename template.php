<?php

/**
 * Implements template_preprocess_page().
 */
function unl_og_preprocess_page(&$vars, $hook) {
  if (module_exists('og')) {
    // Set site_name to Group's display name.
    if (!empty($vars['node'])) {
      $group_context = og_context();

      //Make sure that the current page has a group associated with it.
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
 * Implements theme_breadcrumb().
 */
function unl_og_breadcrumb($variables) {
  $breadcrumbs = $variables['breadcrumb'];
  if (module_exists('og')) {
    $group_context = og_context();
    $group = node_load($group_context['gid']);

    if ($group && count($breadcrumbs) > 0) {
      array_unshift($breadcrumbs, str_replace('Home', $group->title, array_shift($breadcrumbs)));
      // Remove Group breadcrumb for main group
      if ($group->title == 'University of Nebraska–Lincoln') {
        array_pop($breadcrumbs);
      }
    }
  }

  //Prepend UNL
  if (variable_get('site_name') != 'UNL') {
    array_unshift($breadcrumbs, '<a href="http://www.unl.edu/">UNL</a>');
  }

  //Append title of current page -- http://drupal.org/node/133242
  if (!drupal_is_front_page()) {
    $breadcrumbs[] = drupal_get_title();
  }

  $html = '<ul>' . PHP_EOL;
  foreach ($breadcrumbs as $breadcrumb) {
    $html .= '<li>' .  $breadcrumb . '</li>';
  }
  $html .= '</ul>';

  return $html;
}
