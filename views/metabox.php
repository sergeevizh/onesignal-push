 <div id="knife-push-box" data-ajaxurl="<?php echo admin_url('admin-ajax.php') ?>" data-post="<?php echo get_the_ID() ?>">
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

	<a id="knife-push-send" href="#push-send" class="button"><?php _e('Send', 'knife-push') ?></a>
	<span class="spinner"></span>
</div>

<script>
	jQuery(document).ready(function($) {
		var box = $("#knife-push-box");

		var wait = function() {
			box.find('.button').toggleClass('disabled');
			box.find('.spinner').toggleClass('is-active');
		}

		box.on('click', '#knife-push-send', function(e) {
			e.preventDefault();
			box.find(".notice").remove();

			var data = {
				action: 'knife_push',
				post: box.data('post'),
				title: box.find('#knife-push-title').val(),
				message: box.find('#knife-push-message').val()
			}

			var xhr = $.ajax({method: 'POST', url: box.data('ajaxurl'), data: data}, 'json');

			xhr.done(function(answer) {
				wait();

				var status = $('<div />', {
					"class": "notice",
					"html": "<p>" + answer.data + "</p>",
					"css": {"background-color": "#f4f4f4"}
				});

				if(answer.success === true)
					return status.prependTo(box).addClass('notice-success');

				return status.prependTo(box).addClass('notice-error');
			});

			return wait();
		});
	});
</script>
