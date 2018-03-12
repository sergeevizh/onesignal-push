<?php
/*
Plugin Name: Push notifications
Description: Push notifications and popup management plugin
Author: Anton Lukin
Author URI: https://lukin.me
Version: 0.3.0
Text Domain: onesignal-push
*/

if (!defined('WPINC')) {
	die;
}

new Onesignal_Push;

class Onesignal_Push {

  /**
   * Post meta used for mark sent posts
   */
  private $meta   = '_onesignal-push';

  /**
   * Settings option meta
   */
  private $option = 'onesignal-push-settings';

  /**
   * Plugin version used for static cache
   */
  private $static = '0.3.0';


	public function __construct() {
		// plugin localization
		add_action('plugins_loaded', [$this, 'plugin_textdomain']);

    // init admin hooks
		add_action('admin_init', [$this, 'admin_init']);

 		// init plugin settings
 		add_action('admin_menu', [$this, 'settings_init']);

		// init admin menu
		add_action('admin_menu', [$this, 'admin_menu']);

		// add link to settings from plugin row meta
 		add_filter('plugin_row_meta', [$this, 'plugin_row'], 10, 2);

		// include popup script and styles
		add_action('wp_enqueue_scripts', [$this, 'include_static']);

		// include popup template
		add_action('wp_footer', [$this, 'include_footer']);
	}


	/**
	 * Plugin localization
	 */
	public function plugin_textdomain() {
		load_plugin_textdomain('onesignal-push', FALSE, basename(__DIR__) . '/lang/');
	}


	/**
	 * Set permissions to send pushes
	 */
 	public function admin_init() {
		$user_role = apply_filters('onesignal-push_user_role', 'edit_others_posts');

		if(!current_user_can($user_role))
			return false;

		// post metabox
		add_action('add_meta_boxes', [$this, 'admin_metabox']);

    // ajax handler
    add_action('wp_ajax_onesignal_push', [$this, 'send_push']);
	}


	/**
	 * Extra hook to check if OneSignal App Id set before injecting popup
	 */
	public function include_static() {
 		$opts = get_option($this->option);

		if(empty($opts['appid'])) {
			return;
		}

		// enqueue custom popup styles
		wp_enqueue_style('onesignal-push', plugins_url('assets/onesignal-push.css', __FILE__), [], $this->static);

 		// enqueue custom popup script
		wp_enqueue_script('onesignal-push', plugins_url('assets/onesignal-push.js', __FILE__), [], $this->static, true);
		wp_localize_script('onesignal-push', 'onesignal_push_id', $opts['appid']);
	}


	/**
	 * Add custom popup template to footer
	 */
	public function include_footer() {
 		$opts = get_option($this->option);

		if(empty($opts['appid'])) {
			return;
		}

		// filter allow to include another template
    $template = apply_filters('onesignal-push_footer', plugin_dir_path(__FILE__) . 'views/footer.php');

 		include_once($template);
	}


	/**
	 * Add plugin settings page
	 */
	public function admin_menu() {
		add_submenu_page(
			null,
			__('Push settings', 'onesignal-push'),
			__('Push settings', 'onesignal-push'),
			'manage_options',
			'onesignal-push',
			[$this, 'settings_page']
		);
	}


  /**
	 * Add link to settings page inside plugin row meta
	 */
	public function plugin_row($meta, $file) {
		if(plugin_basename(__FILE__) !== $file) {
			return $meta;
		}

		$meta[] = sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url('options-general.php?page=onesignal-push'),
			__('Settings', 'onesignal-push')
		);

