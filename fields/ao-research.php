<?php
class Profile_CCT_AOResearch extends Profile_CCT_Field {
	var $default_options = array(
		'type'          => 'aoresearch',
		'label'         => 'aoresearch',
		'description'   => '',
		'show'          => array( 'aopublication-website', 'aoresearch-year', 'aoresearch-status', 'aopublication-abstract' ),
		'show_fields'   => array( 'aopublication-website', 'aoresearch-year', 'aoresearch-status', 'aopublication-abstract' ),
		'multiple'      => true,
		'show_multiple' => true,
		'width'         => 'full',
		'before'        => '',
		'empty'         => '',
		'after'         => '',
	);
	var $shell = array(
		'class' => 'aoresearch',
	);
	function field() {
		$profile = Profile_CCT::get_object();
		$pubtax = $profile->settings['archive']['ao_use_tax'][0];
		$pubtaxall = $profile->settings['archive']['ao_use_taxall'][0];
		$this->display_shell( array( 'class' => 'handle' ) );
		$this->display();
		$this->display_end_shell();
		$this->display_shell( array( 'class' => 'content-wrap' ) );
		global $post;
		$dataarray = maybe_unserialize( get_post_meta( $post->ID, 'profile_cct' ) );
		$terms_array = array();
		$terms = wp_get_post_terms( $post->ID, $pubtax, array( 'fields' => 'slugs' ) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$terms_array[] = $term;
			}
		}
		$this->input_text( array(
			'field_id' => 'aoresearch-pi',
			'label'    => 'Principle Investigator',
			'size'     => 27,
			'value'    => ( ! empty( $this->data['aoresearch-pi'] ) ? $this->data['aoresearch-pi'] : $dataarray[0][ name ][ first ] .' '.$dataarray[0][ name ][ middle ].' '.$dataarray[0][ name ][ last ] ),
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-authors',
			'label'    => 'Co-investigators',
			'size'     => 27,
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-title',
			'label'    => 'Title',
			'size'     => 57,
		) );

		$this->input_text( array(
			'field_id' => 'aoresearch-program',
			'label'    => 'Dept./Lab/Program',
			'hidden'   => true,
			'size'     => 23,
		) );

		$this->input_select( array(
			'field_id'   => 'aoresearch-funder-selection',
			'label'    => 'Funding Organization',
			'all_fields' => $this->aoresearch_funder(),
		) );

		$this->input_text( array(
			'field_id' => 'aoresearch-funder',
			'class' => ( ( $this->data['aoresearch-funder-selection'] ) == 'Other' ? 'visible' : '' ),
			'hidden'   => 1,
			'size'     => 26,
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-website',
			'label'    => 'Website - http://{value}',
			'size'     => 26,
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-image',
			'label'    => 'Image Source - http://{value}',
			'size'     => 23,
		) );

		$this->input_select( array(
			'field_id'   => 'start-date-month',
			'label'      => 'Start Month',
			'all_fields' => $this->list_of_months(),
		) );

		$this->input_select( array(
			'field_id'   => 'aopublication-year',
			'label'      => 'Start Year',
			'all_fields' => $this->list_of_years( 20, -20 ),
			'value'      => ( ! empty( $this->data['aoresearch-year'] ) ? $this->data['aoresearch-year'] : '2015' ),
		) );

		$this->input_select( array(
			'field_id'   => 'end-date-month',
			'label'      => 'End Month',
			'all_fields' => $this->list_of_months(),
		) );

		$this->input_select( array(
			'field_id'   => 'aoresearch-end-year',
			'label'      => 'End Year',
			'all_fields' => $this->list_of_years( 20, -20 ),
			'value'      => ( ! empty( $this->data['aoresearch-end-year'] ) ? $this->data['aoresearch-end-year'] : '2015' ),
		) );

		$this->input_select( array(
			'field_id'   => 'aopublication-status',
			'label'      => 'Status',
			'all_fields' => $this-> aopublication_status(),
		) );

		//########
		$this->input_textarea( array(
			'field_id' => 'aopublication-abstract',
			'label'    => 'Description',
			'size'     => 35,
		) );

