<?php
/*
Plugin Name: [Нож] Управление пушами
Description: Плагин управления браузерными уведомлениями
Author: Anton Lukin
Author URI: https://lukin.me
Version: 0.1
Text Domain: knife-push
*/

new Knife_Push;

class Knife_Push {
	function __construct() {
		if($_SERVER['REMOTE_ADDR'] !== '176.14.209.11')
			return false;

		add_action('init', [$this, 'init']);

		add_action('admin_init', [$this, 'admin_init']);
		add_action('admin_menu', [$this, 'add_menu']);
 		add_action('admin_menu', [$this, 'settings_init']);

		add_action('plugins_loaded', [$this, 'plugin_textdomain']);
	}

	public function init() {
		add_action('wp_enqueue_scripts', [$this, 'enqueue_scripts']);
		add_filter('script_loader_tag', [$this, 'loader_tag'], 10, 2);
		add_action('wp_footer', [$this, 'footer']);
	}

 	public function admin_init() {
		add_action('add_meta_boxes', [$this, 'add_metabox']);
		add_action('wp_ajax_knife_push', [$this, 'ajax_push']);
 		add_filter('plugin_row_meta', [$this, 'plugin_row'], 10, 4);
	}

	public function plugin_textdomain() {
		load_plugin_textdomain('knife-push', FALSE, basename(__DIR__) . '/lang/');
	}

	public function enqueue_scripts() {
		wp_enqueue_script('onesignal-sdk', 'https://cdn.onesignal.com/sdks/OneSignalSDK.js');
	}

	public function loader_tag($tag, $handle) {
		if('onesignal-sdk' !== $handle)
			return $tag;

		return str_replace(' src', ' async src', $tag);
	}

	public function footer() {
 		include(plugin_dir_path(__FILE__) . "views/footer.php");
	}

	public function add_menu() {
		add_submenu_page(null, __('Push settings', 'knife-push'), __('Push settings', 'knife-push'), 'manage_options', 'knife-push', [$this, 'options_page']);
	}

	public function plugin_row($plugin_meta, $plugin_file, $plugin_data, $status) {
		if (plugin_basename(__FILE__) == $plugin_file)
			$plugin_meta[] = '<a href="' . admin_url('options-general.php?page=knife-push') . '">' . __('Settings', 'knife-push'). '</a>';

		return $plugin_meta;
	}

	public function settings_init() {
		register_setting( 'knife_push', 'knife_push_settings' );

		add_settings_section(
			'knife_push_plugin_section',
			__( 'Push settings', 'knife-push' ),
			[],
			'knife_push'
		);

		add_settings_field(
			'knife_push_appid',
			__( 'OneSignal App ID', 'knife-push' ),
			[$this, 'setting_render_appid'],
			'knife_push',
			'knife_push_plugin_section'
		);

		add_settings_field(
			'knife_push_rest',
			__( 'REST API Key', 'knife-push' ),
 			[$this, 'setting_render_rest'],
			'knife_push',
			'knife_push_plugin_section'
		);

		add_settings_field(
			'knife_push_segments',
			__( 'Included Segments', 'knife-push' ),
 			[$this, 'setting_render_segments'],
			'knife_push',
			'knife_push_plugin_section'
		);
	}

	public function options_page() {
  		include(plugin_dir_path(__FILE__) . "views/settings.php");
	}

	public function setting_render_appid() {
		$options = get_option('knife_push_settings');
		?>
		<input type="text" name="knife_push_settings[knife_push_appid]" class="widefat" value="<?php echo $options['knife_push_appid']; ?>">
		<?php

	}

	public function setting_render_rest() {
		$options = get_option('knife_push_settings');
		?>
		<input type="text" name="knife_push_settings[knife_push_rest]" class="widefat" value="<?php echo $options['knife_push_rest']; ?>">
		<?php
	}


	public function setting_render_segments() {
		$options = get_option('knife_push_settings');
		?>
		<input type="text" name="knife_push_settings[knife_push_segments]" class="widefat" value="<?php echo $options['knife_push_segments']; ?>">
		<?php
	}

	public function add_metabox() {
		if(!current_user_can('edit_pages'))
			return false;

		add_meta_box('knife-push', __('Send webpush', 'knife-push'), [$this, 'display_metabox'], 'post', 'side', 'low');
	}

	public function display_metabox() {
		include(plugin_dir_path(__FILE__) . "views/metabox.php");
	}

	public function ajax_push() {
		$options = get_option('knife_push_settings');

		$content = [
			'en' => $_POST['message']
		];

		$fields = array(
			'app_id' => $options['knife_push_appid'],
			'included_segments' => array($options['knife_push_segments']),
			'contents' => $content
		);

		$fields = json_encode($fields);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8',
											   'Authorization: Basic ' . $options['knife_push_rest']));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);

		print_r($response);
		die;

		wp_send_json_success(__('Successfully sent', 'knife-push'));
	}
}
