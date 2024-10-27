<div class="wrap about-wrap" id="am_wrap">
	<h1><?php _e( 'Ad Kangaroo', 'ad_kangaroo' ); ?></h1>
	<p class="about-text">Ad Kangaroo is now installed and ready to use! Get ready to intensify your AdSense. We hope you'll enjoy it! We hope you will enjoy using Ad Kangaroo, as much as we enjoy creating great products.<br><br></p>
	<div class="wp-badge ad_kangaroo-logo"></div>
	<h2 class="nav-tab-wrapper wp-clearfix">
		<?php $this->render_tabs() ?>
	</h2>
	<div class="container">
		<?php

		switch ( $this->current_tab ) {
			case 'home' :
			case 'pages' :
			case 'posts' :
			case 'categories_tags' :
			case 'widget' :
				require_once( dirname( __FILE__ ) . '/ads-list.php' );
				break;
			
			default:
				require_once( dirname( __FILE__ ) . '/settings.php' );
				break;
		}

		?>
		
	</div>
</div>