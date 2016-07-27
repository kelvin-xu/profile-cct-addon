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
		add_shortcode( 'ao_tag_cloud', array( &$this, 'ao_tag_cloud_shortcode' ) );
		add_shortcode( 'related-by-name', array( &$this, 'related_by_name' ) );
	}

	/**
	 * Shortcode function for showing related posts
	 *
	 * @param array $attr Attributes attributed to the shortcode.
	 */
	function related_by_name( $atts ) {
		$pid = get_queried_object_id();
		$post = get_post( $pid );
		$tag_slug = $post->post_name;
		//Convert tag slug to tagID
		$tag = get_term_by( 'slug', $tag_slug, 'post_tag' );
		$tag_id = $tag->term_id;
		$name = get_the_title( $pid );
		$args = array( 'tag__in' => $tag_id );
		$cat_name = '';
		//Add parameters here
		$atts = shortcode_atts( array( 'category' => '', 'posts_per_page' => -1, 'img_size' => 'thumbnail', 'title' => '' ), $atts , 'related_by_name' );
		if ( ! empty( $atts['category'] ) ) {
			$cat_id = get_cat_ID( $atts['category'] );
			if ( $cat_id ) {
				$args['category__and'] = $cat_id;
				$cat_name = $atts['category'];
			} else {
				$cat = get_category_by_slug( $atts['category'] );
				if ( $cat ) {
					$args['category__and'] = $cat->term_id;
					$cat_name = $cat->name;
				}
			}
		}
		$args['posts_per_page'] = $atts['posts_per_page'];

		$query = new WP_Query( $args );
		if ( $query->have_posts() ) {
			if ( $atts['title'] ) {
					$output = '<h4>'.$name.$atts['title'].$cat_name.' posts.</h4>';
			}
			$output .= '<div class="related-posts">';
			while ( $query->have_posts() ) {
				$output .= '<div class="related-post clear">';
				$query->the_post();
				$outimg = '';
				if ( has_post_thumbnail() ) {
					$output .= get_the_post_thumbnail( null,$atts['img_size'] );
				}
				$output .= '<h4><a href="'.get_the_permalink().'" rel="bookmark" title="Permanent Link to '.get_the_title().'">'.get_the_title().'</a></h4>'.get_the_content().'</div>';
			}
			$output .= '</div>';
		}
		wp_reset_postdata();
		return $output;
	}



	/**
	 * Shortcode function for showing a tag cloud
	 * Input values are based on wp_tag_cloud().  Since it has no 'echo'
	 * parameter, we must port the function to the plugin to return the
	 * the tag cloud for use with the shortcode API.
	 * @link http://codex.wordpress.org/Template_Tags/wp_tag_cloud
	 *
	 * Hooks in the filter to keep correct counts on ao_fields
	 * @param array $attr Attributes attributed to the shortcode.
	 */
	function ao_tag_cloud_shortcode( $attr ) {
		if ( $attr['taxonomy'] ) {
			$profile = Profile_CCT::get_object();
			if ( ( $attr['taxonomy'] == $profile->settings['archive']['ao_use_tax'][0] ) || ( $attr['taxonomy'] == $profile->settings['archive']['ao_use_taxall'][0] ) ) {
				if ( $attr['taxonomy'] == $profile->settings['archive']['ao_use_tax'][0] ) {
					  $this->cloud_taxonomy = 'terms';
				}
				if ( $attr['taxonomy'] == $profile->settings['archive']['ao_use_taxall'][0] ) {
					  $this->cloud_taxonomy = 'themes';
				}
				add_filter( 'wp_generate_tag_cloud_data', array( &$this, 'ao_tag_count' ) );
				if ( $attr['number'] ) {
					$attr['number'] = (int) $attr['number'];
					$this->cloud_number = $attr['number'];
				}
				if ( $attr['largest'] ) {
					$attr['largest'] = (int) $attr['largest'];
					$this->cloud_largest = $attr['largest'];
				}
				if ( $attr['smallest'] ) {
					$attr['smallest'] = (int) $attr['smallest'];
					$this->cloud_smallest = $attr['smallest'];
				}
				$attr['echo'] = false;
				$attr['hide_empty'] = false;
				$output = wp_tag_cloud( $attr );
				remove_filter( 'wp_generate_tag_cloud_data', array( &$this, 'ao_tag_count' ) );
				return $output;
			} else {
				return 'Taxonomy needs to be one of the ones set in AO Settings'.$profile->settings['archive']['ao_use_tax'][0].' or '.$profile->settings['archive']['ao_use_taxall'][0];
			}
		} else {
			return 'You are missing the taxonomy parameter';
		}
	}

	function ao_tag_count( $tags_data ) {
		$counts = array();
		foreach ( $tags_data as $key => $tag_data ) {
			$counts[ $key ] = $this->get_ao_termcount( $tag_data['slug'] );
		}
		$min_count = min( $counts );
		$spread = max( $counts ) - $min_count;
		$font_spread = $this->cloud_largest - $this->cloud_smallest;
		if ( $spread > 0 ) {
			$font_step = $font_spread / $spread;
		}
		foreach ( $tags_data as $key => &$single_tag_data ) {
			$single_tag_data['name'] = $single_tag_data['name'].'('.$counts[ $key ].')';
			$single_tag_data['font_size'] = $this->cloud_smallest + ($counts[ $key ] - $min_count) * $font_step;
		}
		return $tags_data;
	}

	function get_ao_termcount( $termslug ) {
		$pcount = 0;
		$uakeys = array( 'aopublication-chapter','aoresearch-pi','aocourse-code' );
		$metaquery = array(
			array(
				'key' => 'profile_cct',
				'value' => $termslug,
				'compare' => 'LIKE',
			),
		);
		$posts = get_posts(array(
			'numberposts'   => -1,
			'post_type' => 'profile_cct',
			'meta_query' => $metaquery,
		));
		foreach ( $posts as $post ) : // begin cycle through posts of this taxonmy
			$dataarray = maybe_unserialize( get_post_meta( $post->ID,'profile_cct' ) );
			foreach ( $dataarray[0] as $profilefield ) {
				if ( is_array( $profilefield[0] ) ) {
					if ( array_key_exists( $uakeys[0], $profilefield[0] ) || array_key_exists( $uakeys[1], $profilefield[0] ) || array_key_exists( $uakeys[2], $profilefield[0] ) ) {
						foreach ( $profilefield as $publication ) {
							$terms_array = $publication[ $this->cloud_taxonomy ];
							if ( $terms_array ) {
								if ( in_array( $termslug,$terms_array ) ) {
									$pcount++;
								}
							}
						}
					}
				}
			}
		endforeach;
		return $pcount;
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
