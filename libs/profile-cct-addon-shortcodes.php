<?php

class Profile_CCT_Addon_Shortcodes {
	/**
	* init function.
	*
	* @access public
	* @static
	* @return void
	*/

	function __construct( ) {
		add_shortcode( 'aolist2', array( &$this, 'aolist2' ) );
		add_shortcode( 'list-taxonomy', array( &$this, 'list_taxonomy' ) );
		add_shortcode( 'list-all-taxonomy', array( &$this, 'list_all_taxonomy' ) );
		add_shortcode( 'the_tag_cloud', array( &$this, 'the_tag_cloud_shortcode' ) );
	}

	/**
	 * Shortcode function for showing a tag cloud
	 * Input values are based on wp_tag_cloud().  Since it has no 'echo'
	 * parameter, we must port the function to the plugin to return the
	 * the tag cloud for use with the shortcode API.
	 * @link http://codex.wordpress.org/Template_Tags/wp_tag_cloud
	 *
	 * @since 0.1
	 * @param array $attr Attributes attributed to the shortcode.
	 */
	function the_tag_cloud_shortcode( $attr ) {
		/*if (! isset($attr['taxonomy'])){
			$attr['taxonomy'] = 'post_tag';
		}*/
		//$attr['taxonomy'] = 'profile_cct_specialization';
		if ( $attr['number'] ) {
			$attr['number'] = (int) $attr['number'];
		}
		if ( $attr['largest'] ) {
			$attr['largest'] = (int) $attr['largest'];
		}
		if ( $attr['smallest'] ) {
			$attr['smallest'] = (int) $attr['smallest'];
		}
		$attr['echo'] = false;
		$attr['hide_empty'] = false;
		return wp_tag_cloud( $attr );
	}

	function list_all_taxonomy( $atts ) {
		$atts = shortcode_atts( array( 'taxonomy' => '', 'template' => '', 'term' => '', 'wrap' => false, 'image' => false, 'title' => false ), $atts , 'list-all-taxonomy' );
		$query_array = array(
			'numberposts'   => -1,
			'post_type' => 'profile_cct',
		);
		$profile = Profile_CCT::get_object();
		if ( ( $atts['taxonomy'] == $profile->settings['archive']['ao_use_tax'][0] ) || ( $atts['taxonomy'] == $profile->settings['archive']['ao_use_taxall'][0] ) ) {
			if ( $atts['taxonomy'] == $profile->settings['archive']['ao_use_tax'][0] ) {
				  $tax_key = 'terms';
			}
			if ( $atts['taxonomy'] == $profile->settings['archive']['ao_use_taxall'][0] ) {
				  $tax_key = 'themes';
			}
		} else {
			  $atts['term'] = ''; //don't filter
		}

		if ( empty( $atts['template'] ) ) {
			  $templates = array( 'aopublications','aoresearch','aocourses' );
		} else {
			$templates = explode( ',',$atts['template'] );
		}

		$posts = get_posts( $query_array );

		Profile_CCT_Admin::$action = 'display';
		Profile_CCT_Admin::$page   = 'page';
		foreach ( $posts as $post ) : // begin cycle through posts of this taxonmy
			ob_start();
			foreach ( $templates as $template ) {
				$dataarray = maybe_unserialize( get_post_meta( $post->ID,'profile_cct' ) );
				foreach ( $dataarray[0][ $template ] as $publication ) {
					//if intra then
					if ( ! empty( $atts['term'] ) ) {
						$terms_array = $publication[ $tax_key ];
						if ( $terms_array ) {
							if ( in_array( $atts['term'], $terms_array ) ) {
								call_user_func( 'profile_cct_'.$template.'_shell', 'page', $publication );
								$pcount++;
							}
						}
					} else {
						call_user_func( 'profile_cct_'.$template.'_shell', 'page', $publication );
						  $pcount++;
					}
				}
			}
			  //if has stuff and
			if ( ( $atts['wrap']) && ( $pcount > 0 ) ) {
				if ( $atts['title'] ) {
					$aotitle = '<span class="'.$atts['title'].'">'.get_the_title( $post->ID ).'</span>';
				}
				if ( $atts['image'] ) {
					$aoimage = '<a href="'.get_post_permalink( $post->ID ).'"><span class="'.$atts['image'].'" style="width:80px;height:80px;background-size:contain;display:inline-block;background-image:url('.wp_get_attachment_url( get_post_thumbnail_id( $post->ID,'full' ) ).')">'.$aotitle.'</span></a>';
				}
				$output .= '<div class="'.$atts['wrap'].'">'.$aoimage.ob_get_contents().'</div>';
				ob_end_clean();
			}
		endforeach;
		//$output = ob_get_contents();$output
		return $output;
	}

	function list_taxonomy( $atts ) {
		$atts = shortcode_atts( array( 'taxonomy' => '', 'grouped' => false, 'template' => 'aopublications' ), $atts , 'list-taxonomy' );
		$output = '';
		if ( 'aopublications' == $atts['template'] ) {
			$uakey = 'aopublication-chapter';
		}
		if ( 'aoresearch' == $atts['template'] ) {
			$uakey = 'aoresearch-pi';
		}
		if ( 'aocourses' == $atts['template'] ) {
			$uakey = 'aocourse-code';
		}
		$terms = get_terms( $atts['taxonomy'], array( 'hide_empty' => false ) );

		foreach ( $terms as $term ) :
			ob_start();
			Profile_CCT_Admin::$action = 'display';
			Profile_CCT_Admin::$page   = 'page';

			$metaquery = array(
						array(
								'key' => 'profile_cct',
								'value' => $term->slug,
								'compare' => 'LIKE',
						),
			);

			$posts = get_posts(array(
					'numberposts'   => -1,
					'post_type' => 'profile_cct',
					'meta_query' => $metaquery,
			));

			$pcount = 0;
			foreach ( $posts as $post ) : // begin cycle through posts of this taxonmy
				$dataarray = maybe_unserialize( get_post_meta( $post->ID,'profile_cct' ) );
				foreach ( $dataarray[0] as $profilefield ) {
					if ( is_array( $profilefield[0] ) ) {
						if ( array_key_exists( $uakey, $profilefield[0] ) ) {
							foreach ( $profilefield as $publication ) {
								$terms_array = $publication['terms'];
								//print_r($publication);
								if ( $terms_array ) {
									if ( in_array( $term->slug,$terms_array ) ) {
										call_user_func( 'profile_cct_'.$atts['template'].'_shell', 'page', $publication );
										$pcount++;
									}
								}
							}
						}
					}
				}
			endforeach;
			echo '';
			$heading = '';
			if ( $pcount > 0 ) {
				$heading = '<h3><a href="http://profiles.adm.arts.ubc.ca/specialization/'.$term->slug.'/">'.$term->name.'</a></h3>';
			}
			$output .= $heading.ob_get_contents();
			ob_end_clean();
		endforeach;

		return $output;
	}

}
$profile_cct_addon_shortcodes = new Profile_CCT_Addon_Shortcodes();
