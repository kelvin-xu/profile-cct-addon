<?php
class Profile_CCT_AOPublications extends Profile_CCT_Field {
	var $default_options = array(
		'type'          => 'aopublications',
		'label'         => 'aopublications',
		'description'   => '',
		'show'          => array( 'aopublication-book','aopublication-website', 'aopublication-year', 'aopublication-status',  'aopublication-doi', 'aopublication-abstract' ),
		'show_fields'   => array( 'aopublication-book','aopublication-website', 'aopublication-year', 'aopublication-status', 'aopublication-doi', 'aopublication-abstract' ),
		'multiple'      => true,
		'show_multiple' => true,
		'width'         => 'full',
		'before'        => '',
		'empty'         => '',
		'after'         => '',
	);
	var $shell = array(
		'class' => 'aopublications',
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
		$dataarray = maybe_unserialize( get_post_meta( $post->ID,'profile_cct' ) );
		$terms_array = array();
		$terms = wp_get_post_terms( $post->ID, $pubtax, array( 'fields' => 'slugs' ) );
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
			foreach ( $terms as $term ) {
				$terms_array[] = $term;
			}
		}
		$this->input_text( array(
			'field_id' => 'aopublication-authors',
			'label'    => 'Author(s)',
			'size'     => 23,
			'value'    => ( ! empty( $this->data['aopublication-authors'] ) ? $this->data['aopublication-authors'] : $dataarray[0][ 'name' ][ 'first' ] .' '.$dataarray[0][ 'name' ][ 'middle' ].' '.$dataarray[0][ 'name' ][ 'last' ] ),
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-title',
			'label'    => 'Title',
			'size'     => 36,
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-chapter',
			'label'    => 'Journal/Book Title',
			'hidden'   => true,
			'size'     => 23,
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-bookeds',
			'label'    => 'Book Editors',
			'size'     => 23,
		) );

		$this->input_select( array(
			'field_id'   => 'aopublication-year',
			'label'      => 'Year',
			'all_fields' => $this->list_of_years( 20, -20 ),
			'value'      => ( ! empty( $this->data['aopublication-year'] ) ? $this->data['aopublication-year'] : '2015' ),
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-publisher',
			'label'    => 'Publisher',
			'hidden'   => true,
			'size'     => 23,
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-pagenumbers',
			'label'    => 'Pages',
			'size'     => 5,
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-doi',
			'label'    => 'DOI',
			'size'     => 5,
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-website',
			'label'    => 'Website - http://{value}',
			'size'     => 23,
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-image',
			'label'    => 'Image Source - http://{value}',
			'size'     => 23,
		) );

		$this->input_select( array(
			'field_id'   => 'aopublication-status',
			'label'      => 'Status',
			'all_fields' => $this->aopublication_status(),
		) );

		$this->input_textarea( array(
			'field_id' => 'aopublication-abstract',
			'label'    => 'Abstract',
			'size'     => 35,
		) );

