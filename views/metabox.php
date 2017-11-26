 <div id="knife-push-box" data-ajaxurl="<?php echo admin_url('admin-ajax.php') ?>" data-post="<?php echo get_the_ID() ?>" style="margin-bottom: -10px;">
	<?php
		$opts = get_option('knife_push_settings');
		$push = get_post_meta(get_the_ID(), 'knife-push', true);

		$title = empty($opts['title']) ? __('New article', 'knife-push') : $opts['title'];
	?>

	<?php if(!empty($push)) : ?>
	<p class="howto">
		<?php _e('<strong>Note:</strong> Push for this post already sent', 'knife-push') ?>
	</p>
	<?php endif; ?>

 	<p>
		<label for="knife-push-title"><strong><?php _e('Title', 'knife-push') ?></strong></label>
		<input id="knife-push-title" class="widefat" value="<?php echo $title ?>">
	</p>

	<p>
		<label for="knife-push-message"><strong><?php _e('Message', 'knife-push') ?></strong></label>
		<textarea id="knife-push-message" class="widefat" rows="4"><?php echo get_the_title() ?></textarea>
	</p>

	<p>
		<a id="knife-push-send" href="#push-send" class="button"><?php _e('Send', 'knife-push') ?></a>
		<span class="spinner"></span>
	</p>
</div>
