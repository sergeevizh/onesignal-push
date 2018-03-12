 <div id="onesignal-push-box" data-ajaxurl="<?php echo admin_url('admin-ajax.php') ?>" data-post="<?php echo get_the_ID() ?>" style="margin-bottom: -10px;">
	<?php
        $opts = get_option($this->option);
        $push = get_post_meta(get_the_ID(), $this->meta, true);

		$title = empty($opts['title']) ? __('New article', 'onesignal-push') : $opts['title'];
	?>

	<?php if(!empty($push)) : ?>
	<p class="howto">
		<?php _e('<strong>Note:</strong> Push for this post already sent', 'onesignal-push') ?>
	</p>
	<?php endif; ?>

 	<p>
		<label for="onesignal-push-title"><strong><?php _e('Title', 'onesignal-push') ?></strong></label>
		<input id="onesignal-push-title" class="widefat" value="<?php echo $title ?>">
	</p>

	<p>
		<label for="onesignal-push-message"><strong><?php _e('Message', 'onesignal-push') ?></strong></label>
		<textarea id="onesignal-push-message" class="widefat" rows="4"><?php echo get_the_title() ?></textarea>
	</p>

	<p>
		<a id="onesignal-push-send" href="#push-send" class="button"><?php _e('Send', 'onesignal-push') ?></a>
		<span class="spinner"></span>
	</p>
</div>

<script>
    jQuery(document).ready(function($) {
        var box = $("#onesignal-push-box");

        var wait = function() {
            box.find('.button')
                .toggleClass('disabled')
                .prop('disabled', function(i, v) {return !v});

            box.find('.spinner').toggleClass('is-active');
        }

        box.on('click', '#onesignal-push-send', function(e) {
            e.preventDefault();
            box.find(".notice").remove();

            var send = $(this).parent();

            var data = {
                action: 'onesignal_push',
                post: box.data('post'),
                title: box.find('#onesignal-push-title').val(),
                message: box.find('#onesignal-push-message').val()
            }

            var xhr = $.ajax({method: 'POST', url: box.data('ajaxurl'), data: data}, 'json');

            xhr.done(function(answer) {
                wait();

                var status = $('<div />', {
                    "class": "notice",
                    "html": "<p>" + answer.data + "</p>",
                    "css": {"background-color": "#f4f4f4", "margin-top": "15px"}
                });

                if(answer.success !== true)
                    return status.prependTo(box).addClass('notice-error');

                send.hide();

                return status.prependTo(box).addClass('notice-success');
            });

            return wait();
        });
    });
</script>
