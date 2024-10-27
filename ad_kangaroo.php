<?php
/*
Plugin Name: Ad Kangaroo for AdSense
Description: Add Adsense ads to pages, posts, custom posts, categories, tags, pages, and widgets.
Author: Macho Themes
Text Domain: ad_kangaroo
Domain Path: /languages
Version: 1.0
Author URI: https://machothemes.com/
License: GPLv2 or later
*/


/**
* 
*/
class Ad_Kangaroo {

	public $settings;
	public $google_client;
	public $adsense_client;
	public $plugin_info = array(
			'name' => 'Ad Kangaroo',
			'version' => '1.0'
		);
	public $menu_slug = 'ad_kangaroo';
	public $ad_kangaroo_blog_prefix;
	public $current_tab = 'main';
	public $ad_kangaroo_options;
	public $ad_kangaroo_ads;
	public $ad_kangaroo_nonce;
	public $ad_kangaroo_adunit_types;
	public $ad_kangaroo_adunit_statuses;
	public $ad_kangaroo_adunit_sizes;
	public $ad_kangaroo_publisher_id;
	public $ads_count = 0;


	private $is_main_query = false;



	function __construct(  ) {

		$this->ad_kangaroo_blog_prefix = '_'.get_current_blog_id();
		$this->ad_kangaroo_ads = get_option( 'ad_kangaroo_ads', array() );
		$this->ad_kangaroo_options = get_option( 'ad_kangaroo_options', array() );
		$this->ad_kangaroo_nonce = plugin_basename( __FILE__ );

		$this->ad_kangaroo_adunit_types = array(
			'TEXT'       => __( 'Text', 'ad_kangaroo' ),
			'IMAGE'      => __( 'Image', 'ad_kangaroo' ),
			'TEXT_IMAGE' => __( 'Text/Image', 'ad_kangaroo' ),
			'LINK'       => __( 'Link', 'ad_kangaroo' )
		);

		$this->ad_kangaroo_adunit_statuses = array(
			'NEW'      => __( 'New', 'ad_kangaroo' ),
			'ACTIVE'   => __( 'Active', 'ad_kangaroo' ),
			'INACTIVE' => __( 'Idle', 'ad_kangaroo' )
		);

		$this->ad_kangaroo_adunit_sizes  = array(
			'RESPONSIVE' => __( 'Responsive', 'ad_kangaroo' )
		);

		require_once( dirname( __FILE__ ) . '/class.ad_kangaroo.widget.php' );
		add_action( 'widgets_init', array( $this, 'register_ad_kangaroo_widget' ) );

		if ( is_admin() ) {
			
			$this->run_backend();
		}else{
			$this->run_frontend();
		}

	}

	function register_ad_kangaroo_widget(){
		register_widget( 'Ad_Kangaroo_Widget' );
	}

	public function run_backend() {

		if ( isset($_GET['ad_kangaroo_tab']) ) {
			$this->current_tab = $_GET['ad_kangaroo_tab'];
		}
		add_action( 'admin_init', array( $this, 'ad_kangaroo_init_session') );
		add_action( 'admin_enqueue_scripts', array( $this, 'ad_kangaroo_add_admin_scripts' ) );
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		add_action( 'wp_ajax_ad_kangaroo', array( $this, 'ad_kangaroo_ajax') );

	}

	public function run_frontend(){

		add_action( 'loop_start', array( $this, 'ad_kangaroo_loop_start' ) );
		add_action( 'loop_end', array( $this, 'ad_kangaroo_loop_end' ) );
		add_filter( 'the_content', array( $this, 'ad_kangaroo_content' ) );

	}

