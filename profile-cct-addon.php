<?php
/**
 * Plugin Name: Profile CCT Addon
 * Plugin URI: https://github.com/ubc/profile-cct
 * Version: 1.4.1
 * Description: Adds new field type
 * Author: Shaffiq Rahemtulla  ISIT, UBC
 * Author URI: http://isit.arts.ubc.ca
 * Licence: GPLv2
 */

add_filter( 'plugins_loaded', '_profile_cct_addon_check_dependancy' );
add_filter( 'plugins_loaded', '_profile_cct_addon' );

function _profile_cct_addon_check_dependancy( ) {
	// Exit if accessed directly
	if ( ! class_exists( 'Profile_CCT_Admin' ) ) {
		//deactivate if GF not active - has to be done out of class as class is an extension
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 'The <strong>Profile_CCT_Addon</strong> plugin requires the Profiles Custom Content plugin (v 1.4 >)to be installed and activated - <strong>DEACTIVATED</strong> ' );
	}
}


/**
 * Profile_CCT class.
 */

class Profile_CCT_Addon {

	public $extratax = '';
	public $intratax = '';

	// -- Function Name : __construct
	// -- Params : None
	// -- Purpose : New Instance
	function __construct( ) {
		error_reporting( E_ERROR | E_WARNING | E_PARSE );
		$profile = Profile_CCT::get_object();
		$this->intratax = $profile->settings['archive']['ao_use_tax'][0];
		$this->extratax = $profile->settings['archive']['ao_use_taxall'][0];
		ini_set( 'display_errors', 'On' );
		$this->setup_constants();
		$this->includes();
		add_filter( 'get_the_excerpt', array( &$this, 'edit_content' ) );
		add_action( 'init', array( &$this, 'init' ) );
	}

	// -- Function Name : includes
	// -- Params :
	// -- Purpose : All php to be included
	private function includes() {

		wp_enqueue_style( 'profile-cct-addon', PROFILE_Addon_CCT_DIR_URL.'/profile-addon.css' );
		require( PROFILE_Addon_CCT_DIR_PATH . 'fields/ao-publications.php' );
		require( PROFILE_Addon_CCT_DIR_PATH . 'fields/ao-courses.php' );
		require( PROFILE_Addon_CCT_DIR_PATH . 'fields/ao-research.php' );
		require( PROFILE_Addon_CCT_DIR_PATH . 'libs/profile-cct-addon-shortcodes.php' );
	}

	// -- Function Name : init
	// -- Params :
	// -- Purpose : All actions filters
	public function init( ) {
		add_filter( 'profile_cct_default_options', array( &$this, 'addfieldtype' ), 10, 2 );
		add_filter( 'profile_cct_fields_to_clone', array( &$this, 'addfieldtypetoclone' ), 10, 1 );
		add_action( 'posts_clauses', array( &$this, 'get_all_profiles' ), 10, 2 );
		add_action( 'admin_menu', array( &$this, 'add_ao_submenu' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'load_admin_js_script' ) );
		add_action( 'admin_print_styles-post.php', array( &$this, 'modify_profilejs' ), 100 );
		add_action( 'admin_print_styles-post-new.php', array( &$this, 'modify_profilejs' ), 100 );
		add_action( 'wp_enqueue_scripts', array( &$this, 'ao_fe_scripts' ) );
	}


	// -- Function Name : modify_profilejs
	// -- Params :
	// -- Purpose : Replace file by dequeue/enqueue
	// -- Fix taxonomy issue with Profiles plugin
	function modify_profilejs() {
		global $current_screen;
		if ( 'profile_cct' === $current_screen->id ) :
			wp_deregister_script( 'profile-cct-edit-post' );
			wp_dequeue_script( 'profile-cct-edit-post' );
			wp_enqueue_script( 'profile-cct-edit-post', PROFILE_Addon_CCT_DIR_URL.'/profile-page.js', array( 'jquery-ui-tabs' ) );
			wp_localize_script( 'profile-cct-edit-post', 'profileCCTSocialArray', Profile_CCT_Social::social_options() );
		endif;
	}

	// -- Function Name : addfieldtype
	// -- Params :
	// -- Purpose : Add the fields to profiles bench
	public function addfieldtype( $setting, $form ) {
		if ( $setting['fields']['bench'] ) {
			array_push( $setting['fields']['bench'],array( 'type' => 'aopublications', 'label' => 'aopublications' ) );
			array_push( $setting['fields']['bench'],array( 'type' => 'aocourses', 'label' => 'aocourses' ) );
			array_push( $setting['fields']['bench'],array( 'type' => 'aoresearch', 'label' => 'aoresearch' ) );
		}
		return $setting;
	}

