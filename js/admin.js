jQuery(document).ready( function(){

	var _Ad_Kangaroo_Value = '';

	function checkValue( _new_Value ) {

		if ( ( _Ad_Kangaroo_Value == 'before' || _Ad_Kangaroo_Value == 'after' || _Ad_Kangaroo_Value == 'enable' ) && _new_Value == 'disable' ) {
			return true;
		}

		if ( ( _new_Value == 'before' || _new_Value == 'after' || _new_Value == 'enable' ) && _Ad_Kangaroo_Value == 'disable' ) {
			return true;
		}

		return false;

	}

	jQuery('.ad_kangaroo_adunit_position').focus(function(){
		_Ad_Kangaroo_Value = jQuery(this).val();
	}).change(function(){
		var ad_id = jQuery(this).data('id');
		var ad_position = jQuery(this).val();
		var ad_nonce = jQuery('#ad_kangaroo_nonce').val();
		var ad_tab = jQuery('#ad_kangaroo_tab').val();

		jQuery('.ad_kangaroo-ads-list').addClass('waiting');

		jQuery.ajax({
		  	method: "POST",
		  	url: Ad_Kangaroo.ajax,
		  	data: { action: "ad_kangaroo", id: ad_id, position: ad_position, secret: ad_nonce, tab : ad_tab }
		}).done(function( check ) {
			jQuery('.ad_kangaroo-ads-list').removeClass('waiting');

			if ( check == 'succes' && checkValue( ad_position ) && ad_position != 'disable' ) {
				Ad_Kangaroo.ads_count = parseInt(Ad_Kangaroo.ads_count)+1;
			}

			if ( check == 'succes' && ad_position == 'disable' ) {
				Ad_Kangaroo.ads_count = parseInt(Ad_Kangaroo.ads_count)-1;
			}
			jQuery('#am_wrap .nav-tab-wrapper .nav-tab-active .ad_kangaroo_ads').text(Ad_Kangaroo.ads_count);

			if ( Ad_Kangaroo.ads_count == 3 ) {
				jQuery('.ad_kangaroo_adunit_position').each(function(){
					if ( jQuery(this).val() == 'disable' ) {
						jQuery(this).attr( 'disabled', true );
					}
				});
			}else{
				jQuery('.ad_kangaroo_adunit_position').attr( 'disabled', false );
			}

		 });

	});

});