	// Frontend
	public function ad_kangaroo_loop_start( $content ){
		global $wp_query;
		if ( is_main_query() && $content === $wp_query ) {
			$this->is_main_query = true;
			$this->main_query = $wp_query;
		}
	}
	public function ad_kangaroo_loop_end(){
		$this->is_main_query = false;
	}
	public function ad_kangaroo_content( $content ){

		if ( $this->is_main_query && ! is_feed() && ( is_home() || is_front_page() || is_category() || is_tag() || is_single() || is_page() ) ){

			$show_before = true;
			$show_after = true;

			if ( is_home() || is_category() || is_tag() ) {
				
				$max_posts = $this->main_query->post_count;
				$current = $this->main_query->current_post + 1 ;

				if ( $current == 1 ) {
					$show_after = false;
				}elseif ( $max_posts == $current ) {
					$show_before = false;
				}else{
					$show_before = false;
					$show_after = false;
				}

			}

			if ( is_home() || is_front_page() ) {
				$ad_page = 'home';
			}
			if ( is_category() || is_tag() ) {
				$ad_page = 'categories_tags';
			}
			if ( is_single() ) {
				$ad_page = 'posts';
			}
			if ( is_page() ) {
				$ad_page = 'pages';
			}

			if ( isset($this->ad_kangaroo_ads[$this->ad_kangaroo_options['publisher_id']][$ad_page]) ) {
				foreach ( $this->ad_kangaroo_ads[$this->ad_kangaroo_options['publisher_id']][$ad_page] as $ad_unit_id => $ad_unit ) {
					$ad_code = htmlspecialchars_decode( $ad_unit['code'] );
					$ad_html = sprintf( '<div id="%s" class="ads ads_after">%s</div>', $ad_unit_id, $ad_code );

					if ( $ad_unit['position'] == 'before' && $show_before && $this->ads_count < 4 ) {
						$content = $ad_html.$content;
						$this->ads_count ++;
					}

					if ( $ad_unit['position'] == 'after' && $show_after && $this->ads_count < 4 ) {
						$content = $content.$ad_html;
						$this->ads_count ++;
					}
				}
			}

		}

		return $content;

	}

	// Backend 
	public function create_google_client() {

		require_once( dirname( __FILE__ ) . '/google_api/autoload.php' );
		$client = new Google_Client();
		$client->setClientId( '132414533943-no0lcpfjlm975fop8eah6agatvn9u3vh.apps.googleusercontent.com' );
		$client->setClientSecret( '6FEn4C1BjBA-kLMbilUNYDE7' );
		$client->setScopes( array( 'https://www.googleapis.com/auth/adsense' ) );
		$client->setRedirectUri( 'urn:ietf:wg:oauth:2.0:oob' );
		$client->setAccessType( 'offline' );
		$client->setDeveloperKey( 'AIzaSyCRqcU0_H1TehsCmwGPLA5jsPgMIATClc0' );
		$client->setApplicationName( $this->plugin_info['name'] );
		return $client;

	}

	public function ad_kangaroo_add_admin_scripts() {
		if ( isset( $_GET['page'] ) && $this->menu_slug == $_GET['page'] ) {
			wp_enqueue_style( 'ad_kangaroo_admin_css', plugins_url( 'css/admin.css', __FILE__ ), false, $this->plugin_info["version"] );
			wp_enqueue_script( 'ad_kangaroo_admin_js', plugins_url( 'js/admin.js' , __FILE__ ), array( 'jquery' ), $this->plugin_info["version"] );
			$count = 0;
			if ( isset( $this->ad_kangaroo_options['publisher_id'] ) && isset( $this->ad_kangaroo_ads[$this->ad_kangaroo_options['publisher_id']][$this->current_tab] ) ) {
				$count = count( $this->ad_kangaroo_ads[$this->ad_kangaroo_options['publisher_id']][$this->current_tab] );
			}
			$Ad_Kangaroo = array(
				'ajax' => admin_url( 'admin-ajax.php' ),
				'ads_count' => $count
				);
			wp_localize_script( 'ad_kangaroo_admin_js', 'Ad_Kangaroo', $Ad_Kangaroo );
		}
	}

	public function ad_kangaroo_init_session(){

		if ( isset( $_GET['page'] ) && $this->menu_slug == $_GET['page'] ) {
			if ( ! session_id() ) {
				session_start();
			}
		}

	}

	public function add_admin_menu() {
		add_menu_page( __( 'Ad Kangaroo', 'ad_kangaroo' ), __( 'Ad Kangaroo', 'ad_kangaroo' ), 'manage_options', $this->menu_slug, array( $this, 'am_admin_page' ), 'dashicons-dashboard' );
	}