	// -- Function Name : addfieldtypetoclone
	// -- Params :
	// -- Purpose : Allow new fields to be clonefields_array
	public function addfieldtypetoclone( $clonefields_array ) {
		array_push( $clonefields_array, array( 'type' => 'aopublications' ) );
		array_push( $clonefields_array, array( 'type' => 'aocourses' ) );
		array_push( $clonefields_array, array( 'type' => 'aoresearch' ) );
		return $clonefields_array;
	}

	// -- Function Name : setup_constants
	// -- Params : None
	// -- Purpose : Plugin constant (just paths for now)
	private function setup_constants() {
		define( 'PROFILE_Addon_CCT_DIR_PATH', plugin_dir_path( __FILE__ ) );
		define( 'PROFILE_Addon_CCT_BASENAME', plugin_basename( __FILE__ ) );
		define( 'PROFILE_Addon_CCT_DIR_URL', plugins_url( '', PROFILE_Addon_CCT_BASENAME ) );
	}

	// -- Function Name : add_ao_submenu
	// -- Params :
	// -- Purpose : All submenu into profiles
	function add_ao_submenu() {
		// Settings page
		$page = add_submenu_page(
			'edit.php?post_type=profile_cct',
			__( 'AOSettings', 'profile-cct-td' ),
			__( 'AOSettings', 'profile-cct-td' ),
			'manage_options', __FILE__,
			array( __CLASS__, 'aoadmin_pages' )
		);
	}

	// -- Function Name : edit_content
	// -- Params : $content
	// -- Purpose : Manipulate content based on context
	function edit_content( $content ) {
		global $post;

		if ( (is_single()) && $post->post_type === 'profile_cct' ) {
			return $content;
		} else {
			if ( (is_tax()) && $post->post_type === 'profile_cct' ) {
				$qobj = get_queried_object();
				$currentqobj = $qobj->slug;

				//****MOD hideshow
				$profile = Profile_CCT::get_object();
				$hideclasses = '';
				if ( $qobj->taxonomy === $this->extratax ) {
					if ( ! empty( $profile->settings['archive']['ao_display_onextratax'] ) ) {
						$hideclasses  .= 'hide-extratax';
					}
				}
				if ( $qobj->taxonomy === $this->intratax ) {
					if ( ! empty( $profile->settings['archive']['ao_display_onintratax'] ) ) {
						$hideclasses  .= 'hide-intratax';
					}
				}
				//****

				$param = '';

				$dom = new domDocument;
				//--Necessary silencing to control unnecessary output
				@$dom->loadHTML( $content );
				$xpath = new DOMXPath( $dom );

				$classname = 'aopublications';
				$elements = $xpath->query( "//*[@class='" . $classname . "']" );
				$parent_path = $xpath->query( "//*[@class='" . $classname . " field-item   full']" );

				$pcount = 0;
				for ( $i = $elements->length; --$i >= 0; ) {
					$publication = $elements->item( $i );
					if ( strpos( $publication->childNodes->item( 0 )->getAttribute( 'class' ), $currentqobj ) === false ) {
						$publication->parentNode->parentNode->removeChild( $publication->parentNode );
						$pcount ++;
					}
				}
				if ( ( ( $elements->length - $pcount ) <= 0 ) && ( $elements->length > 0 ) ) {
					$parent_path->item( 0 )->parentNode->removeChild( $parent_path->item( 0 ) );
				}

				$classname = 'aoresearch';
				$elements = $xpath->query( "//*[@class='" . $classname . "']" );
				$parent_path = $xpath->query( "//*[@class='" . $classname . " field-item   full']" );

				$pcount = 0;
				for ( $i = $elements->length; --$i >= 0; ) {
					$publication = $elements->item( $i );
					if ( strpos( $publication->childNodes->item( 0 )->getAttribute( 'class' ), $currentqobj ) === false ) {
						$publication->parentNode->parentNode->removeChild( $publication->parentNode );
						$pcount ++;
					}
				}

				if ( ( ( $elements->length - $pcount ) <= 0 ) && ( $elements->length > 0 ) ) {
					$parent_path->item( 0 )->parentNode->removeChild( $parent_path->item( 0 ) );
				}

				$classname = 'aocourses';
				$elements = $xpath->query( "//*[@class='" . $classname . "']" );
				$parent_path = $xpath->query( "//*[@class='" . $classname . " field-item   full']" );

				$pcount = 0;
				for ( $i = $elements->length; --$i >= 0; ) {
					$publication = $elements->item( $i );
					if ( strpos( $publication->childNodes->item( 0 )->getAttribute( 'class' ), $currentqobj ) === false ) {
						$publication->parentNode->parentNode->removeChild( $publication->parentNode );
						$pcount ++;
					}
				}

				if ( ( ( $elements->length - $pcount ) <= 0 ) && ( $elements->length > 0 ) ) {
					$parent_path->item( 0 )->parentNode->removeChild( $parent_path->item( 0 ) );
				}

				return '<span class="'.$hideclasses.'">'.$dom->saveHTML().'</span>';

			} else {
				if ( (is_archive()) && $post->post_type === 'profile_cct' ) {
					if ( (is_search()) && $post->post_type === 'profile_cct' ) {
						$search = esc_html( $_GET['s'] );
						$search = str_replace( '\"','',$search,$count );
						if ( $count <= 0 ) {
							$search = explode( ' ', $search );
						} else {
							$search = array( $search );
						}
						foreach ( $search as $searchstr ) {
							if ( strlen( $searchstr ) > 2 ) {
								$content = preg_replace( '/('.$searchstr.')(?=[^>]*(<|$))/i', '<span class="found">${1}</span>', $content );
							}
						}
						return $content;
					} else {
						  //****MOD hideshow
						$profile = Profile_CCT::get_object();
						$hideclasses = '';
						if ( ! empty( $profile->settings['archive']['ao_display_onarchive'] ) ) {
							$hideclasses  = 'hide-archive';
						}
						return '<span class="'.$hideclasses.'">'.$content.'</span>';
					}
				}
			}
		}
		return $content;
	}

