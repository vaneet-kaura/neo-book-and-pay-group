<?php
namespace NBAP;

class Autoload {
    const class_directory = __DIR__;
	const sql_dir = NBAP_BK_LOCATION_PATH."/database/migrations/";
	const seed_dir = NBAP_BK_LOCATION_PATH."/database/seed/";
	
	public function __construct() {
		if( !nbap_check_plugin_dependencies() )
			return;		

		add_action( 'init', array( $this, 'init') );

		add_action('media_buttons', function () {
			echo '<a href="#" id="insert-myshortcode" class="button">Add Shortcode</a>';
		}, 25);

	}
	
	public function init() {
		if( !session_id() ) session_start();
		$this->check_licence();
		nbap_object( 'NBAP\Helpers\Functions\Logs', wp_get_upload_dir()[ 'basedir' ] );
		$locale = apply_filters('plugin_locale', get_locale(), 'neo-book-and-pay');
		$mofile = WP_PLUGIN_DIR . '/' . dirname(plugin_basename(__FILE__)) . '/../languages/' . $locale . '.mo';
		if(file_exists($mofile)) 
			load_textdomain('neo-book-and-pay', $mofile);	
		
		$this->migrate_database();
		$this->seed_database();
		$this->declare_tables();
		
		if( is_admin() ) nbap_object( "NBAP\BackendLoader" )->init();
		else nbap_object( "NBAP\FrontendLoader" )->init();	

		$blockUrl = NBAP_BK_LOCATION_URL."/public/backend/block.js"; 
		$blockPath = NBAP_BK_LOCATION_PATH."/public/backend/block.js";
		wp_register_script('nbap-shortcodes', $blockUrl, ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components'], filemtime($blockPath), ['in_footer' => true]);
		register_block_type('neo-book-and-pay/nbap-shortcodes', [
			'editor_script' => 'nbap-shortcodes',
			'render_callback' => function () { return ''; }
		]);
	}

	private function check_licence() {
		$licence_key = nbap_get_setting("license.license_key", "");
		if(!empty($licence_key)) {
			 $licence_data = nbap_verify_license($licence_key);
			 if($licence_data === false && nbap_get_page() != "setting") {
				 wp_redirect(nbap_view_url( "", "setting", ['tab' => 'license']));
				 exit;
			 }			 
		}
	}
	
	private function declare_tables() {
		$declare_tables = wp_cache_get( 'declare_tables', 'nbap' );
		if($declare_tables === false) {
			global $wpdb;
			define( "NBAP_TB_PREFIX", $wpdb->prefix.NBAP_BK_PLUGIN_PREFIX );
			$declare_tables = $wpdb->get_results( $wpdb->prepare( "SHOW tables LIKE %s",NBAP_TB_PREFIX."%" ), ARRAY_A );	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
			wp_cache_set( 'declare_tables', $declare_tables, 'nbap', 3600 * 24); // cache for 24 hours
		}		
		foreach( $declare_tables as $k => $arr )
			define( 'NBAP_TB_'.strtoupper( substr( $arr[ key( $arr ) ], strlen( NBAP_TB_PREFIX ) ) ), $arr[ key( $arr ) ] );
	}

	private function migrate_database() {		
		$files = glob( self::sql_dir.'*.php' );
		$this->execute_database("core_db_version", $files);
	}
	
	private function seed_database() {		
		$files = glob( self::seed_dir.'*.php' );
		$this->execute_database("core_seed_version", $files);
	}

	private function execute_database($version_type = "db_version", $files = array()) {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$installed_ver = get_option( NBAP_BK_PLUGIN_PREFIX.$version_type );

		if( is_array( $files ) && !empty( $files ) ) {
			asort( $files );
			foreach( $files as $file ) {
				if( $file != "" && file_exists( $file ) ) {
					$file_version = str_replace( array( "sql.", ".php" ), "", basename( $file ) );
					if( version_compare( $file_version,$installed_ver ) > 0 ) {
						$query_array = include_once( $file );
						$error_found = array();
						$process_file = get_option( NBAP_BK_PLUGIN_PREFIX.$version_type."_process" );
						if( isset( $query_array ) && is_array( $query_array ) && count($query_array) > 0 && $process_file == false ) {
							update_option( NBAP_BK_PLUGIN_PREFIX.$version_type."_process", $file );
							foreach( $query_array as $query ) {
								$query = str_replace( array( '{prefix}', '{charset_collate}' ), array( $wpdb->prefix.NBAP_BK_PLUGIN_PREFIX, $charset_collate ), $query );
								if( $query != "" && $process_file == false ) {
									$wpdb->query( $query );	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.PreparedSQL.NotPrepared
									if( $wpdb->last_error !== '' ) {
										array_push( $error_found, array( "file" 	=> __FILE__,
												"function"	=>__FUNCTION__,
												"query"		=>$query,
												"error_msg"	=>$wpdb->last_error
											)
										);
									}
								}
							}
						}
						if( count( $error_found ) === 0 ) {
							$installed_ver = $file_version;
							update_option( NBAP_BK_PLUGIN_PREFIX.$version_type, $installed_ver );
							delete_option( NBAP_BK_PLUGIN_PREFIX.$version_type."_process");
						} else {
							nbap_object( "NBAP\Helpers\Functions\Logs" )->debug( $error_found );
							delete_option( NBAP_BK_PLUGIN_PREFIX.$version_type."_process" );
							break;
						}	
						wp_cache_delete( 'declare_tables', 'nbap' );
					}
				}
			}
		}
	}

	public static function load_class_init( $class ) {
		if( !str_starts_with( $class, __NAMESPACE__ ) )
			return NULL;
		
		$file = self::get_file_path( $class );		
		if( $file != '' && file_exists( $file ) ) {
			require_once $file;		
			return;
		}
		echo esc_html($file);
		exit;
    }
	
	public static function get_file_path( $class, $ext = ".php" ) {
		return self::class_directory."/".str_replace( array( __NAMESPACE__, '\\' ), array( '', '/'), $class ).$ext;
	}	
}