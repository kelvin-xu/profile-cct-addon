<?php
Class Profile_CCT_AOCourses extends Profile_CCT_Field {
	var $default_options = array(
		'type'          => 'aocourses',
		'label'         => 'aocourses',
		'description'   => '',
		'show'          => array( 'aocourse-code','aopublication-website'),
		'show_fields'   => array( 'aocourse-code','aopublication-website'),
		'multiple'      => true,
		'show_multiple' => true,
		'width'         => 'full',
		'before'        => '',
		'empty'         => '',
		'after'         => '',
	);
	
	var $shell = array(
		'class' => 'aocourses',
	);
	
	function field() {
		$profile = Profile_CCT::get_object();
		$pubtax = $profile->settings['archive']['ao_use_tax'][0];
		$pubtaxall = $profile->settings['archive']['ao_use_taxall'][0];
		$this->display_shell( array( 'class' => 'handle') );
		//Add formatted publication here!! (readonly)
		$this->display();
		$this->display_end_shell();
		$this->display_shell( array( 'class' => 'content-wrap') );
		global $post;
$dataarray = maybe_unserialize(get_post_meta($post->ID,'profile_cct'));
		//######## Need to check if setting "Publication Taxonomy" selected (only one) and grab taxonomy
		$terms_array = array();
		$terms = wp_get_post_terms($post->ID, $pubtax, array("fields" => "slugs"));
		if ( ! empty( $terms ) && ! is_wp_error( $terms ) ){
    			foreach ( $terms as $term ) {
        			$terms_array[] =  $term;
    			}
		}
		$this->input_text( array(
			'field_id' => 'aocourse-code',
			'label'    => 'Course Code',
			'size'     => 7,
		) );

		$this->input_text( array(
			'field_id' => 'aopublication-title',
			'label'    => 'Title',
			'size'     => 57,
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
		$themes = get_terms( $pubtaxall, array('hide_empty' => false) );
		if ( ! empty( $themes ) && ! is_wp_error( $themes ) ){
    			foreach ( $themes as $theme ) {
        			$themes_array[] =  $theme->slug;
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
		$dataarray = maybe_unserialize(get_post_meta($post->ID,'profile_cct'));
        $themeclasses = ($this->data['themes']) ? implode(' ',$this->data['themes']) : '';  
        $termclasses = ($this->data['terms']) ? implode(' ',$this->data['terms']) : '';  
      	$this->display_shell( array( 'class' => 'pubrec course ' . $termclasses.' '.$themeclasses ) ); 
		$separator = "";

		$this->display_text( array(
					'class'        => 'thumbnail-holder',
					'default_text' => '',
					//'separator'    => $separator,
					'value'       	 => ( ! empty( $this->data['aopublication-image'] ) ? '<span class="thumbnail"></span>' : '' ),
		) );

		$this->display_text( array(
			'field_id'       => 'aocourse-code',
			'class'          => 'aocourse-code',
			'default_text'   => 'ANTH 100',
			'value'	       => ( ! empty( $this->data['aocourse-code'] ) ? $this->data['aocourse-code'] : ' ' ),
			'post_separator' => ' ',
			'tag'            => 'strong',
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
					'class'        => 'abstract-icon',
					'default_text' => '',
					//'separator'    => $separator,
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
	
	public static function shell( $options, $data ) {
		new Profile_CCT_AOCourses( $options, $data ); 
	}
}

function profile_cct_aocourses_shell( $options, $data ) {
	Profile_CCT_AOCourses::shell( $options, $data ); 
}