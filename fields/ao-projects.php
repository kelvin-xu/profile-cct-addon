<?php
Class Profile_CCT_AOProjects extends Profile_CCT_Field {
	var $default_options = array(
		'type'          => 'aoprojects',
		'label'         => 'aoprojects',
		'description'   => '',
		'show'          => array( 'aoproject-website', 'start-date-month', 'start-date-year', 'end-date-month', 'end-date-year', 'aoproject-status' ),
		'show_fields'   => array( 'aoproject-website', 'start-date-month', 'start-date-year', 'end-date-month', 'end-date-year', 'aoproject-status' ),
		'multiple'      => true,
		'show_multiple' => true,
		'width'         => 'full',
		'before'        => '',
		'empty'         => '',
		'after'         => '',
	);
	
	var $shell = array(
		'class' => 'aoprojects',
	);
	
	function field() {
		$this->input_text( array(
			'field_id' => 'aoproject-title',
			'label'    => 'Title',
			'size'     => 57,
		) );
		$this->input_textarea( array(
			'field_id' => 'aoproject-description',
			'label'    => 'Description',
			'size'     => 35,
		) );
		$this->input_text( array(
			'field_id' => 'aoproject-website',
			'label'    => 'Website - http://{value}',
			'size'     => 35,
		) );
		$this->input_select( array(
			'field_id'   => 'start-date-month',
			'label'      => 'Start Month',
			'all_fields' => $this->list_of_months()
		) );
		$this->input_select( array(
			'field_id'   => 'start-date-year',
			'label'      => 'Start Year',
			'all_fields' => $this->list_of_years(),
		) );
		$this->input_select( array(
			'field_id'   => 'end-date-month',
			'label'      => 'End Month',
			'all_fields' => $this->list_of_months()
		) );
		$this->input_select( array(
			'field_id'   => 'end-date-year',
			'label'      => 'End Year',
			'all_fields' => $this->list_of_years( 20, -20 ),
		) );
		$this->input_select( array(
			'field_id'   => 'aoproject-status',
			'label'      => 'Status',
			'all_fields' => $this->project_status(),
		) );
	}
	
	function display() {
		$this->display_text( array(
			'field_id'       => 'aoproject-title',
			'class'          => 'aoproject-title',
			'default_text'   => 'Cure for Cancer',
			'post_separator' => ' ',
			'tag'            => 'strong',
		) );
		$this->display_text( array(
			'field_id'     => 'aoproject-status',
			'class'        => 'aoproject-status',
			'default_text' => 'Current',
			'tag'          => 'em',
		) );
		$this->display_shell( array( 'class' => 'aoproject-dates') );
		$this->display_text( array(
			'field_id'       => 'start-date-month',
			'class'          => 'start-date-month',
			'default_text'   => 'January',
			'post_separator' => ', ',
		) );
		$this->display_text( array(
			'field_id'     => 'start-date-year',
			'class'        => 'start-date-year',
			'default_text' => '2006',
		) );
		$this->display_text( array(
			'field_id'       => 'end-date-month',
			'class'          => 'end-date-month',
			'default_text'   => 'December',
			'separator'      => '  -  ',
			'post_separator' => ', ',
		) );
		$this->display_text( array(
			'field_id'     => 'end-date-year',
			'class'        => 'end-date-year',
			'default_text' => '2016',
			'separator'    => ( empty( $this->data['end-date-month'] ) ? '  -  ' : '' ),
		) );
		$this->display_end_shell();
		$this->display_link( array(
			'field_id'     => 'aoproject-website',
			'class'        => 'aoproject-website',
			'default_text' => 'http://wayneenterprises.biz',
			'href'         => ( ! empty( $this->data['aoproject-website'] ) ? $this->data['aoproject-website'] : '' ),
		) );
		$this->display_textfield( array(
			'field_id'     => 'aoproject-description',
			'class'        => 'aoproject-description',
			'default_text' => 'The current research at Wayne Biotech is focused on finding a cure for cancer.',
		) );
	}
	
	public static function shell( $options, $data ) {
		new Profile_CCT_AOProjects( $options, $data ); 
	}
}

function profile_cct_aoprojects_shell( $options, $data ) {
	Profile_CCT_AOProjects::shell( $options, $data ); 
}