	// -- Function Name : ao_fe_scripts
	// -- Params :
	// -- Purpose : Load front-end js
	function ao_fe_scripts() {
		wp_enqueue_script( 'profile-addon-fe-script', PROFILE_Addon_CCT_DIR_URL.'/profile-addon-fe.js' );
		//load dashicons style
		wp_enqueue_style( 'dashicons' );
		// load masonary
		wp_enqueue_script( 'profile-addon-masonary', PROFILE_Addon_CCT_DIR_URL.'/masonary.pkgd.min.js' );
	}

	// -- Function Name : custom_posts_join
	// -- Params :
	// -- Purpose : Include all profles for taxonomy
	function custom_posts_join( $join ) {
			 global $wpdb;
			 $join .= " LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
			 return $join;
	}

	// -- Function Name : get_all_profiles
	// -- Params :
	// -- Purpose : Get All the profiles here
	// -- Meta query cache needed here???
	function get_all_profiles( $pieces, $query ) {
		global $wpdb;
		if ( ! is_admin() && $query->is_main_query() && $query->is_tax( $this->extratax ) ) {
			$qobj = get_queried_object();
			$currentqobj = $qobj->slug;
			$metaquery = array(
						array(
								'key' => 'profile_cct',
								'value' => $currentqobj,
								'compare' => 'LIKE',
						),
			);
			$taxquery = array(
						array(
							'taxonomy' => $this->extratax,
							'field' => 'slug',
							'terms' => $currentqobj,
						),
			);

			$field = 'ID';
			$sql_tax  = get_tax_sql( $taxquery,  $wpdb->posts, $field );
			$sql_meta = get_meta_sql( $metaquery, 'post', $wpdb->posts, $field );
			$pieces['where'] = sprintf( ' AND ( %s OR  %s ) ', substr( trim( $sql_meta['where'] ), 4 ), substr( trim( $sql_tax['where'] ), 4 ) );
			$pieces['join'] .= " LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";
		}
		return $pieces;
	}

	// -- Function Name : load_admin_js_script
	// -- Params :
	// -- Purpose : Load admin js scripts here
	public function load_admin_js_script() {
		global $current_screen;
		if ( 'profile_cct' === $current_screen->id ) :
			wp_enqueue_script( 'jquery' );
			global $post;
			$dataarray = maybe_unserialize( get_post_meta( $post->ID, 'profile_cct' ) );
				$name = $dataarray[0][ name ][ last ] .', '.$dataarray[0][ name ][ first ].' '.$dataarray[0][ name ][ middle ];
				wp_enqueue_script( 'profile-addon-script', PROFILE_Addon_CCT_DIR_URL.'/profile-addon.js' );
				wp_localize_script( 'profile-addon-script', 'ao_script_vars',
					array(
						'name' => $name,
						'year' => date( 'Y' ),
					)
				);
		endif;
	}

