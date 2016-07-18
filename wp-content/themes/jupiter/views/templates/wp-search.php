

<section class="mk-search-loop">

<section class="widget widget_search"><p><?php _e('Not so happy with results? Search for a new keyword ', 'mk_framework'); ?></p>
	<form class="mk-searchform" method="get" id="searchform" action="<?php echo home_url(); ?>">
		<input type="text" class="text-input" placeholder="<?php _e('Search site', 'mk_framework'); ?>" value="" name="s" id="s" />
		<i class="mk-searchform-icon"><?php Mk_SVG_Icons::get_svg_icon_by_class_name(true,'mk-icon-search'); ?><input value="" class="search-button" type="submit" /></i>
	</form>
</section>

<?php

	if ( have_posts() ):
		while ( have_posts() ) :
			the_post();

			$post_type =  get_post_type();
			?>

				<article class="search-result-item">
					
					<h4 class="the-title"><a href="<?php echo get_permalink(); ?>"><?php the_title(); ?></a></h4>

					<div class="search-loop-meta">
						<span><?php _e('By', 'mk_framework'); ?> <?php the_author_posts_link(); ?></span>

							<time datetime="<?php the_time('Y-m-d'); ?>">
			
								<?php the_date('', '<time datetime="'.get_the_time().'">'.__('On', 'mk_framework').' <a href="'.get_month_link( get_the_time( "Y" ), get_the_time( "m" ) ).'">', '</a></time>'); ?>
								</a>
							</time>
						<?php
							echo '<span class="mk-search-cats">';
								switch ($post_type) {
		
									case 'post':
											echo __('In', 'mk_framework').' '.get_the_category_list( ', ' );
										break;
									case 'portfolio':
											echo __('In', 'mk_framework').' '.implode(', ', mk_get_custom_tax(get_the_id(), 'portfolio', true));
										break;	
									case 'news':
											echo __('In', 'mk_framework').' '.implode(', ', mk_get_custom_tax(get_the_id(), 'news', true));
										break;			
								}
							echo '</span>';
						?>
					</div>



					<div class="the-excerpt"><p><?php mk_excerpt_max_charlength(200) ?></p></div>
				</article>

			

<?php
			$post_type = '';
		endwhile;
	

	mk_post_pagination(NULL);

	wp_reset_query();

	endif;
		

		
	?>

</section>
			