		return $meta;
	}


  /**
   * Render settings page view
   */
  public function settings_page() {
  	include_once(plugin_dir_path(__FILE__) . 'views/settings.php');
	}


	/**
	 * Init settings
	 */
	public function settings_init() {
		register_setting('onesignal-push-settings', $this->option);

		add_settings_section(
			'onesignal-push-section',
			__('Push settings', 'onesignal-push'),
			[],
			'onesignal-push-settings'
		);

		add_settings_field(
			'appid',
			__('OneSignal App ID', 'onesignal-push'),
			[$this, 'setting_render_appid'],
			'onesignal-push-settings',
			'onesignal-push-section'
		);

		add_settings_field(
			'rest',
			__('REST API Key', 'onesignal-push'),
 			[$this, 'setting_render_rest'],
			'onesignal-push-settings',
			'onesignal-push-section'
		);

		add_settings_field(
			'segments',
			__('Included Segments', 'onesignal-push'),
 			[$this, 'setting_render_segments'],
			'onesignal-push-settings',
			'onesignal-push-section'
		);

		add_settings_field(
			'title',
			__('Default push title', 'onesignal-push'),
 			[$this, 'setting_render_title'],
			'onesignal-push-settings',
			'onesignal-push-section'
		);

 		add_settings_field(
			'utm',
			__('URL paramaters', 'onesignal-push'),
 			[$this, 'setting_render_utm'],
			'onesignal-push-settings',
			'onesignal-push-section'
		);
	}


	public function setting_render_appid() {
		$options = get_option($this->option);
		$default = isset($options['appid']) ? $options['appid'] : '';

		printf(
			'<input type="text" name="%1$s[appid]" class="widefat" value="%2$s">',
			$this->option,
			esc_attr($default)
		);
	}


	public function setting_render_rest() {
		$options = get_option($this->option);
 		$default = isset($options['rest']) ? $options['rest'] : '';

		printf(
			'<input type="text" name="%1$s[rest]" class="widefat" value="%2$s">',
 			$this->option,
			esc_attr($default)
		);
	}


	public function setting_render_segments() {
		$options = get_option($this->option);
  		$default = isset($options['segments']) ? $options['segments'] : '';

		printf(
			'<input type="text" name="%1$s[segments]" placeholder="All" class="widefat" value="%2$s">',
 			$this->option,
			esc_attr($default)
		);
	}


	public function setting_render_title() {
		$options = get_option($this->option);
  		$default = isset($options['title']) ? $options['title'] : '';

		printf(
			'<input type="text" name="%1$s[title]" class="widefat" value="%2$s">',
 			$this->option,
			esc_attr($default)
		);
	}


 	public function setting_render_utm() {
		$options = get_option($this->option);
  		$default = isset($options['utm']) ? $options['utm'] : '';

		printf(
			'<input type="text" name="%1$s[utm]" placeholder="utm_source=site&utm_medium=webpush" class="widefat" value="%2$s">',
 			$this->option,
			esc_attr($default)
		);
	}


	/**
	 * Init post push metabox
	 */
	public function admin_metabox() {
		add_meta_box('onesignal-push', __('Send webpush', 'onesignal-push'), [$this, 'display_metabox'], 'post', 'side', 'low');
	}


	/**
	 * Display post push metabox
	 */
	public function display_metabox() {
		include_once(plugin_dir_path(__FILE__) . 'views/metabox.php');
	}


	/**
	 * Ajax handler for push sending
	 */
	public function send_push() {
		$id = $_POST['post'];

		if(!is_numeric($id))
			wp_send_json_error(__("Something wrong with post id", 'onesignal-push'));

		$opts = get_option($this->option);

		if(empty($opts['appid']) || empty($opts['rest']))
			wp_send_json_error(__("You should fill options on settings page", 'onesignal-push'));

		if(empty($opts['segments']))
			$opts['segments'] = 'All';

		parse_str($opts['utm'], $args);

		$fields = [
			'app_id' => $opts['appid'],

			'included_segments' => explode(",", $opts['segments']),

			'contents' => [
				'en' => esc_html($_POST['message'])
			],

			'headings' => [
				'en' => esc_html($_POST['title'])
			],

			'url' => add_query_arg($args, get_permalink($id))
		];

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
			wp_send_json_error(__("Webpush not sent. Something went wrong", 'onesignal-push'));

		update_post_meta($id, $this->meta, $answer->id);

		wp_send_json_success(__("Webpush successfully sent", 'onesignal-push'));
	}
}