	public function render_tabs(){
		$tabs = array( 
				'main' => __( 'Settings', 'ad_kangaroo' ),
		);

		if ( isset( $this->ad_kangaroo_publisher_id ) ) {
			$tabs = array(
					'main' => __( 'Settings', 'ad_kangaroo' ),
					'home'  => __( 'Homepage', 'ad_kangaroo' ),
					'pages' => __( 'Pages', 'ad_kangaroo' ),
					'posts' => __( 'Posts', 'ad_kangaroo' ),
					'categories_tags' => __( 'Categories / Tags', 'ad_kangaroo' ),
					'widget' => __( 'Widget', 'ad_kangaroo' )
				);
		}

		foreach ($tabs as $key => $tab) {

			$title = $tab;

			if ( 'main' != $key ) {
				
				$count = 0;
				if ( isset( $this->ad_kangaroo_ads[$this->ad_kangaroo_options['publisher_id']][$key] ) ) {
					$count = count( $this->ad_kangaroo_ads[$this->ad_kangaroo_options['publisher_id']][$key] );
				}

				$title = $tab.'<span class="ad_kangaroo_ads">'.$count.'</span>';

			}

			$url = admin_url( 'admin.php?page=ad_kangaroo&ad_kangaroo_tab='.$key );
			if ( $key == 'main' ) {
				$url = admin_url( 'admin.php?page=ad_kangaroo' );
			}

			if ( $key != $this->current_tab ) {
				echo '<a href="'.$url.'" class="nav-tab">'.$title.'</a>';
			}else{
				echo '<a href="'.$url.'" class="nav-tab nav-tab-active">'.$title.'</a>';
			}
			
		}

	}