	// -- Function Name : aoadmin_pages
	// -- Params :
	// -- Purpose : Settings page
	public static function aoadmin_pages() {
		$profile = Profile_CCT::get_object();
		if ( ! empty( $_POST ) && isset( $_POST['update_settings_nonce_field'] ) && wp_verify_nonce( $_POST['update_settings_nonce_field'], 'update_settings_nonce' ) ) :
			$archive = esc_html( $_POST['archive'] );
			$profile->settings['archive'] = $archive;
			update_option( 'Profile_CCT_settings', $profile->settings );
			$note = 'Settings saved.';
			$profile->register_profiles();
			flush_rewrite_rules();
		endif;

		?>
		<h2>AO General Settings</h2>
		<div class="updated below-h2"><p> <?php echo esc_html( $note ); ?></p></div>
		<form method="post" action="">
			<?php wp_nonce_field( 'update_settings_nonce', 'update_settings_nonce_field' ); ?>
			<h3>Profile Archive Navigation Form</h3>
			<p>Below choices need to be documented from the user POV.</p>
			<p><strong style="color:red;">***WARNING - All categorizations within AO fields will be lost</strong> if changes are made to the two taxonomy settings below and an update is done to all profiles.</p>
			<table class="form-table">
					<tbody>
						<tr valign="top">
							<th scope="row">Select an taxonomy to use with Profiles Add On fields for use on ALL profiles.</th>
								<td>
									<select id="archive_ao_use_taxall" name="<?php echo 'archive[ao_use_taxall][0]'; ?>">
										<option value="default">Select a taxonomy to use.</option>
						<?php
						foreach ( $profile->taxonomies as $taxonomy ) {
							if ( $profile->settings['archive']['ao_use_tax'][0] !== Profile_CCT_Taxonomy::id( $taxonomy['single'] ) ) {
								$taxonomy_id = Profile_CCT_Taxonomy::id( $taxonomy['single'] );
						?>
										<option value="<?php echo esc_html( $taxonomy_id ); ?>" <?php echo esc_html( selected( $profile->settings['archive']['ao_use_taxall'][0], $taxonomy_id, false ) ); ?> ><?php echo esc_html( $taxonomy['plural'] ); ?></option>
						<?php
							}
						}
						?>
									</select><br><?php echo esc_html( $profile->settings['archive']['ao_use_taxall'][0] );?>
								</td>
						</tr>
						<tr valign="top">
							<th scope="row">Select an taxonomy to use with Profiles Add On fields for use custom to EACH profile.</th>
								<td>
								<select id="archive_ao_use_tax" name="<?php echo 'archive[ao_use_tax][0]'; ?>">
										<option value="default">Select a taxonomy to use.</option>
						<?php
						foreach ( $profile->taxonomies as $taxonomy ) {
							if ( $profile->settings['archive']['ao_use_taxall'][0] !== Profile_CCT_Taxonomy::id( $taxonomy['single'] ) ) {
								$taxonomy_id = Profile_CCT_Taxonomy::id( $taxonomy['single'] );
						?>
										<option value="<?php echo esc_html( $taxonomy_id ); ?>" <?php echo esc_html( selected( $profile->settings['archive']['ao_use_tax'][0], $taxonomy_id, false ) ); ?> ><?php echo esc_html( $taxonomy['plural'] ); ?></option>
						<?php
							}
						}
						?>
									</select><br><?php echo esc_html( $profile->settings['archive']['ao_use_tax'][0] );?>
								</td>
						</tr>

							<tr valign="top">
								<th scope="row"><label for="ao_archive_display">DONT show AO fields on ALL archive pages</label></th>
								<td>
										<input type="checkbox" name="archive[ao_display_onarchive]" id="ao_archive_display" value="true" <?php checked( ! empty( $profile->settings['archive']['ao_display_onarchive'] ) ); ?> />
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label for="ao_extratax_display">DONT show AO fields on ALL <?php echo esc_html( $profile->settings['archive']['ao_use_taxall'][0] ); ?> taxonomy pages.</label></th>
								<td>
										<input type="checkbox" name="archive[ao_display_onextratax]" id="ao_extratax_display" value="true" <?php checked( ! empty( $profile->settings['archive']['ao_display_onextratax'] ) ); ?> />
								</td>
							</tr>

							<tr valign="top">
								<th scope="row"><label for="ao_intratax_display">DONT show AO fields on ALL <?php echo esc_html( $profile->settings['archive']['ao_use_tax'][0] ); ?> taxonomy pages.</label></th>
								<td>
										<input type="checkbox" name="archive[ao_display_onintratax]" id="ao_intratax_display" value="true" <?php checked( ! empty( $profile->settings['archive']['ao_display_onintratax'] ) ); ?> />
								</td>
							</tr>

					</tbody>
				</table>
			<input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ) ?>" />
		</form>
		<?php
	}

}

// -- Function Name : _profile_cct_addon
// -- Params :
// -- Purpose : call
function _profile_cct_addon() {
	return new Profile_CCT_Addon();
}
