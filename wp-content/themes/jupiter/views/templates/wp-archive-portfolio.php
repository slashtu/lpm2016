<?php
global $mk_options;

echo do_shortcode('
						[mk_portfolio 
							style =					"' . $mk_options['archive_portfolio_style'] . '" 
							column =		"' . $mk_options['archive_portfolio_column'] . '" 
							height = 			"' . $mk_options['archive_portfolio_image_height'] . '" 
							pagination_style = 		"' . $mk_options['archive_portfolio_pagination_style'] . '"
						]'
				  );