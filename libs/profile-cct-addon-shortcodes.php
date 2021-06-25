<?php

class Profile_CCT_Addon_Shortcodes {
	/**
	* init function.
	*
	* @access public
	* @static
	* @return void
	*/

	/** tag cloud defaults from wp **/
	private $cloud_smallest = 8;
	private $cloud_largest = 22;
	private $cloud_number = 45;
	private $cloud_taxonomy = '';

	private $default_templates = array( 'aopublications','aoresearch','aocourses' );
	private $transient_key = 'ao-data-query';

	function __construct( ) {
		add_shortcode( 'aolist-masonary', array( &$this, 'aolist_masonary' ) );
		add_shortcode( 'aolist-fields', array( &$this, 'aolist_fields' ) );
		add_shortcode( 'ao_tag_cloud', array( &$this, 'ao_tag_cloud_shortcode' ) );
		add_shortcode( 'related-by-name', array( &$this, 'related_by_name' ) );
		add_action( 'save_post', array( &$this, 'delete_ao_transient' ), 10, 2 );
	}

	/**
	 * function to return the templates to be used for displaying ao fields
	 * sanitizes return as well
	 * @param array $attr attributes passed to the shortcode.
	 */
	private function get_templates( $template_string ) {
		if ( empty( $template_string ) ) {
			$templates = $this->default_templates;
		} else {
			$templates = explode( ',',$template_string );
			foreach ( $templates as $template ) {
			   	if ( ! in_array( $template, $this->default_templates ) ) {
					return false;
				}
			}
		}
		return $templates;
	}

	/**
	 * function to return the ao taxonomy to be used filtering ao fields
	 * sanitizes - can only return '','terms' or 'themes'
	 * @param $taxonomy passed in attributes to the shortcode.
	 */
	private function get_taxkey( $taxonomy ) {
		$profile = Profile_CCT::get_object();
		if ( ( $taxonomy === $profile::$settings['archive']['ao_use_tax'][0] ) || ( $taxonomy === $profile::$settings['archive']['ao_use_taxall'][0] ) ) {
			if ( $taxonomy === $profile::$settings['archive']['ao_use_tax'][0] ) {
				  return 'terms';
			}
			if ( $taxonomy === $profile::$settings['archive']['ao_use_taxall'][0] ) {
				  return 'themes';
			}
		} else {
			  return '';
		}
	}

	/**
	 * function to generate and return main query data
	 * This is cached in a transient for 24 hours or if any profiles are changed
	 * @param array $templates.
	 */
	private function get_query_data() {
		$output_array = get_transient( $this->transient_key );
		if ( ! $output_array ) {
			$output_array = array();
			$templates = $this->get_templates( '' );
			$posts = get_posts( array( 'numberposts' => -1, 'post_type' => 'profile_cct' ) );
			foreach ( $posts as $post ) {
				foreach ( $templates as $template ) {
					$dataarray = maybe_unserialize( get_post_meta( $post->ID,'profile_cct' ) );
					foreach ( $dataarray[0][ $template ] as $publication ) {
						$output_array[ $template ][] = $publication;
					}
				}
			}
			set_transient( $this->transient_key , $output_array, DAY_IN_SECONDS );
		}
		return $output_array;
	}

	 /**
	 * function to delete transient query data
	 * only if post is of type 'profile_cct'
	 * @param array $attr attributes passed to the shortcode.
	 */
	function delete_ao_transient( $post_id, $post ) {
		if ( 'profile_cct' !== $post->post_type ) {
			return;
		}
		delete_transient( $this->transient_key );
	}

	/**
	 * function to return all ao items containing term
	 *
	 * @param array $output_array $taxkey, $term.
	 */
	private function get_field_items( $output_array, $tax_key, $term ) {
		$publications = array();
		foreach ( $output_array as $publication ) {
			$terms_array = $publication[ $tax_key ];
			if ( $terms_array ) {
				if ( in_array( $term,$terms_array ) ) {
					$publications[] = $publication;
				}
			}
		}
		return $publications;
	}

