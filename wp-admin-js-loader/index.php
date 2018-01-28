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
  foreach (array_map(
    ['WpAdminJsLoader', 'separateSrcAndDeps'],
    explode("\n", get_option(WpAdminJsLoader::$fieldId))
  ) as $i => $line) {
    $src = WpAdminJsLoader::optimizeSpaces($line['src']);
    if ($src === '') continue;
    wp_enqueue_script(
      "wpajl-admin-custom-{$i}",
      $src,
      // Remove empty string
      array_values(array_filter(array_map(
        ['WpAdminJsLoader', 'optimizeSpaces'],
        explode(',', $line['deps'])
      ), 'strlen'))
    );
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
    echo "<textarea name=\"$id\" id=\"$id\" rows=\"2\" class=\"large-text code\">$value</textarea>";
  }
  // Separate to a src url and a deps strings from a line of textarea
  static public function separateSrcAndDeps($line)
  {
    $posOpening = strpos($line, '[');
    $posClosing = strpos($line, ']');
    if (
      $posOpening === false || $posClosing === false ||
      ($posClosing - $posOpening) <= 0
    ) return ['src' => $line, 'deps' => ''];
    $separated = explode('[', str_replace(']', '', $line));
    return [
      'src' => $separated[0],
      'deps' => isset($separated[1]) ? $separated[1] : ''
    ];
  }
  // Trim and Encode spaces
  static public function optimizeSpaces($url)
  {
    return str_replace(' ', '%20', trim($url));
  }
}
