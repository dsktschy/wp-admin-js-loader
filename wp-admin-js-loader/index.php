<?php
/*
Plugin Name: WP Admin JS Loader
Plugin URI: https://github.com/dsktschy/wp-admin-js-loader
Description: WP Admin JS Loader loads the JS files for admin pages.
Version: 1.0.0
Author: dsktschy
Author URI: https://github.com/dsktschy
License: GPL2
*/

// Add fields to the setting page
add_filter('admin_init', function() {
  add_settings_field(
    WpAdminJsLoader::$fieldId,
    preg_match('/^ja/', get_option('WPLANG')) ?
      '管理画面で読み込むJSファイルのURL' :
      'URLs of JS files to link on admin pages',
    ['WpAdminJsLoader', 'echoField'],
    WpAdminJsLoader::$fieldPage,
    'default',
    ['id' => WpAdminJsLoader::$fieldId]
  );
  register_setting(WpAdminJsLoader::$fieldPage, WpAdminJsLoader::$fieldId);
});

// Load the JS files for admin pages if specified
add_action('admin_enqueue_scripts', function() {
  $option = get_option(WpAdminJsLoader::$fieldId);
  if ($option === '') return;
  foreach (array_map(
    ['WpAdminJsLoader', 'encodeSpace'],
    array_map('trim', explode(',', $option))
  ) as $i => $url) {
    if ($url === '') continue;
    wp_enqueue_script("wpajl-admin-custom-{$i}", $url);
  }
});

// Class as a namespace
class WpAdminJsLoader
{
  static public $fieldId = 'wp_admin_js_loader';
  static public $fieldPage = 'general';
  // Outputs an input element with initial value
  static public function echoField(array $args)
  {
    $id = $args['id'];
    $value = esc_html(get_option($id));
    echo "<input name=\"$id\" id=\"$id\" type=\"text\" value=\"$value\" class=\"regular-text code\">";
  }
  // Encode spaces
  static public function encodeSpace($url)
  {
    return str_replace(' ', '%20', $url);
  }
}