	public function am_admin_page() {
		$this->google_client = $this->create_google_client();

		// Ad_Kangaroo Forms actions

		// Logout action.
		if ( isset( $_POST['ad_kangaroo_logout'] ) && check_admin_referer( $this->ad_kangaroo_nonce, 'ad_kangaroo_nonce' ) ) {
			unset( $_SESSION[ 'ad_kangaroo_authorization_code' . $this->ad_kangaroo_blog_prefix ] );
			unset( $this->ad_kangaroo_options['authorization_code'] );
			update_option( 'ad_kangaroo_options', $this->ad_kangaroo_options );
		}

		// Authenticate action.
		if ( isset( $_POST['ad_kangaroo_authorization_code'] ) && ! empty( $_POST['ad_kangaroo_authorization_code'] ) && check_admin_referer( $this->ad_kangaroo_nonce, 'ad_kangaroo_nonce' ) ) {
			try {
				$this->google_client->authenticate( $_POST['ad_kangaroo_authorization_code'] );
				$this->ad_kangaroo_options['authorization_code'] = $_SESSION[ 'ad_kangaroo_authorization_code' . $this->ad_kangaroo_blog_prefix ] = $this->google_client->getAccessToken();
				update_option( 'ad_kangaroo_options', $this->ad_kangaroo_options );
			} catch ( Exception $e ) {}
		}

		// Add authorization to session if authorization exist in db.
		if ( ! isset( $_SESSION[ 'ad_kangaroo_authorization_code' . $this->ad_kangaroo_blog_prefix ] ) && isset( $this->ad_kangaroo_options['authorization_code'] ) ) {
			$_SESSION[ 'ad_kangaroo_authorization_code' . $this->ad_kangaroo_blog_prefix ] = $this->ad_kangaroo_options['authorization_code'];
		}

		// Set access token if authorization code exist.
		if ( isset( $_SESSION[ 'ad_kangaroo_authorization_code' . $this->ad_kangaroo_blog_prefix ] ) ) {
			$this->google_client->setAccessToken( $_SESSION[ 'ad_kangaroo_authorization_code' . $this->ad_kangaroo_blog_prefix ] );
		}

		// Get AdSense Info
		if ( $this->google_client->getAccessToken() ) {
			$this->adsense_client = new Google_Service_AdSense( $this->google_client );
			$ad_kangaroo_adsense_accounts = $this->adsense_client->accounts;
			$ad_kangaroo_adsense_adclients = $this->adsense_client->adclients;
			$ad_kangaroo_adsense_adunits = $this->adsense_client->adunits;

			try {
				$ad_kangaroo_list_accounts = $ad_kangaroo_adsense_accounts->listAccounts()->getItems();
				$this->ad_kangaroo_publisher_id = $ad_kangaroo_list_accounts[0]['id'];
				$this->ad_kangaroo_options['publisher_id'] = $this->ad_kangaroo_publisher_id;
				/* Start fix old options */
				if ( isset( $this->ad_kangaroo_options['adunits'] ) && ! isset( $this->ad_kangaroo_options['adunits'][ $this->ad_kangaroo_options['publisher_id'] ] ) ) {
					$ad_kangaroo_temp_adunits = $this->ad_kangaroo_options['adunits'];
					unset( $this->ad_kangaroo_options['adunits'] );
					$this->ad_kangaroo_options['adunits'][ $this->ad_kangaroo_options['publisher_id'] ] = $ad_kangaroo_temp_adunits;
				}
				/* End fix old options */
				update_option( 'ad_kangaroo_options', $this->ad_kangaroo_options );
				try {
					$ad_kangaroo_list_adclients = $ad_kangaroo_adsense_adclients->listAdclients()->getItems();
					$ad_kangaroo_ad_client = null;
					foreach ( $ad_kangaroo_list_adclients as $ad_kangaroo_list_adclient ) {
						if ( $ad_kangaroo_list_adclient['productCode'] == 'AFC' ) {
							$ad_kangaroo_ad_client = $ad_kangaroo_list_adclient['id'];
						}
					}
					if ( $ad_kangaroo_ad_client ) {
						try {
							$ad_kangaroo_adunits = $ad_kangaroo_adsense_adunits->listAdunits( $ad_kangaroo_ad_client )->getItems();
							foreach ( $ad_kangaroo_adunits as $ad_kangaroo_adunit ) {
								$ad_kangaroo_adunit_type = $this->ad_kangaroo_adunit_types[ $ad_kangaroo_adunit->getContentAdsSettings()->getType() ];
								$ad_kangaroo_adunit_size = preg_replace( '/SIZE_([\d]+)_([\d]+)/', '$1x$2', $ad_kangaroo_adunit->getContentAdsSettings()->getSize() );
								if ( array_key_exists( $ad_kangaroo_adunit_size, $this->ad_kangaroo_adunit_sizes ) ) {
									$ad_kangaroo_adunit_size = $this->ad_kangaroo_adunit_sizes[ $ad_kangaroo_adunit_size ];
								}
								$ad_kangaroo_adunit_status = $ad_kangaroo_adunit->getStatus();
								if ( array_key_exists( $ad_kangaroo_adunit_status, $this->ad_kangaroo_adunit_statuses ) ) {
									$ad_kangaroo_adunit_status = $this->ad_kangaroo_adunit_statuses[ $ad_kangaroo_adunit_status ];
								}
								$ad_kangaroo_table_data[ $ad_kangaroo_adunit->getId() ] = array(
									'id'      => $ad_kangaroo_adunit->getId(),
									'name'    => $ad_kangaroo_adunit->getName(),
									'code'    => $ad_kangaroo_adunit->getCode(),
									'summary' => sprintf( '%s, %s', $ad_kangaroo_adunit_type, $ad_kangaroo_adunit_size ),
									'status'  => $ad_kangaroo_adunit_status,
									'status_value' => $ad_kangaroo_adunit['status'],
									// 'script_code' => htmlspecialchars($ad_kangaroo_adsense_adunits->getAdCode( $ad_kangaroo_ad_client, $ad_kangaroo_adunit->getId() )->getAdCode())
								);
							}
						} catch ( Google_Service_Exception $e ) {
							$ad_kangaroo_err = $e->getErrors();
							$ad_kangaroo_api_notice = array(
								'class'    => 'error ad_kangaroo_api_notice below-h2',
								'message'  => sprintf( '<strong>%s</strong> %s %s',
												__( 'AdUnits Error:', 'ad_kangaroo' ),
												$ad_kangaroo_err[0]['message'],
												sprintf( __( 'Create account in %s', 'ad_kangaroo' ), '<a href="https://www.google.com/adsense" target="_blank">Google AdSense.</a>' )
											)
							);
						}
					}
				} catch ( Google_Service_Exception $e ) {
					$ad_kangaroo_err = $e->getErrors();
					$ad_kangaroo_api_notice = array(
						'class'    => 'error ad_kangaroo_api_notice below-h2',
						'message'  => sprintf( '<strong>%s</strong> %s %s',
										__( 'AdClient Error:', 'ad_kangaroo' ),
										$ad_kangaroo_err[0]['message'],
										sprintf( __( 'Create account in %s', 'ad_kangaroo' ), '<a href="https://www.google.com/adsense" target="_blank">Google AdSense.</a>' )
									)
					);
				}
			} catch ( Google_Service_Exception $e ) {
				$ad_kangaroo_err = $e->getErrors();
				$ad_kangaroo_api_notice = array(
					'class'    => 'error ad_kangaroo_api_notice below-h2',
					'message'  => sprintf( '<strong>%s</strong> %s %s',
									__( 'Account Error:', 'ad_kangaroo' ),
									$ad_kangaroo_err[0]['message'],
									sprintf( __( 'Create account in %s', 'ad_kangaroo' ), '<a href="https://www.google.com/adsense" target="_blank">Google AdSense.</a>' )
								)
				);
			} catch ( Exception $e ) {
				$ad_kangaroo_api_notice = array(
					'class'   => 'error ad_kangaroo_api_notice below-h2',
					'message' => $e->getMessage()
				);
			}

		}

		if ( isset( $_POST['ad_kangaroo_authorization_code'] ) && isset( $_POST['ad_kangaroo_authorize'] ) && ! $this->google_client->getAccessToken() && check_admin_referer( $this->ad_kangaroo_nonce, 'ad_kangaroo_nonce' ) ) {
			$ad_kangaroo_api_notice = array(
				'class'   => 'error ad_kangaroo_api_notice below-h2',
				'message' => __( 'Invalid authorization code. Please, try again.', 'ad_kangaroo' )
			);
		}

		if ( isset( $_POST['ad_kangaroo_save_settings'] ) && check_admin_referer( $this->ad_kangaroo_nonce, 'ad_kangaroo_nonce' ) ) {
			$this->ad_kangaroo_options['include_inactive_ads'] = ( ! empty( $_POST['ad_kangaroo_include_inactive_id'] ) ) ? 1 : 0;
			update_option( 'ad_kangaroo_options', $this->ad_kangaroo_options );
		}

		require_once( dirname( __FILE__ ) . '/templates/screen.php' );
	}

