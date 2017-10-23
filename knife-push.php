<?php
/*
Plugin Name: Webpush manage
Description: Webpush and popup management plugin
Author: Anton Lukin
Author URI: https://lukin.me
Version: 0.1
Text Domain: knife-push
*/

if (!defined('WPINC')) {
	die;
}

new Knife_Push;

class Knife_Push {
	function __construct() {
		add_action('init', [$this, 'init']);

		add_action('admin_init', [$this, 'admin_init']);
		add_action('admin_menu', [$this, 'add_menu']);
 		add_action('admin_menu', [$this, 'settings_init']);

		add_action('plugins_loaded', [$this, 'plugin_textdomain']);
	}

	public function init() {

		add_action('wp_enqueue_scripts', [$this, 'enqueue_script']);
		add_action('wp_enqueue_scripts', [$this, 'enqueue_style']);
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

	public function enqueue_script() {
		wp_enqueue_script('onesignal-sdk', 'https://cdn.onesignal.com/sdks/OneSignalSDK.js');
	}

 	public function enqueue_style() {
		$default_style = apply_filters('knife-push_enqueue_style', true);

		if($default_style === true) {
			wp_enqueue_style('knife-push', plugins_url("assets/knife-styles.css", __FILE__), [], null);
		}
	}

	public function loader_tag($tag, $handle) {
		if('onesignal-sdk' !== $handle)
			return $tag;

		return str_replace(' src', ' async src', $tag);
	}

	public function footer() {
		$opts = get_option('knife_push_settings');
    	$template = apply_filters('knife-push_footer', plugin_dir_path(__FILE__) . "views/footer.php");

		$vars = [
			'promo' => apply_filters('knife-push_footer_promo', __('We want to notify you for the lastest updates', 'knife-push')),
			'button' => apply_filters('knife-push_footer_button', __('Subscribe', 'knife-push')),
			'appid' => $opts['appid']
		];

 		include($template);
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
			'appid',
			__( 'OneSignal App ID', 'knife-push' ),
			[$this, 'setting_render_appid'],
			'knife_push',
			'knife_push_plugin_section'
		);

		add_settings_field(
			'rest',
			__( 'REST API Key', 'knife-push' ),
 			[$this, 'setting_render_rest'],
			'knife_push',
			'knife_push_plugin_section'
		);

		add_settings_field(
			'segments',
			__( 'Included Segments', 'knife-push' ),
 			[$this, 'setting_render_segments'],
			'knife_push',
			'knife_push_plugin_section'
		);

		add_settings_field(
			'title',
			__( 'Default push title', 'knife-push' ),
 			[$this, 'setting_render_title'],
			'knife_push',
			'knife_push_plugin_section'
		);

 		add_settings_field(
			'utm',
			__( 'URL paramaters', 'knife-push' ),
 			[$this, 'setting_render_utm'],
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
		<input type="text" name="knife_push_settings[appid]" class="widefat" value="<?php echo $options['appid']; ?>">
		<?php

	}

	public function setting_render_rest() {
		$options = get_option('knife_push_settings');
		?>
		<input type="text" name="knife_push_settings[rest]" class="widefat" value="<?php echo $options['rest']; ?>">
		<?php
	}


	public function setting_render_segments() {
		$options = get_option('knife_push_settings');
		?>
		<input type="text" name="knife_push_settings[segments]" placeholder="All" class="widefat" value="<?php echo $options['segments']; ?>">
		<?php
	}

	public function setting_render_title() {
		$options = get_option('knife_push_settings');
		?>
		<input type="text" name="knife_push_settings[title]" placeholder="My best site" class="widefat" value="<?php echo $options['title']; ?>">
		<?php
	}

 	public function setting_render_utm() {
		$options = get_option('knife_push_settings');
		?>
		<input type="text" name="knife_push_settings[utm]" placeholder="utm_source=site&utm_medium=webpush" class="widefat" value="<?php echo $options['utm']; ?>">
		<?php
	}

	public function add_metabox() {
		$user_role = apply_filters('knife-push_user_role', 'edit_others_posts');

		if(!current_user_can($user_role))
			return false;

		add_meta_box('knife-push', __('Send webpush', 'knife-push'), [$this, 'display_metabox'], 'post', 'side', 'low');
	}

	public function display_metabox() {
		include(plugin_dir_path(__FILE__) . "views/metabox.php");
	}

	public function ajax_push() {
		$id = $_POST['post'];

		if(!is_numeric($id))
			wp_send_json_error(__("Something wrong with post id", 'knife-push'));

		$opts = get_option('knife_push_settings');

		if(empty($opts['appid']) || empty($opts['rest']))
			wp_send_json_error(__("You should fill options on settings page", 'knife-push'));

		if(empty($opts['segments']))
			$opts['segments'] = 'All';

		parse_str($opts['utm'], $args);

		$fields = array(
			'app_id' => $opts['appid'],

			'included_segments' => explode(",", $opts['segments']),

			'contents' => [
				'en' => $_POST['message']
			],

			'headings' => [
				'en' => $_POST['title']
			],

			'url' => add_query_arg($args, get_permalink($id))
		);

		$header = [
			'Content-Type: application/json; charset=utf-8',
			'Authorization: Basic ' . $opts['rest']
		];

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_POST, TRUE);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

		$response = curl_exec($ch);
		curl_close($ch);

		$answer = json_decode($response);

		if(!isset($answer->id))
			wp_send_json_error(__("Webpush not sent. Something went wrong", 'knife-push'));

		update_post_meta($id, 'knife-push', $answer->id);

		wp_send_json_success(__("Webpush successfully sent", 'knife-push'));
	}
}
