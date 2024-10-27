<form id="ad_kangaroo_settings_form" method="post">
	<table id="ad_kangaroo_api" class="form-table">
		<?php if ( !$this->google_client->getAccessToken() ): ?>
			<th scope="row"><?php _e( 'Get authorization code', 'ad_kangaroo' ); ?></th>
			<td>
				<?php

				$ad_kangaroo_state = mt_rand();
				$this->google_client->setState( $ad_kangaroo_state );
				$_SESSION[ 'gglstmp_state' . $this->ad_kangaroo_blog_prefix ] = $this->google_client;
				$ad_kangaroo_auth_url = $this->google_client->createAuthUrl();


				?>
				<a id="ad_kangaroo_authorization_button" class="button-primary" href="<?php echo $ad_kangaroo_auth_url; ?>" target="_blank" onclick="window.open(this.href,'','top='+(screen.height/2-560/2)+',left='+(screen.width/2-640/2)+',width=640,height=560,resizable=0,scrollbars=0,menubar=0,toolbar=0,status=1,location=0').focus(); return false;"><?php _e( 'Get Authorization Code', 'ad_kangaroo' ); ?></a>
				<div id="ad_kangaroo_authorization_notice" style="margin-top:5px;">
					<?php _e( "Please authorize via your Google Account to manage ad blocks.", 'ad_kangaroo' ); ?>
				</div>
			</td>
		<?php endif ?>
		<tr valign="top">
			<th scope="row"><?php _e( 'Remote work with Google AdSense', 'ad_kangaroo' ); ?></th>
			<td>
				<?php if ( $this->google_client->getAccessToken() ) { ?>
					<div id="ad_kangaroo_api_buttons">
						<input class="button-secondary" name="ad_kangaroo_logout" type="submit" value="<?php _e( 'Log out from Google AdSense', 'ad_kangaroo' ); ?>" />
					</div>
				<?php } else { ?>
					<div id="ad_kangaroo_authorization_form">
						<input id="ad_kangaroo_authorization_code" class="ad_kangaroo_no_bind_notice" name="ad_kangaroo_authorization_code" type="text" autocomplete="off" maxlength="100">
						<input id="ad_kangaroo_authorize" class="button-primary" name="ad_kangaroo_authorize" type="submit" value="<?php _e( 'Authorize', 'ad_kangaroo' ); ?>">
					</div>
				<?php } ?>
			</td>
		</tr>
		<?php if ( isset( $this->ad_kangaroo_publisher_id ) ) { ?>
			<tr valign="top">
				<th scope="row"><?php _e( 'Your Publisher ID:', 'ad_kangaroo' ); ?></th>
				<td>
					<span id="ad_kangaroo_publisher_id"><?php echo $this->ad_kangaroo_publisher_id; ?></span>
				</td>
			</tr>
		<?php }
		if ( isset( $this->ad_kangaroo_publisher_id ) ) {?>
			<tr valign="top">
				<th scope="row"><label for="ad_kangaroo_include_inactive_id"><?php _e( 'Show idle ad blocks', 'ad_kangaroo' ); ?>:</label></th>
				<td>
					<input id="ad_kangaroo_include_inactive_id" type="checkbox" name="ad_kangaroo_include_inactive_id" <?php if ( isset( $this->ad_kangaroo_options['include_inactive_ads'] ) && 1 == $this->ad_kangaroo_options['include_inactive_ads'] ) echo 'checked="checked"'; ?> value="1">
				</td>
			</tr>
		<?php } ?>
	</table>
	<?php if ( isset( $this->ad_kangaroo_publisher_id ) ) { ?>
		<p>
			<input type="hidden" name="ad_kangaroo_area" value="<?php echo $this->current_tab; ?>" />
			<input id="ad_kangaroo-submit-button" type="submit" class="button-primary" name="ad_kangaroo_save_settings" value="<?php _e( 'Save Changes', 'ad_kangaroo' ); ?>" />
		</p>
	<?php } ?>
	<?php wp_nonce_field( $this->ad_kangaroo_nonce, 'ad_kangaroo_nonce' ); ?>
</form>