		$this->input_multiple( array(
			'field_id'        => 'terms',
			'selected_fields' => $this->data['terms'],
			'all_fields'      => $terms_array,
		) );

		$themes_array = array();
		$themes = get_terms( $pubtaxall, array( 'hide_empty' => false ) );
		if ( ! empty( $themes ) && ! is_wp_error( $themes ) ) {
			foreach ( $themes as $theme ) {
				$themes_array[] = $theme->slug;
			}
		}

		$this->input_multiple( array(
			'field_id'        => 'themes',
			'selected_fields' => $this->data['themes'],
			'all_fields'      => $themes_array,
		) );

		$this->display_end_shell();
	}

	function display() {
		global $post;
		$dataarray = maybe_unserialize( get_post_meta( $post->ID, 'profile_cct' ) );
		$themeclasses = ( $this->data['themes'] ) ? implode( ' ',$this->data['themes'] ) : '';
		$termclasses = ( $this->data['terms'] ) ? implode( ' ',$this->data['terms'] ) : '';
		$this->display_shell( array( 'class' => 'pubrec research ' . $termclasses.' '.$themeclasses ) );
		$separator = '';

		$this->display_text( array(
					'class'        => 'thumbnail-holder',
					'default_text' => '',
					//'separator'    => $separator,
					'value'       	 => ( ! empty( $this->data['aopublication-image'] ) ? '<span class="thumbnail"></span>' : '' ),
		) );

		$this->display_link( array(
			'field_id'     => 'aopublication-website',
			'class'        => 'aopublication-website',
			'default_text' => 'Cybernetic Enhancements for DC',
			'value'	       => ( ! empty( $this->data['aopublication-title'] ) ? $this->data['aopublication-title'] : ' ' ),
			'maybe_link'   => true,
			'tag'          => ( ! empty( $this->data['aopublication-website'] ) ? 'a' : 'span' ),
			'href'         => ( ! empty( $this->data['aopublication-website'] ) ? $this->data['aopublication-website'] : '' ),
			'post_separator' => ' ',
		) );

		$this->display_link( array(
			'field_id'     => 'aopublication-image',
			'class'        => 'aopublication-image',
			'default_text' => 'The Publication Image',
			'value'	       => ( ! empty( $this->data['aopublication-image'] ) ? $this->data['aopublication-image'] : ' ' ),
			'maybe_link'   => true,
			'tag'          => ( 'span' ),
			//'href'         => ( ! empty( $this->data['aopublication-website'] ) ? $this->data['aopublication-website'] : '' ),
			'post_separator' => ' ',
		) );

		$this->display_text( array(
			'field_id'       => 'aoresearch-pi',
			'class'          => 'aoresearch-pi',
			'default_text'   => 'Bruce Wayne',
			'value'       	 => ( ! empty( $this->data['aoresearch-pi'] ) ? $this->data['aoresearch-pi'] : $dataarray[0][ name ][ first ] .' '.$dataarray[0][ name ][ middle ].' '.$dataarray[0][ name ][ last ] ),
			'post_separator' => ' ',
			'tag'            => 'strong',
		) );

		$this->display_text( array(
			'field_id'       => 'aopublication-authors',
			'class'          => 'aopublication-authors',
			'default_text'   => 'Co-investigators',
			'value'       	 => ( ! empty( $this->data['aopublication-authors'] ) ? $this->data['aopublication-authors'] : ' ' ),
			'post_separator' => ' ',
			//'tag'            => 'strong',
		) );
		$this->display_text( array(
			'field_id'       => 'aoresearch-program',
			'class'          => 'aoresearch-program',
			'default_text'   => 'S.T.A.R Labs',
			'value'       	 => ( ! empty( $this->data['aoresearch-program'] ) ? $this->data['aoresearch-program'] : ' ' ),
			'post_separator' => ' ',
			//'tag'            => 'strong',
		) );
		$this->display_text( array(
			'field_id'     => 'aoresearch-funder',
			'class'        => 'aoresearch-funder',
			'default_text' => 'Social Sciences and Humanities Research Council (SSHRC)',
			'tag'          => 'strong',
			'value'       	 => ( ! empty( $this->data['aoresearch-funder'] ) ? $this->data['aoresearch-funder'] : ' ' ),
			'post_separator' => ' ',
		) );
		$this->display_text( array(
			'field_id'       => 'start-date-month',
			'class'          => 'start-date-month',
			'default_text'   => 'January',
			'value'       	 => ( ! empty( $this->data['start-date-month'] ) ? $this->data['start-date-month'] : 'January' ),
			'post_separator' => ', ',
		) );
		$this->display_text( array(
			'field_id'     => 'aopublication-year',
			'class'        => 'aopublication-year',
			'default_text' => '2015',
			'value'       	 => ( ! empty( $this->data['aoresearch-year'] ) ? $this->data['aoresearch-year'] : '2015' ),
			//'post_separator' => ' ',
		) );

		$this->display_text( array(
			'field_id'       => 'end-date-month',
			'class'          => 'end-date-month',
			'default_text'   => 'January',
			'value'       	 => ( ! empty( $this->data['start-date-month'] ) ? $this->data['start-date-month'] : 'December' ),
			'post_separator' => ', ',
		) );
		$this->display_text( array(
			'field_id'     => 'aoresearch-end-year',
			'class'        => 'aoresearch-end-year',
			'default_text' => '2015',
			'value'       	 => ( ! empty( $this->data['aoresearch-end-year'] ) ? $this->data['aoresearch-end-year'] : '2017' ),
			//'post_separator' => ' ',
		) );

		$this->display_text( array(
			'field_id'     => 'aopublication-status',
			'class'        => 'aopublication-status',
			'default_text' => 'current',
			'tag'          => 'em',
			'value'       	 => ( ! empty( $this->data['aopublication-status'] ) ? $this->data['aopublication-status'] : 'current' ),
			'post_separator' => ' ',
		) );

		$this->display_shell( array( 'class' => 'profileterms' ) );
		if ( isset( $this->data['terms'] ) ) :
			foreach ( $this->data['terms'] as $term ) :
				$this->display_text( array(
					'class'        => 'terms',
					'default_text' => 'my specialization',
					'separator'    => $separator,
					'value'        => $term,
					'tag'          => 'em',
				) );
				$separator = ', ';
			endforeach;
		endif;
		$this->display_end_shell();

		$this->display_text( array(
					'class'        => 'abstract-icon',
					'default_text' => '',
					//'separator'    => $separator,
					'value'       	 => ( ! empty( $this->data['aopublication-abstract'] ) ? '<span style="display:inline-block" class="dashicons dashicons-plus-alt"></span>' : '<span style="display:none" class="dashicons dashicons-plus-alt"></span>' ),
		) );

		$this->display_shell( array( 'class' => 'abstract' ) );
		$this->display_textfield( array(
			'field_id'     => 'aopublication-abstract',
			'class'        => 'aopublication-abstract',
			'default_text' => 'The current research at ISIT is focused on finding Featured Research Grants.',
			'value'       	 => ( ! empty( $this->data['aopublication-abstract'] ) ? $this->data['aopublication-abstract'] : ' ' ),
		) );
		$this->display_end_shell();
		$this->display_end_shell();
	}

	  /**
	 * publication_status function.
	 *
	 * @access public
	 * @return void
	 */
	function aopublication_status() {
		return array( 'planning', 'current', 'complete' );
	}

	function aoresearch_book() {
		return array( 'Book' );
	}

	function aoresearch_funder() {
		return array( 'Other','Social Sciences and Humanities Research Council (SSHRC)','National Sciences and Engineering Research Council (NSERC)','Canadian Institutes of Health Research (CIHR)','Canada Foundation for Innovation (CFI)' );
	}

	public static function shell( $options, $data ) {
		new Profile_CCT_AOResearch( $options, $data );
	}
}

function profile_cct_aoresearch_shell( $options, $data ) {
	Profile_CCT_AOResearch::shell( $options, $data );
}