	public function ad_kangaroo_ajax(){

		if ( isset($_POST['id']) && isset($_POST['position']) && check_admin_referer( $this->ad_kangaroo_nonce, 'secret' ) ) {

			$current_publisher_ads = isset( $this->ad_kangaroo_ads[$this->ad_kangaroo_options['publisher_id']] ) ? $this->ad_kangaroo_ads[$this->ad_kangaroo_options['publisher_id']] : array();

			if ( 'disable' == $_POST['position'] ) {
				unset( $current_publisher_ads[$_POST['tab']][$_POST['id']] );
				$this->ad_kangaroo_ads[$this->ad_kangaroo_options['publisher_id']] = $current_publisher_ads;
				update_option( 'ad_kangaroo_ads', $this->ad_kangaroo_ads );
				wp_die( 'succes' );
			}

			$this->google_client = $this->create_google_client();
			$this->google_client->setAccessToken( $this->ad_kangaroo_options['authorization_code'] );

			if ( $this->google_client->getAccessToken() ) {

				$this->adsense_client = new Google_Service_AdSense( $this->google_client );
				$ad_kangaroo_adsense_adunits = $this->adsense_client->adunits;
				$ad_kangaroo_adsense_adclients = $this->adsense_client->adclients;

				$ad_kangaroo_list_adclients = $ad_kangaroo_adsense_adclients->listAdclients()->getItems();

				foreach ( $ad_kangaroo_list_adclients as $ad_kangaroo_list_adclient ) {
					if ( $ad_kangaroo_list_adclient['productCode'] == 'AFC' ) {
						$ad_kangaroo_ad_client = $ad_kangaroo_list_adclient['id'];
					}
				}

				if ( $ad_kangaroo_ad_client ) {
					
					$ad_kangaroo_adunits = $ad_kangaroo_adsense_adunits->listAdunits( $ad_kangaroo_ad_client )->getItems();

					$code = $ad_kangaroo_adsense_adunits->getAdCode( $ad_kangaroo_ad_client, $_POST['id'] )->getAdCode();

					$current_publisher_ads[$_POST['tab']][$_POST['id']] = array(
							'position' => $_POST['position'],
							'code' => $code
						);
					$this->ad_kangaroo_ads[$this->ad_kangaroo_options['publisher_id']] = $current_publisher_ads;
					update_option( 'ad_kangaroo_ads', $this->ad_kangaroo_ads );
					wp_die( 'succes' );

				}
				

			}

			
		}

		wp_die('error');

	}


}

$wp_ad_kangaroo = new Ad_Kangaroo();