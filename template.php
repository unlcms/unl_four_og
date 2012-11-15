<?php


/**
 * Implements template_preprocess_field().
 */
function unl_preprocess_field(&$vars, $hook) {
  $element = $vars['element'];
  // Set the field label tag to a header or default to div
  if (strlen($element['#label_display']) == 2 && substr($element['#label_display'], 0, 1) == 'h') {
    $vars['label_html_tag'] = $element['#label_display'];
  }
  else {
    $vars['label_html_tag'] = 'div';
  }
}

/**
 * Implements template_preprocess_region().
 * Adds grid classes for sidebar_first, sidebar_second, and content regions.
 */
function unl_preprocess_region(&$vars) {
/*  $vars['region_name'] = str_replace('_', '-', $vars['region']);
  $vars['classes_array'][] = $vars['region_name'];

  if (in_array($vars['region'], array_keys($grid['regions']))) {
    $vars['classes_array'][] = 'grid' . $grid['regions'][$vars['region']]['width'];
  }

  // Sidebar regions receive common 'sidebar' class
  $sidebar_regions = array('sidebar_first', 'sidebar_second');
  if (in_array($vars['region'], $sidebar_regions)) {
    $vars['classes_array'][] = 'sidebar';
  }

  // Determine which region needs the 'first' class
  if ($vars['region'] == 'content' && $grid['regions']['sidebar_first']['width'] == 0) {
    $vars['classes_array'][] = 'first';
  }
  else if ($vars['region'] == 'sidebar_first') {
    $vars['classes_array'][] = 'first';
  }*/
}

/**
 * Implements template_preprocess_node().
 */
function unl_preprocess_node(&$vars) {
  // Drupal doesn't correctly set the $page flag for the preview on node/add/page which results in the <h2> being displayed in modules/node/node.tpl.php
  if (isset($vars['elements']['#node']->op) && $vars['elements']['#node']->op == 'Preview') {
    $vars['page'] = true;
  }
}


/**
 * Implements template_preprocess_page().
 */
