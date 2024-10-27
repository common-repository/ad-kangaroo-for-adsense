<div>
	<div id="ad_kangaroo_usage_notice">
		<p><?php printf( '<strong>%s</strong> %s <a href="https://support.google.com/adsense/answer/1346295?hl=en#Ad_limit_per_page" target="_blank">%s</a>.', __( 'Please note:', 'ad_kangaroo' ), __( 'The maximum number of ad blocks on the page cannot be more than 3 ad blocks.', 'ad_kangaroo' ), __( 'Learn more', 'ad_kangaroo' ) ); ?></p>
		<?php if ( $this->current_tab == 'widget' ) { ?>
			<p><?php printf( __( "Please don't forget to place the AdSense widget into a needed sidebar on the %s.", 'ad_kangaroo' ), sprintf( '<a href="widgets.php" target="_blank">%s</a>', __( 'widget page', 'ad_kangaroo' ) ) );?></p>
		<?php } ?>
		<p>
			<?php printf( __( 'Add or manage existing ad blocks in the %s.', 'ad_kangaroo' ), sprintf( '<a href="https://www.google.com/adsense/app#main/myads-viewall-adunits" target="_blank">%s</a>', __( 'Google AdSense', 'ad_kangaroo' ) ) ); ?><br />
			<span><?php printf( __( 'After adding the ad block in Google AdSense, please %s to see the new ad block in the list of plugin ad blocks.', 'ad_kangaroo' ), sprintf( '<a href="admin.php?page=adsense-manager&ad_kangaroo_tab=%s">%s</a>', $this->current_tab, __( 'reload the page', 'ad_kangaroo' ) ) ) ; ?></span>
		</p>
	</div>
	<?php if ( isset( $this->ad_kangaroo_ads[ $this->ad_kangaroo_options['publisher_id'] ][ $this->current_tab ] ) ) {
		$ad_kangaroo_table_adunits = $this->ad_kangaroo_ads[ $this->ad_kangaroo_options['publisher_id'] ][ $this->current_tab ];
	}
	require_once( dirname( __FILE__ ) . '/../class.ad_kangaroo.list_table.php' );
	$ad_kangaroo_lt = new Ad_Kangaroo_Ads_List( $this->ad_kangaroo_options );
	$ad_kangaroo_lt->ad_kangaroo_table_data = $ad_kangaroo_table_data;
	$ad_kangaroo_lt->ad_kangaroo_table_adunits = ( isset( $ad_kangaroo_table_adunits ) && is_array( $ad_kangaroo_table_adunits ) ) ? $ad_kangaroo_table_adunits : array();
	$ad_kangaroo_lt->ad_kangaroo_adunit_positions = array(
			'disable'          => __( 'Disabled', 'ad_kangaroo' ),
			'before'           => __( 'Before the content', 'ad_kangaroo' ),
			'after'            => __( 'After the content', 'ad_kangaroo' )
		);

	if ( $this->current_tab == 'widget' ) {
		$ad_kangaroo_lt->ad_kangaroo_adunit_positions = array(
			'disable'          => __( 'Disabled', 'ad_kangaroo' ),
			'enable'           => __( 'Enabled', 'ad_kangaroo' ),
		);
	}

	$ad_kangaroo_lt->prepare_items();
	echo '<div class="ad_kangaroo-ads-list">';
		$ad_kangaroo_lt->display();
		echo '<div class="spinner"></div>';
	echo "</div>";
	wp_nonce_field( $this->ad_kangaroo_nonce, 'ad_kangaroo_nonce' );
	echo '<input type="hidden" id="ad_kangaroo_tab" value="'.$this->current_tab.'">';
	echo '<script>';
		echo 'var _Ad_Kangaroo_ads = '.json_encode( $ad_kangaroo_table_data );
	echo '</script>';

	?>

</div>