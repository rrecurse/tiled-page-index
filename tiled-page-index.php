<?php
/*
Plugin Name: Tiled Page Index
Description: Displays tiled index of pages using tile-index Shortcode; uses the Parent Page field to determine content of index or one of supplied pageid or slug parameters. This multi-site compatible plugin is a fork of plugin azurecurve Page Index (forked to extend options to include classes, dates and sort modes. Also fixes some mobile responsive issues).
Version: 1.0
Author: cdebellis
License: None

Text Domain: tile-index
*/

add_shortcode('tile-index', 'tile_display_page_index' );

function tiles_load_assets(){
	wp_enqueue_style('tile-index', plugins_url('assets/style.css', __FILE__), '', '1.0.0');
	wp_enqueue_script('tile-js', plugins_url('assets/tile-js.js', __FILE__), array('jquery'), '', true);

}
add_action('wp_enqueue_scripts', 'tiles_load_assets');

function tile_display_page_index($atts, $content = null) {

	extract(shortcode_atts(array('pageid' => '', 'slug' => '', 'order' => '', 'class' => '', 'date' => ''), $atts));
	
	global $wpdb, $post;

	$pageid = intval($pageid);
	$slug = sanitize_text_field($slug);
	$order = sanitize_text_field($order);
	$class = sanitize_text_field($class);
	$date = sanitize_text_field($date);

	switch ($order) {
      case 'date-asc':
      	$order = "ORDER BY post_date ASC";
        break;
      case 'date-desc':
      	$order = "ORDER BY post_date DESC";
        break;
	  case 'name-asc':
      	$order = "ORDER BY post_title ASC";
        break;
	  case 'name-desc':
      	$order = "ORDER BY post_title DESC";
        break;
 	  case 'id-asc':
      	$order = "ORDER BY ID ASC";
        break;
      case 'id-desc':
      	$order = "ORDER BY ID DESC";
        break;
      default:
      	$order = "ORDER BY menu_order, post_title ASC";
      	break;
	}

	if (strlen($postid) > 0){
		$pageid = $postid;
	} elseif (strlen($slug) > 0){
		$page = get_page_by_path($slug);
		$pageid = $page->ID;
	} else {
		$pageid = get_the_ID();
	}

	$tile_rows = $wpdb->get_results($wpdb->prepare("SELECT ID, post_title, post_name, post_date, post_modified FROM ". $wpdb->prefix ."posts WHERE post_status = 'publish' AND post_type = 'page' AND post_parent=%s " . $order, $pageid));

	$output = '';
	$allowed_roles = array();
	$uniques = array();
	$allowed = array();
	$rol = array();

	//$tile_rows = array_unique($tile_rows);
	foreach ($tile_rows as $key => $tiles){

		// # find the destination URL
		$tile_url = get_permalink(get_page_by_title($tiles->post_title));

		// # cast tiles->ID as an integer
		$tile_id = (int)$tiles->ID;

		switch ($date) {
	  	  case 'last-updated':
      		$post_date = $tiles->post_modified;
        	break;
      	  default:
      		$post_date = $tiles->post_date;
      		break;
		}

		// # inherit-child usage = will inherit the first class found for any matches on [tile-index .... class="inherit-child".....]
		switch ($class) {

		  case 'inherit-child':

		  	// # cleanup the inherit-child class name:
		  	$class = str_replace('inherit-child', '', $class);
			$child_classes = array();
			$child_class_ids = array();

			$content = get_children(array('post_parent' => get_the_ID()));

			foreach ($content as $post_array) {
				$child_id = $post_array->ID;
				$post_content = $post_array->post_content;

				// # find occurences of shorttag [tile-index ... ]
				//if(preg_match('|'.'\[tile-index'.'(.*?)'.'\]'.'|s', $post_content, $matches)) {
					
					preg_match('|'.'\[tile-index'.'(.*?)'.'\]'.'|s', $post_content, $matches);

					// # find first occurence of class name
					$post_content = strchr($matches[0], 'class="');

					// # find matches inside class name
					preg_match('|'.'class="'.'(.*?)'.'"'.'|s', $post_content, $results);

					// # if class match exists, assign the match to $child_class
					if(!empty($results)) {
						$child_class = str_replace('"', '', $results[1]) . '-' . $child_id;

						// # find the child class id
						$child_class_ids[] = (int)substr($child_class, strrpos($child_class, '-') + 1);

						$child_classes[] = $child_class;
					}
	
				//}
			}
	      break;

	      default:
			$class = $class;
	      break;
		}

		if(!empty($child_class_ids) && in_array($tile_id, $child_class_ids)) {

			foreach ($child_classes as $child_class) {
				$child_id = (int)substr($child_class, strrpos($child_class, '-') + 1);
				$child_classname = substr($child_class, 0, strripos($child_class, '-'));

				if($tile_id == $child_id){

					$tile_block = '<li id="tile-'. $tile_id .'" '.($child_classname ? ' class="' . $child_classname.'"' : '').'>
									<a href="'.$tile_url.'" id="tile_href_'.$tile_id.'" class="tiles_href'.(!empty($class) ? ' '.$class : '') .'">'.$tiles->post_title.'</a>
									<span class="tiles_date">'.date('m/d/Y', strtotime($post_date)).'</span>
								   </li>';
				}
			}
			
		} else {

			$tile_block = '<li id="tile-'. $tile_id .'"><a href="'.$tile_url.'" id="tile_href_'.$tile_id.'" class="tiles_href'.(!empty($class) ? ' '.$class : '') .'">'.$tiles->post_title.'</a>
							<span class="tiles_date">'.date('m/d/Y', strtotime($post_date)).'</span>
						   </li>';
		}

		if(is_plugin_active('pcr-restrict-page-by-role/pcr-restrict-page-by-role.php')) {

			$restrict_access = get_post_meta($tile_id, 'pcr-rpbr_restrict_access', true );

			if($restrict_access == 1) {

				if(!empty(get_post_meta($tile_id, 'pcr-rpbr_select_role', true))) {

					// # push all results into $allowed_roles array 
					// # use get_post_meta() function and set return object to false
					$allowed_roles = get_post_meta($tile_id, 'pcr-rpbr_select_role', true);

					// # loop through allowed_roles array
					foreach($allowed_roles as $allowed){

						if(is_array($allowed)) { // # if $allowed is array, iterate through it
					    	foreach($allowed as $rl){
					    		// # insert values into empty array rol[];
					        	$rol[] = $rl;
					    	}
					    } else { // # if $allowed is NOT an array, insert value into array rol[];
					    	$rol[] = $allowed;
					    }
					}
					
					// # cleanup duplicates in final $rol[] array
					$rol = array_unique($rol);
				}

				// # loop through rol[] array
				foreach ($rol as $role) {

					// # if role exists in $current_role array or user is admin, continue.
					if(current_user_can($role) || current_user_can('administrator') || current_user_can('editor')) {

						if(!in_array($tile_id, $uniques)) {
							$uniques[] = $tile_id;

							// # get the output from above
							$output .= $tile_block;
						}

						$uniques[$tile_id] = $tile_id;

					} else {
						unset($tile_rows[$key]);
					}
				}

			} else {
				$output .= $tile_block;
			}

		} else {

			$output .= $tile_block;
		}
	}

	if(!empty($output)) {
		
		return '<ul class="tiles_container">'.$output.'</ul>';
		
	} else {

			$output = '<div class="special-404 center">
							<i class="fa fa-warning"></i>
						</div>
						<div class="heading">
							<h2>'. __( 'No Content') .'</h2>
						</div>
						<p class="blog-sum-up center"> '.__( 'There are is no active content to display at this time.') .'</p>
							<div class="blog-button center">
								<a href="'.get_home_url().'" class="btn btn-default"><i class="fa fa-arrow-left"></i>' . __('Back on the home page'). '</a>
							</div>
					  ';

		return $output;
	}
}