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
		add_shortcode('the_tag_cloud', array(&$this ,'the_tag_cloud_shortcode' ));
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
		if ( $attr['number'] )
			$attr['number'] = (int)$attr['number'];
		if ( $attr['largest'] )
			$attr['largest'] = (int)$attr['largest'];
		if ( $attr['smallest'] )
			$attr['smallest'] = (int)$attr['smallest'];
		$attr['echo'] = false;
		$attr['hide_empty'] = false;
		return wp_tag_cloud( $attr );
	}

	function list_all_taxonomy($atts){
		$atts = shortcode_atts( array( 'taxonomy' => '','template' => '', 'term' => '', 'wrap' => false, 'image' => false, 'title' => false), $atts , 'list-all-taxonomy' );
        $query_array = array(
			'numberposts'   => -1,
            'post_type' => 'profile_cct',                                
        );
      	$profile = Profile_CCT::get_object();
       	if (($atts['taxonomy'] == $profile->settings['archive']['ao_use_tax'][0]) || ($atts['taxonomy'] == $profile->settings['archive']['ao_use_taxall'][0]) ){
        	if ($atts['taxonomy'] == $profile->settings['archive']['ao_use_tax'][0]) {
      			$tax_key = 'terms';
        	}
      		if ($atts['taxonomy'] == $profile->settings['archive']['ao_use_taxall'][0]) {
      			$tax_key = 'themes';
        	}
        } else {
      		$atts['term'] = ''; //don't filter
        }
      
      	if (empty($atts['template'])) {
      		$templates = array('aopublications','aoresearch','aocourses');
        } else {
        	$templates = explode(",",$atts['template']);
        }
      	
        $posts = get_posts($query_array);


		Profile_CCT_Admin::$action = 'display';
		Profile_CCT_Admin::$page   = 'page';  
        foreach($posts as $post): // begin cycle through posts of this taxonmy
      		ob_start(); 
      		foreach ($templates as $template) {
				$dataarray = maybe_unserialize(get_post_meta($post->ID,'profile_cct'));
				foreach($dataarray[0][$template] as $publication){
                	//if intra then
              		if (! empty($atts['term'])) {
      					$terms_array = $publication[$tax_key];
                    	if ($terms_array) {
							if (in_array($atts['term'],$terms_array)){
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
            if (($atts['wrap'])&&($pcount > 0)) {
              if ($atts['title']) {
                $aotitle = '<span class="'.$atts['title'].'">'.get_the_title($post->ID).'</span>';
              }
              if ($atts['image']) {
                $aoimage = '<a href="'.get_post_permalink($post->ID).'"><span class="'.$atts['image'].'" style="width:80px;height:80px;background-size:contain;display:inline-block;background-image:url('.wp_get_attachment_url( get_post_thumbnail_id($post->ID,'full') ).')">'.$aotitle.'</span></a>';
              }
              $output .= '<div class="'.$atts['wrap'].'">'.$aoimage.ob_get_contents().'</div>';
              ob_end_clean(); 
        	}
        endforeach;
		//$output = ob_get_contents();$output
		                                               
		return $output; 
	}
  
    function list_taxonomy($atts){
		$atts = shortcode_atts( array( 'taxonomy' => '', 'grouped' => false, 'template' => 'aopublications'), $atts , 'list-taxonomy' );
		$output = '';
		if ($atts['template'] == 'aopublications') 
			$uakey = 'aopublication-chapter';
		if ($atts['template'] == 'aoresearch') 
			$uakey = 'aoresearch-pi';
 		if ($atts['template'] == 'aocourses') 
			$uakey = 'aocourse-code';
		$terms = get_terms($atts['taxonomy'],array('hide_empty' => false));

		foreach( $terms as $term ):
			ob_start(); 
			Profile_CCT_Admin::$action = 'display';
			Profile_CCT_Admin::$page   = 'page';  
			  
    			$metaquery = array(
        				array(
            					'key' => 'profile_cct',
            					'value' => $term->slug,
            					'compare' => 'LIKE'
        				)
    			);
                                         
          		$posts = get_posts(array(
					'numberposts'   => -1,
            				'post_type' => 'profile_cct',
            				'meta_query' => $metaquery,                                 
          		));

			$pcount = 0;
          		foreach($posts as $post): // begin cycle through posts of this taxonmy
				$dataarray = maybe_unserialize(get_post_meta($post->ID,'profile_cct'));
				foreach($dataarray[0] as $profilefield){
					if (is_array($profilefield[0])) {
                    	if (array_key_exists($uakey,$profilefield[0])) {
								foreach($profilefield as $publication){
									$terms_array = $publication['terms'];
									//print_r($publication);
                                  	if ($terms_array) {
										if (in_array($term->slug,$terms_array)){
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
			if ($pcount > 0)
				$heading = '<h3><a href="http://profiles.adm.arts.ubc.ca/specialization/'.$term->slug.'/">'.$term->name.'</a></h3>';    
			$output .= $heading.ob_get_contents();
			ob_end_clean();                                            
		endforeach; 

		return $output; 
	}
  
    function getRandomFromBucket(&$bucket) {
    	$randomKey = array_rand($bucket, 1);
    	$randomValue = $bucket[$randomKey];
    	unset($bucket[$randomKey]);
    	return $randomValue;
  	}
  
  	function collect_buckets($uakey,$term,&$profilefield,&$bucket) {
          	if ( is_array($profilefield[0]) ){
				if ( array_key_exists($uakey,$profilefield[0] ) ) {
            			foreach( $profilefield as $publication ){
              				$terms_array = $publication['themes'];
                          	if ($terms_array) {
              					if ( in_array($term,$terms_array) ){
                					array_push( $bucket,$publication );
              					}
                            }
            			} 
				}
          	}

	}
  
  	function slot_algorithm (&$slots,&$bucket1, &$bucket2, &$bucket3){
        if ($bucket1) {
          array_push($slots,$this->getRandomFromBucket($bucket1));
          return 1;
        } else {
          if ($bucket2) {
            array_push($slots,$this->getRandomFromBucket($bucket2));
            return 2;
          } else {
            if ($bucket3) {
              array_push($slots,$this->getRandomFromBucket($bucket3));
              return 3;
            }
          }
        }
	}
  
   	function get_html_data($slot,$shape,$color,$pcount,$slotnum){
		$ao_image = $slot['aopublication-image'];
		$ao_link = $slot['aopublication-website'];
		$ao_abstract = $slot['aopublication-abstract'];
		if (array_key_exists("aopublication-chapter",$slot)){ //publication
			$ao_title = $slot['aopublication-title'];
			if ($slot['aopublication-book']) { //book chk exists
              	$ao_type = 'publication book';
				$ao_tagline =  $slot['aopublication-publisher'].': '.$slot['aopublication-year'];
			} else {
              	$ao_type = 'publication journal';
				$ao_tagline =  $slot['aopublication-chapter']; //check this
			}
		} else {
			if (array_key_exists("aoresearch-pi",$slot)){  //research
                $ao_type = 'research';
				$ao_title = $slot['aopublication-title'];
				$ao_tagline =  $slot['aoresearch-funder'];
			} else {      							  //course
                $ao_type = 'course';
				$ao_title = $slot['aocourse-code'];
				$ao_tagline =  $slot['aopublication-title'];
			}
		}
		if ($ao_image) {
          $ao_image_data = '<img class="masonimg" src="'.$ao_image.'"/>';
          $ao_hasimage = 'has-image';
        }
      
      	if ($ao_link) {
          $ao_link_data = '<a href="'.$ao_link.'">';
          $ao_link_data_end = '</a>';
        }
      
		$masondata =  '<div id="slot'.$pcount.$slotnum.'" class="grid-item '.$shape.' '.$color.' '.$ao_type.' '.$ao_hasimage.'">'.$ao_link_data.'<span class="masondata"><p class="masontitle">'.$ao_title.'</p><p class="masontag">'.$ao_tagline.'</p></span><span class="masonimg-wrap">'.$ao_image_data.'</span>'.$ao_link_data_end.'<span class="masonbgimg-wrap"></span></div>';

       	if ($ao_image) {
          	//$masondata .= '<style>#slot'.$pcount.$slotnum.':before{background: url("'.$ao_image.'") no-repeat -50% center;background-size:100%;}</style>';
        }
      	return $masondata;
	}
  
  /**
   * profile_list_shortcode function.
   * 
   * @access public
   * @param mixed $atts
   * @return void
   */
    function create_masonary(&$slots, $pcount, $pid){
    $output = '';
    $output .= '<div class="grid-item largebox bgcolor"><a href="'.get_post_permalink($pid).'"><p class="masonimgtitle">'.get_the_title($pid).'</p><img src="'.wp_get_attachment_url( get_post_thumbnail_id($pid,'full') ).'" /></a></div>';
    if ($slots[0]) {
      $output .= $this->get_html_data($slots[0],'widebox','bgltcolor',$pcount,0);
    } else {
      $output .= '<div class="grid-item widebox bgltcolor">Empty Slot</div>';
    }

    if ($slots[1] && $slots[2] && $slots[3] && $slots[4]) {
      $output .= $this->get_html_data($slots[1],'longbox','bgcolor',$pcount,1);
      $output .= $this->get_html_data($slots[2],'mediumbox m1','bgcolor',$pcount,2);
      $output .= $this->get_html_data($slots[3],'mediumbox m2','bgcolor',$pcount,3);
      $output .= $this->get_html_data($slots[4],'mediumbox m3','bgcolor',$pcount,4);
    } else {
      if ($slots[1] && $slots[2] && $slots[3]) { //no slot4
        $output .= $this->get_html_data($slots[1],'longbox','bgcolor',$pcount,1);
      	$output .= $this->get_html_data($slots[2],'mediumbox m1','bgcolor',$pcount,2);
        $output .= $this->get_html_data($slots[3],'mediumbox m2','bgcolor',$pcount,3);
        //Put an empty mediumbox here
        $output .= '<div class="grid-item mediumbox m3 bgcolor empty"></div>';
      } else {
        if ($slots[1] && $slots[2]) { //no slot3 and 4
          $output .= '<div class="grid-item longbox bgcolor empty n3 n4"></div>';
          $output .= $this->get_html_data($slots[1],'widebox n3 n4','bgcolor',$pcount,1);
          $output .= $this->get_html_data($slots[2],'mediumbox m3 n3 n4','bgcolor',$pcount,2);
        } else { //no 123
          if ($slots[1]) { // no 234
            $output .= $this->get_html_data($slots[1],'widebox','bgcolor',$pcount,1);
          	$output .= '<div class="grid-item medium bgcolor empty"></div>';
          	$output .= '<div class="grid-item longbox bgcolor empty"></div>';
          } else { //no 1234
			$output .= $this->get_html_data($slots[1],'mediumbox','bgcolor',$pcount,1);
      		$output .= $this->get_html_data($slots[2],'mediumbox','bgcolor',$pcount,2);
        	$output .= $this->get_html_data($slots[3],'mediumbox','bgcolor',$pcount,3);
            $output .= '<div class="grid-item longbox bgcolor empty"></div>';
          }	
        }
      }
    }
   	$output .= '<div class="grid-item smallbox bgdkcolor empty"></div>';
	$output .= '<div class="grid-item smallbox bgdkcolor empty"></div>';
    return $output;
  }

  
  function aolist2($atts){
    $atts = shortcode_atts( array( 'term' => 'featured', 'taxonomy' => 'profile_cct_theme', 'grouped' => false, 'template' => 'grid'), $atts , 'aolist2' );
    $output = '';

    if ($atts['term'])
      $terms = array(get_term_by('name', $atts['term'], $atts['taxonomy']));
    else
      $terms = get_terms($atts['taxonomy'],array('hide_empty' => false));

    foreach( $terms as $term ):
      //$output .= '<h3>'.$term->slug.'</h3>';
                                           
      $posts = get_posts(array(
          			'numberposts'   => -1,
                    'post_type' => 'profile_cct',                              
      ));

      $pcount = 0;
      foreach($posts as $post): // begin cycle through posts of this taxonmy
        	$dataarray = maybe_unserialize(get_post_meta($post->ID,'profile_cct'));
        	//setup the buckets
        	$rbucket = array();
        	$pbucket = array();
        	$cbucket = array();
        	$slots = array();
        	foreach($dataarray[0] as $profilefield){
          		//get all publications
          		$this->collect_buckets('aopublication-chapter',$term->slug,$profilefield,$pbucket);
          		//get all research
          		$this->collect_buckets('aoresearch-pi',$term->slug,$profilefield,$rbucket);
          		//get all courses
          		$this->collect_buckets('aocourse-code',$term->slug,$profilefield,$cbucket);
       		}
        	$output .= '<div class="'.$atts['template'].'">';
        	//Algorithm to fill slots
        	//Iteration1
    		$bucketnum = $this->slot_algorithm($slots,$rbucket, $pbucket, $cbucket);
            //Iteration2
        	if ($atts['template'] == 'grid') {
    			$bucketnum = $this->slot_algorithm($slots,$pbucket, $rbucket, $cbucket);
            } else {
              	$bucketnum = $this->slot_algorithm($slots,$rbucket, $pbucket, $cbucket);
            }
        	//Iteration3
    		$bucketnum = $this->slot_algorithm($slots,$pbucket, $rbucket, $cbucket);
            //Iteration4
        	if ($atts['template'] == 'grid') {
        		$bucketnum = $this->slot_algorithm($slots,$cbucket, $pbucket, $rbucket);
            } else {
              	$bucketnum = $this->slot_algorithm($slots,$pbucket, $rbucket, $cbucket);
            }
            //Iteration5
        	$bucketnum = $this->slot_algorithm($slots,$cbucket, $pbucket, $rbucket);
            //Iteration6
        	$bucketnum = $this->slot_algorithm($slots,$cbucket, $pbucket, $rbucket);
    		//conditional display
    		if ($atts['template'] == 'grid') {
        		$output .= $this->create_masonary($slots,$pcount,$post->ID);
            } else {
              	$output .= $this->create_masonary2($slots,$pcount,$post->ID);
            }
        	$output .= '</div>'; //close grid
    		$pcount ++;
		endforeach;                                     
    endforeach; 
    return $output; 
  }
  
    function create_masonary2(&$slots, $pcount, $pid){
    $output = '';
    $output .= '<div class="grid-item profimgbox bgcolor" style="background-image:url('.wp_get_attachment_url( get_post_thumbnail_id($pid,'full') ).')"><a href="'.get_post_permalink($pid).'"></a></div>';

      
    if ($slots[0]) {
      $output .= $this->get_html_data($slots[0],'widebox','bgltcolor',$pcount,0);
    } else {
      $output .= '<div class="grid-item widebox bgltcolor">Empty Slot</div>';
    }

    if ($slots[1] && $slots[2] && $slots[3] && $slots[4] && $slots[5]) {
      $output .= $this->get_html_data($slots[1],'widebox','bgcolor',$pcount,1);
      $output .= $this->get_html_data($slots[2],'mediumbox m1','bgcolor',$pcount,2);
      $output .= $this->get_html_data($slots[3],'mediumbox m2','bgcolor',$pcount,3);
      $output .= $this->get_html_data($slots[4],'mediumbox m3','bgcolor',$pcount,4);
      $output .= $this->get_html_data($slots[5],'mediumbox m4','bgcolor',$pcount,5);
      $output .= '<div class="grid-item wsmallbox bgdkcolor"><a href="'.get_post_permalink($pid).'"><p class="masonimgtitle">'.get_the_title($pid).'</p><span class="masonbgimg-wrap"></span></a></div>';
    } else {
      if ($slots[1] && $slots[2] && $slots[3]&& $slots[4]) { //no slot5
      	$output .= $this->get_html_data($slots[1],'widebox','bgcolor',$pcount,1);
      	$output .= $this->get_html_data($slots[2],'mediumbox m1','bgcolor',$pcount,2);
      	$output .= $this->get_html_data($slots[3],'mediumbox m2','bgcolor',$pcount,3);
      	$output .= $this->get_html_data($slots[4],'mediumbox m3','bgcolor',$pcount,4);
        $output .= '<div class="grid-item mediumbox m4 bgcolor empty"><span class="masonbgimg-wrap"></span></div>';
        $output .= '<div class="grid-item wsmallbox bgdkcolor"><a href="'.get_post_permalink($pid).'"><p class="masonimgtitle">'.get_the_title($pid).'</p><span class="masonbgimg-wrap"></span></a></div>';
      } else {
        if ($slots[1] && $slots[2] && $slots[3]) { //no slot4 and 5
      		$output .= $this->get_html_data($slots[1],'widebox','bgcolor',$pcount,1);
      		$output .= $this->get_html_data($slots[2],'mediumbox m1','bgcolor',$pcount,2);
        	$output .= $this->get_html_data($slots[3],'mediumbox m2','bgcolor',$pcount,3);
        	$output .= '<div class="grid-item mediumbox m4 bgcolor empty"><span class="masonbgimg-wrap"></span></div>';
        	$output .= '<div class="grid-item mediumbox m4 bgcolor empty"><span class="masonbgimg-wrap"></span></div>';
      		$output .= '<div class="grid-item wsmallbox bgdkcolor"><a href="'.get_post_permalink($pid).'"><p class="masonimgtitle">'.get_the_title($pid).'</p><span class="masonbgimg-wrap"></span></a></div>';
        } else { //no 123
          if ($slots[1] && $slots[2]) { // no 234
      			$output .= $this->get_html_data($slots[1],'widebox','bgcolor',$pcount,1);
      			$output .= $this->get_html_data($slots[2],'mediumbox m1','bgcolor',$pcount,2);
        		$output .= '<div class="grid-item mediumbox m4 bgcolor empty"><span class="masonbgimg-wrap"></span></div>';
        		$output .= '<div class="grid-item mediumbox m4 bgcolor empty"><span class="masonbgimg-wrap"></span></div>';
        		$output .= '<div class="grid-item mediumbox m4 bgcolor empty"><span class="masonbgimg-wrap"></span></div>';
      			$output .= '<div class="grid-item wsmallbox bgdkcolor"><a href="'.get_post_permalink($pid).'"><p class="masonimgtitle">'.get_the_title($pid).'</p><span class="masonbgimg-wrap"></span></a></div>';
          } else { //no 1234
			$output .= $this->get_html_data($slots[1],'mediumbox','bgcolor',$pcount,1);
      		$output .= $this->get_html_data($slots[2],'mediumbox','bgcolor',$pcount,2);
        	$output .= $this->get_html_data($slots[3],'mediumbox','bgcolor',$pcount,3);
            $output .= '<div class="grid-item longbox bgcolor empty"></div>';
          }	
        }
      }
    }

    return $output;
  }

}
$profile_cct_addon_shortcodes = new Profile_CCT_Addon_Shortcodes();