function unl_preprocess_page(&$vars, $hook) {
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
 * Called in html.tpl.php and page.tpl.php.
 */
function unl_get_instance() {
  static $instance;
  if (!$instance) {
    set_include_path(dirname(__FILE__) . '/lib/php' . PATH_SEPARATOR . get_include_path());
    require_once "UNL/Templates.php";
    require_once "UNL/Templates/CachingService/Null.php";

    // Use NULL caching service so templates are pulled from local tpl_cache
    UNL_Templates::setCachingService(new UNL_Templates_CachingService_Null());
    UNL_Templates::$options['version'] = UNL_Templates::VERSION3x1;

    $instance = UNL_Templates::factory('Local');
  }

  return $instance;
}

/**
 * Implements theme_breadcrumb().
 */
function unl_breadcrumb($variables) {
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

/**
 * Implements theme_file_icon().
 * File icons are provided as css background sprites in UNL WDN template project.
 */
function unl_file_icon($variables) {
  return '';
}

function unl_menu_item($link, $has_children, $menu = '', $in_active_trail = FALSE, $extra_class = NULL) {
  if ($extra_class) {
    return '<li class="' . $extra_class . '">' . $link . $menu . '</li>' . "\n";
  }
  else {
    return '<li>' . $link . $menu . '</li>' . PHP_EOL;
  }
}

/**
 * Implements theme_menu_tree().
 */
function unl_menu_tree($variables) {
  $tree = $variables['tree'];
  return '<ul>' . $tree . '</ul>' . PHP_EOL;
}

/**
 * Implements theme_menu_local_tasks().
 */
function unl_menu_local_tasks($variables) {
  $output = '';

  if (!empty($variables['primary'])) {
    $variables['primary']['#prefix'] = '<ul class="wdn_tabs cms_tabs disableSwitching">';
    $variables['primary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['primary']);
  }
  if (!empty($variables['secondary'])) {
    $variables['secondary']['#prefix'] = '<ul class="wdn_tabs cms_tabs disableSwitching">';
    $variables['secondary']['#suffix'] = '</ul>';
    $output .= drupal_render($variables['secondary']);
  }

  return $output;
}

/**
 * Implements theme_menu_local_task().
 */
function unl_menu_local_task($variables) {
  $link = $variables['element']['#link'];
  $link_text = $link['title'];

  if (!empty($variables['element']['#active'])) {
    // If the link does not contain HTML already, check_plain() it now.
    // After we set 'html'=TRUE the link will not be sanitized by l().
    if (empty($link['localized_options']['html'])) {
      $link['title'] = check_plain($link['title']);
    }
    $link['localized_options']['html'] = TRUE;
    $link_text = t('!local-task-title !active', array('!local-task-title' => $link['title'], '!active' => ''));
  }
  return '<li' . (!empty($variables['element']['#active']) ? ' class="selected"' : '') . '>' . l($link_text, $link['href'], $link['localized_options']) . "</li>\n";
}

/**
 * Implements theme_pager().
 */
function unl_pager($variables) {
  // This is straight-copied from the default except with css class names changed and wdn css loaded
  // http://api.drupal.org/api/drupal/includes--pager.inc/function/theme_pager/7
  drupal_add_js('WDN.loadCSS("/wdn/templates_3.0/css/content/pagination.css");', 'inline');

  $tags = $variables['tags'];
  $element = $variables['element'];
  $parameters = $variables['parameters'];
  $quantity = $variables['quantity'];
  global $pager_page_array, $pager_total;

  // Calculate various markers within this pager piece:
  // Middle is used to "center" pages around the current page.
  $pager_middle = ceil($quantity / 2);
  // current is the page we are currently paged to
  $pager_current = $pager_page_array[$element] + 1;
  // first is the first page listed by this pager piece (re quantity)
  $pager_first = $pager_current - $pager_middle + 1;
  // last is the last page listed by this pager piece (re quantity)
  $pager_last = $pager_current + $quantity - $pager_middle;
  // max is the maximum page number
  $pager_max = $pager_total[$element];
  // End of marker calculations.

  // Prepare for generation loop.
  $i = $pager_first;
  if ($pager_last > $pager_max) {
    // Adjust "center" if at end of query.
    $i = $i + ($pager_max - $pager_last);
    $pager_last = $pager_max;
  }
  if ($i <= 0) {
    // Adjust "center" if at start of query.
    $pager_last = $pager_last + (1 - $i);
    $i = 1;
  }
  // End of generation loop preparation.

  $li_first = theme('pager_first', array('text' => (isset($tags[0]) ? $tags[0] : t('« first')), 'element' => $element, 'parameters' => $parameters));
  $li_previous = theme('pager_previous', array('text' => (isset($tags[1]) ? $tags[1] : t('‹ previous')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_next = theme('pager_next', array('text' => (isset($tags[3]) ? $tags[3] : t('next ›')), 'element' => $element, 'interval' => 1, 'parameters' => $parameters));
  $li_last = theme('pager_last', array('text' => (isset($tags[4]) ? $tags[4] : t('last »')), 'element' => $element, 'parameters' => $parameters));

  if ($pager_total[$element] > 1) {
    if ($li_first) {
      $items[] = array(
        'class' => array('pager-first'),
        'data' => $li_first,
      );
    }
    if ($li_previous) {
      $items[] = array(
        'class' => array('pager-previous'),
        'data' => $li_previous,
      );
    }

    // When there is more than one page, create the pager list.
    if ($i != $pager_max) {
      if ($i > 1) {
        $items[] = array(
          'class' => array('ellipsis'),
          'data' => '…',
        );
      }
      // Now generate the actual pager piece.
      for (; $i <= $pager_last && $i <= $pager_max; $i++) {
        if ($i < $pager_current) {
          $items[] = array(
            'class' => array('pager-item'),
            'data' => theme('pager_previous', array('text' => $i, 'element' => $element, 'interval' => ($pager_current - $i), 'parameters' => $parameters)),
          );
        }
        if ($i == $pager_current) {
          $items[] = array(
            'class' => array('selected'),
            'data' => $i,
          );
        }
        if ($i > $pager_current) {
          $items[] = array(
            'class' => array('pager-item'),
            'data' => theme('pager_next', array('text' => $i, 'element' => $element, 'interval' => ($i - $pager_current), 'parameters' => $parameters)),
          );
        }
      }
      if ($i < $pager_max) {
        $items[] = array(
          'class' => array('ellipsis'),
          'data' => '…',
        );
      }
    }
    // End generation.
    if ($li_next) {
      $items[] = array(
        'class' => array('pager-next'),
        'data' => $li_next,
      );
    }
    if ($li_last) {
      $items[] = array(
        'class' => array('pager-last'),
        'data' => $li_last,
      );
    }
    return '<h2 class="element-invisible">' . t('Pages') . '</h2>' . theme('item_list', array(
      'items' => $items,
      'attributes' => array('class' => array('wdn_pagination')),
    ));
  }
}

function unl_status_messages($variables) {
  $display = $variables['display'];

  $output = '';
  foreach (drupal_get_messages($display) as $type => $messages) {
    switch ($type) {
      case 'status':
        $extra_class = ' affirm';
        break;

      case 'warning':
        $extra_class = ' alert';
        break;

      case 'error':
        $extra_class = ' negate';
        break;

      default:
        $extra_class = '';
        break;
    }
    $type = ucfirst($type);
    $output .= <<<EOF
<div class="wdn_notice$extra_class">
    <div class="close">
        <a href="#" title="Close this notice">Close this notice</a>
    </div>
    <div class="message">
        <h4>$type</h4>
EOF;
    if (count($messages) > 1) {
      $output .= '<ul>' . PHP_EOL;
      foreach ($messages as $message) {
        $output .= '<li>' . $message . '</li>' . PHP_EOL;
      }
      $output .= '</ul>' . PHP_EOL;
    }
    else {
      $output .= $messages[0];
    }
    $output .= <<<EOF
    </div>
</div>
EOF;
  }

  if (!$output) {
    return '';
  }

  $output = <<<EOF
<script type="text/javascript">
WDN.initializePlugin('notice');
</script>
$output
EOF;

  return $output;
}
