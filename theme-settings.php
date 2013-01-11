<?php
function unl_og_form_system_theme_settings_alter(&$form, &$form_state) {
  global $user;
  
   $form['advanced_settings'] += array(
     'unl_og_base_path' => array(
       '#type' => 'textfield',
       '#title' => t('Base breadcrumb path'),
       '#default_value' => theme_get_setting('unl_og_base_path'),
       '#description' => t("Will create a breadcrumb after the UNL breadcrumb if the current context does not have a group'.  Example: node/1 <br /> To enable a menu for non-group pages, create an og_menu and enable the menu for '[gid:0]'")
     ),
    );
}