		$this->input_multiple( array(
			'field_id'   => 'aopublication-book',
			'class'	     => 'pub-book-class',
			'selected_fields' => $this->data['aopublication-book'],
			'all_fields' => $this->aopublication_book(),
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
		$themeclasses = ( $this->data['themes']) ? implode( ' ',$this->data['themes'] ) : '';
		$termclasses = ( $this->data['terms']) ? implode( ' ',$this->data['terms'] ) : '';
		$isbook = ( $this->data['aopublication-book']) ? 'book' : 'journal';
		$this->display_shell( array( 'class' => 'pubrec publication ' . $termclasses.' '.$themeclasses.' '.$isbook.' '.$hideclasses ) );
		$separator = '';

		$this->display_text( array(
					'class'        => 'thumbnail-holder',
					'default_text' => '',
					'value'       	 => ( ! empty( $this->data['aopublication-image'] ) ? '<span class="thumbnail"></span>' : '<span class="thumbnail"></span>' ),
		) );

		if ( $this->data['book'] ) {
			foreach ( $this->data['book'] as $booktype ) :
				$this->display_text( array(
					'class'        => 'book',
					'default_text' => '',
					'separator'    => $separator,
					'value'        => $booktype,
				) );
				$separator = ', ';
			endforeach;
		}

		$this->display_text( array(
			'field_id'       => 'aopublication-authors',
			'class'          => 'aopublication-authors',
			'default_text'   => 'Bruce Wayne',
			'value'       	 => ( ! empty( $this->data['aopublication-authors'] ) ? $this->data['aopublication-authors'] : $dataarray[0][ 'name' ][ 'first' ] .' '.$dataarray[0][ 'name' ][ 'middle' ].' '.$dataarray[0][ 'name' ][ 'last' ] ),
			'post_separator' => ' ',
			'tag'            => 'strong',
		) );

		$this->display_link( array(
			'field_id'     => 'aopublication-website',
			'class'        => 'aopublication-website',
			'default_text' => 'The Publication Title',
			'value'	       => ( ! empty( $this->data['aopublication-title'] ) ? $this->data['aopublication-title'] : ' ' ),
			'maybe_link'   => true,
			'tag'          => ( ! empty( $this->data['aopublication-website'] ) ? 'a' : 'span' ),
			'href'         => ( ! empty( $this->data['aopublication-website'] ) ? $this->data['aopublication-website'] : '' ),
			'post_separator' => ' ',
		) );

		$this->display_text( array(
			'field_id'       => 'aopublication-bookeds',
			'class'          => 'aopublication-bookeds',
			'default_text'   => 'Book Editors',
			'value'       	 => ( ! ( ' ' === $this->data['aopublication-bookeds'] )  ? '' . $this->data['aopublication-bookeds'] : ' ' ),
		) );

		$this->display_link( array(
			'field_id'     => 'aopublication-image',
			'class'        => 'aopublication-image',
			'default_text' => 'The Publication Image',
			'value'	       => ( ! empty( $this->data['aopublication-image'] ) ? $this->data['aopublication-image'] : ' ' ),
			'maybe_link'   => true,
			'tag'          => ( 'span' ),
			'post_separator' => ' ',
		) );

		$this->display_text( array(
			'field_id'       => 'aopublication-chapter',
			'class'          => 'aopublication-chapter',
			'default_text'   => 'Journal/Book Title',
			'value'       	 => ( ! empty( $this->data['aopublication-chapter'] ) ? $this->data['aopublication-chapter'] : ' ' ),
			'post_separator' => ' ',
		) );

		$this->display_shell( array( 'class' => 'year-publisher' ) );
		$this->display_text( array(
			'field_id'     => 'aopublication-year',
			'class'        => 'aopublication-year',
			'default_text' => '2015',
			'value'       	 => ( ! empty( $this->data['aopublication-year'] ) ? $this->data['aopublication-year'] : '2015' ),
		) );

		$this->display_text( array(
			'field_id'       => 'aopublication-publisher',
			'class'          => 'aopublication-publisher',
			'default_text'   => 'Cambridge Press',
			'value'       	 => ( ! empty( $this->data['aopublication-publisher'] ) ? $this->data['aopublication-publisher'] : ' ' ),
		) );

		$this->display_end_shell();
		$this->display_text( array(
			'field_id'       => 'aopublication-pagenumbers',
			'class'          => 'aopublication-pagenumbers',
			'default_text'   => '40-50',
			'value'       	 => ( ! ( ' ' === $this->data['aopublication-pagenumbers'] )  ? '' . $this->data['aopublication-pagenumbers'] : ' ' ),
		) );

		$this->display_text( array(
			'field_id'       => 'aopublication-doi',
			'class'          => 'aopublication-doi',
			'default_text'   => '',
			'post_separator' => ' ',
			'value'       	 => ( ! empty( $this->data['aopublication-doi'] ) ? $this->data['aopublication-doi'] : ' ' ),
		) );

		$this->display_text( array(
			'field_id'     => 'aopublication-status',
			'class'        => 'aopublication-status',
			'default_text' => 'in progress',
			'tag'          => 'em',
			'value'       	 => ( ! empty( $this->data['aopublication-status'] ) ? $this->data['aopublication-status'] : ' ' ),
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
					'value'       	 => ( ! empty( $this->data['aopublication-abstract'] ) ? '<span style="display:inline-block" class="dashicons dashicons-plus-alt"></span>' : '<span style="display:none" class="dashicons dashicons-plus-alt"></span>' ),
		) );

		$this->display_shell( array( 'class' => 'abstract' ) );
		$this->display_textfield( array(
			'field_id'     => 'aopublication-abstract',
			'class'        => 'aopublication-abstract',
			'default_text' => 'The current research at ISIT is focused on finding Publications.',
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
		return array( 'in progress', 'under review', 'submitted' );
	}

	function aopublication_book() {
		return array( 'Book' );
	}

	public static function shell( $options, $data ) {
		new Profile_CCT_AOPublications( $options, $data );
	}
}

function profile_cct_aopublications_shell( $options, $data ) {
	Profile_CCT_AOPublications::shell( $options, $data );
}
