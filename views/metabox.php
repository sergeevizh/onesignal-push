 <div id="knife-push-box" data-ajaxurl="<?php echo admin_url('admin-ajax.php') ?>" data-post="<?php echo get_the_ID() ?>">
	<?php $push = get_post_meta(get_the_ID(), 'knife-push', true); ?> 

	<?php if(!empty($push)) : ?>
	<p class="howto">
		<strong>Note: </strong><span><?php _e('Push for this post already sent', 'knife-push') ?></span>
	</p>   
	<?php endif; ?>

	<p>
		<span id="knife-push-status"></span>
	</p>

 	<p>
		<label for="knife-push-title"><strong><?php _e('Title', 'knife-push') ?></strong></label>
		<input id="knife-push-title" class="widefat" value="<?php echo get_the_title() ?>">
	</p>

	<p>
		<label for="knife-push-description"><strong><?php _e('Message', 'knife-push') ?></strong></label>      
		<textarea id="knife-push-description" class="widefat" rows="4"></textarea>
	</p>

	<a id="knife-push-send" href="#push-push" class="button"><?php _e('Send', 'knife-push') ?></a>
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

			var data = {
				action: 'knife_push',
				post: box.data('post'),
				title: box.find('#knife-push-title').val(),
				description: box.find('#knife-push-description').val()
			}

			var xhr = $.ajax({method: 'POST', url: box.data('ajaxurl'), data: data}, 'json');

			xhr.done(function(answer) {
				wait();

//				if(answer.success === true)
					
				return box.find('#knife-push-status').text(answer.data)
			});    

			return wait();
		});          
	});
</script>