	/**
	 * function creates output for ao fields
	 * is called between output buffering
	 * @param array $output_array $taxkey, $taxonomy.
	 */
	private function display_list( $output_array, $templates, $tax_key, $taxonomy ) {
		if ( $tax_key ) {
			$terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );
			foreach ( $terms as $term ) {
				$header = true;
				foreach ( $templates as $template ) {
					$publications = $this->get_field_items( $output_array[ $template ], $tax_key, $term->slug );
					if ( $publications ) {
						if ( $header ) {
							echo '<h3><a href="'.esc_url( get_term_link( $term, $taxonomy ) ).'">'.esc_html( $term->name ).'</a></h3>';
							$header = false;
						}
						foreach ( $publications as $publication ) {
							call_user_func( 'profile_cct_'.$template.'_shell', 'page', $publication );
						}
					}
		 		}
			}
		} else {
			foreach ( $templates as $template ) {
				foreach ( $output_array[ $template ] as $publication ) {
					call_user_func( 'profile_cct_'.$template.'_shell', 'page', $publication );
				}
			}
		}
	}

	/**
	 * Shortcode function aolist_fields
	 *
	 * @param array $attr Attributes attributed to the shortcode.
	 */
	function aolist_fields( $atts ) {
		$atts = shortcode_atts( array( 'taxonomy' => '', 'template' => 'aopublications' ), $atts , 'list-taxonomy' );
		$templates = $this->get_templates( $atts['template'] );
		$tax_key = $this->get_taxkey( $atts['taxonomy'] );
		$output_array = $this->get_query_data();

		Profile_CCT_Admin::$action = 'display';
		Profile_CCT_Admin::$page   = 'page';
		ob_start();

		if ( $tax_key ) {
			$this->display_list( $output_array, $templates, $tax_key, $atts['taxonomy'] );
		} else {
			$this->display_list( $output_array, $templates, '', '' );
		}
		$output = ob_get_contents();
		ob_end_clean();
		return wp_kses_post( $output );
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
			if ( ( $attr['taxonomy'] === $profile::$settings['archive']['ao_use_tax'][0] ) || ( $attr['taxonomy'] === $profile::$settings['archive']['ao_use_taxall'][0] ) ) {
				$this->cloud_taxonomy = $attr['taxonomy'];
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
				return wp_kses_post( $output );
			} else {
				return 'Taxonomy needs to be one of the ones set in AO Settings'.$profile::$settings['archive']['ao_use_tax'][0].' or '.$profile::$settings['archive']['ao_use_taxall'][0];
			}
		} else {
			return 'You are missing the taxonomy parameter';
		}
	}

	/**
	 * Function for generating the data (counts) for ao fields
	 *
	 * @param array $tags_data  WP's array of data to 'fix'.
	 */
	function ao_tag_count( $tags_data ) {
		$counts = array();
		$counts = $this->get_ao_termcounts( $tags_data );
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

	/**
	 * Function that actually does the counting for each term for ao field type
	 *
	 * @param array $termslug the term slug used in meta query (cache this).
	 */
	private function get_ao_termcounts( $tags_data ) {
		$counts = array();
		$templates = $this->get_templates( '' );
		$terms = get_terms( $this->cloud_taxonomy, array( 'hide_empty' => false ) );
		$tax_key = $this->get_taxkey( $this->cloud_taxonomy );
		$output_array = $this->get_query_data();
		foreach ( $tags_data as $key => $tag_data ) {
			$pcount = 0;
			foreach ( $templates as $template ) {
				$publications = $this->get_field_items( $output_array[ $template ], $tax_key, $tag_data['slug'] );
				$pcount = $pcount + count( $publications );
			}
			$counts[ $key ] = $pcount;
		}
		return $counts;
	}

	/**
	 * Shortcode function for creating markup for further processing on client side
	 * useful for any algorithm and display customizations
	 * @param array $attr Attributes attributed to the shortcode.
	 */
	function aolist_masonary( $atts ) {

		$allowed_html['div'] = array( 'id' => array(), 'class' => array(), 'data-pcount' => array(), 'data-rcount' => array(), 'data-bcount' => array(), 'data-jcount' => array(), 'data-ccount' => array() );
		$allowed_html['p'] = array( 'class' => array() );
		$allowed_html['span'] = array( 'class' => array() );
		$allowed_html['a'] = array( 'href' => array() );
		$allowed_html['img'] = array( 'src' => array() );

		$profile = Profile_CCT::get_object();
		$atts = shortcode_atts( array( 'term' => '', 'class' => 'grid' ), $atts , 'aolist2' );
		if ( $profile::$settings['archive']['ao_use_taxall'][0]  ) {
			if ( $atts['term'] ) {
				$term = get_term_by( 'slug', $atts['term'], $profile::$settings['archive']['ao_use_taxall'][0] );
			}
		}
		if ( $term ) {
			$posts = get_posts( array( 'numberposts' => -1, 'post_type' => 'profile_cct' ) );
			$pcount = 0;
			foreach ( $posts as $post ) { // begin cycle through posts of this taxonomy
				$ibucket = '<div class="grid-item tile profileimg"><a href="'.esc_url( get_post_permalink( $post->ID ) ).'"><p class="aoptitle">'.esc_attr( get_the_title( $post->ID ) ).'</p><img src="'.wp_get_attachment_url( get_post_thumbnail_id( $post->ID,'full' ) ).'" /></a></div>';
				$rcount = 0;
				$bcount = 0;
				$jcount = 0;
				$ccount = 0;
				$items = '';
				$dataarray = maybe_unserialize( get_post_meta( $post->ID,'profile_cct' ) );
				foreach ( $dataarray[0] as $profilefield ) { //each field
					if ( is_array( $profilefield[0] ) ) {
						foreach ( $profilefield as $publication ) {
							$terms_array = $publication['themes'];
							if ( $terms_array ) {
								if ( in_array( $term->slug, $terms_array ) ) {
									$ao_link_data = '';
									$ao_link_data_end = '';
									$ao_image_data = '';
									$ao_hasimage = '';
									if ( $publication['aopublication-website'] ) {
										$ao_link_data = '<a href="'.$publication['aopublication-website'].'">';
										$ao_link_data_end = '</a>';
									}
									if ( $publication['aopublication-image'] ) {
										$ao_image_data = '<img class="aoimg" src="'.$publication['aopublication-image'].'"/>';
										$ao_hasimage = 'has-image';
									}
									if ( array_key_exists( 'aoresearch-pi' ,$profilefield[0] ) ) { //research
										$rcount ++;
										$ao_type = 'research';
										$ao_title = $publication['aopublication-title'];
										$ao_tagline = $publication['aoresearch-funder'];
									}
									if ( array_key_exists( 'aopublication-chapter' ,$profilefield[0] ) ) { //publication
										$ao_title = $publication['aopublication-title'];
										if ( $publication['aopublication-book'] ) {
											$bcount ++;
											$ao_type = 'book';
											$ao_tagline = $publication['aopublication-publisher'].': '.$publication['aopublication-year'];
										} else {
											$jcount ++;
											$ao_type = 'journal';
											$ao_tagline = $publication['aopublication-chapter']; //check this
										}
									}
									if ( array_key_exists( 'aocourse-code' ,$profilefield[0] ) ) { //course
											$ccount ++;
											$ao_type = 'course';
											$ao_title = $publication['aocourse-code'];
											$ao_tagline = $publication['aopublication-title'];
									}
									$items .= '<div id="tile" class="grid-item tile inactive '.$ao_type.' '.$ao_hasimage.'">'.$ao_link_data.'<span class="aoimgtype-wrap"></span><span class="aodata"><p class="aotitle">'.$ao_title.'</p><p class="aotag">'.$ao_tagline.'</p></span><span class="aoimg-wrap">'.$ao_image_data.'</span>'.$ao_link_data_end.'<span class="aobgimg-wrap"></span></div>';
								}
							}
						}
					}
				}
				$output .= '<div id="pgrid'.$pcount.'" data-pcount="'.$pcount.'" data-rcount="'.$rcount.'" data-bcount="'.$bcount.'" data-jcount="'.$jcount.'" data-ccount="'.$ccount.'" class="ao-grid aoprofile'.$pcount.'">'.$ibucket.$items.'</div>';
				$pcount ++;
			}
			return wp_kses( $output, $allowed_html );
		} else {
			return 'ERROR - Missing term or ao_taxonomy in shortcode parameter OR no term in taxonomy';
		}
	}

	/**
	 * Shortcode function for showing posts related to profiles
	 * by Name on current profiles
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
				$output .= '<h4><a href="'.esc_url( get_the_permalink() ).'" rel="bookmark" title="Permanent Link to '.esc_attr( get_the_title() ).'">'.esc_attr( get_the_title() ).'</a></h4>'.wp_kses_post( get_the_content() ).'</div>';
			}
			$output .= '</div>';
		}
		wp_reset_postdata();
		return wp_kses_post( $output );
	}

}
$profile_cct_addon_shortcodes = new Profile_CCT_Addon_Shortcodes();
