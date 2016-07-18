<?php

/**
 * template part for blog single about author single.php. views/blog/components
 *
 * @author      Artbees
 * @package     jupiter/views
 * @version     5.0.0
 */

global $mk_options;

 if($mk_options['enable_blog_author'] == 'true' && get_post_meta( $post->ID, '_disable_about_author', true ) != 'false') : ?>
	<div class="mk-about-author-wrapper" <?php echo get_schema_markup('author_box'); ?>>
		<div class="mk-about-author-meta">
			<?php if (mk_get_blog_single_style() != 'bold') : ?>
			<div class="avatar-wrapper"><?php global $user; echo get_avatar( get_the_author_meta('email'), '65',false ,get_the_author_meta('display_name', $user['ID'])); ?></div>
			<?php endif; ?>
			<?php if (mk_get_blog_single_style() == 'bold') : ?>
			<div class="about-author-title"><?php _e('About', 'mk_framework')?></div>
			<?php endif; ?>
			<a class="about-author-name" href="<?php echo get_author_posts_url(get_the_author_meta( 'ID' )); ?>"><?php the_author_meta('display_name'); ?></a>
			<div class="about-author-desc"><?php the_author_meta('description'); ?></div>
			<ul class="about-author-social">

				<?php if(get_the_author_meta( 'twitter' )) { ?>
					<li><a class="twitter-icon" title="<?php _e('Follow me on Twitter','mk_framework'); ?>" href="<?php echo get_the_author_meta( 'twitter' ) ;?>" target="_blank"><?php Mk_SVG_Icons::get_svg_icon_by_class_name(true, 'mk-moon-twitter'); ?></a></li>
				<?php } ?>

				<?php if(get_the_author_meta('email')) { ?>
					<li><a class="email-icon" title="<?php _e('Get in touch with me via email','mk_framework'); ?>" href="mailto:<?php echo get_the_author_meta('email'); ?>" target="_blank"><?php Mk_SVG_Icons::get_svg_icon_by_class_name(true, 'mk-moon-envelop'); ?></a></li>
				<?php } ?>

				<?php if(get_the_author_meta( 'facebook' )) { ?>
				   <li><a class="facebook-icon" title="<?php _e('Follow me on Facebook','mk_framework'); ?>" href="<?php echo get_the_author_meta( 'facebook' ); ?>" target="_blank"><?php Mk_SVG_Icons::get_svg_icon_by_class_name(true, 'mk-moon-facebook'); ?></a></li>
				<?php } ?>

				<?php if(get_the_author_meta( 'googleplus' )) { ?>
				   <li><a class="googleplus-icon" title="<?php _e('Follow me on Google+','mk_framework'); ?>" href="<?php echo get_the_author_meta( 'googleplus' ); ?>" target="_blank"><?php Mk_SVG_Icons::get_svg_icon_by_class_name(true, 'mk-moon-google-plus'); ?></a></li>
				<?php } ?>
			</ul>
		</div>
		<div class="clearboth"></div>
	</div>
<?php endif; ?>