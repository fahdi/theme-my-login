<div class="login" id="theme-my-login<?php $template->the_instance(); ?>">
	<?php if ( $template->options['show_gravatar'] ) : ?>
	<div class="tml-user-avatar"><?php $template->the_user_avatar(); ?></div>
	<?php endif; ?>

	<?php $this->the_user_links(); ?>
</div>