<?php
/**
 * Plugin Name: Authors Based on Recent Posts 
 * Description: A widget that displays authors profile.
 * Version: 1.0.1
 * Author: Position2 WAAS Team
 * Author URI: www.position2.com
 * License: A "Slug" license name e.g. GPL2
 */
class abrp_Blog_Authors extends WP_Widget {

    function __construct() {

		$widget_ops = array(  'description' => __('A widget that displays all authors based on the recent posts ', 'default') );
		$control_ops = array();

		$this->WP_Widget( 'abrp_blog_authors', __('Authors Based on Recent Posts', 'default'), $widget_ops, $control_ops );
		
		 // add plugin style
	    wp_enqueue_style('abrp_PluginStylesheet', plugins_url('abrp_style.css', __FILE__) );
	    
	    // additional fields hooks
	    add_action( 'show_user_profile', array($this,'abrp_show_extra_profile_fields'));
		add_action( 'edit_user_profile',  array($this,'abrp_show_extra_profile_fields'));
		
		// additional fields hooks
        add_action( 'personal_options_update',  array($this,'abrp_save_extra_profile_fields'));
		add_action( 'edit_user_profile_update',  array($this,'abrp_save_extra_profile_fields'));
		 
	} // end of __construct
		
	function widget( $args, $instance ) {
		extract( $args );
		//Our variables from the widget settings.
		$title = apply_filters('widget_title', $instance['title'] );
		// add style sheet 
		// end before widget 
		 echo $before_widget;
		// Display the widget title 
            echo ' <!-- BLOG AUTHORS BEGIN -->' ;         
            echo' <div id="BlogAuthorsWrapper">
        	<div id="BlogAuthorsList">';
           if ($title):
			echo $before_title . $title . $after_title;
		   endif;  

           echo '<ul class="'.$instance['profile_style'].'">';
		//Display the name 
		 
		  add_filter( 'posts_groupby',array($this,'abrp_posts_groupby'));
		  global $post;
          $auth_args = array( 'post_type' => 'post','post_status' => 'publish', 'posts_per_page'=>-1 );
          $query = new WP_Query( $auth_args );
          $author_array =  Array();
          $authors  =  Array();
         

	      while ($query->have_posts()) : $query->the_post(); 
                $author_array[] = $post->post_author;
	      endwhile; 
	      wp_reset_query();
          $countAuthor=0;
		  $hideAuthor="";
		  $role = array();
		  foreach ($author_array as $author_id ) :
				$role = get_the_author_meta('roles', $author_id);
			//var_dump($instance['role']);
			// show specific authors list based on role
			// 
		 		 
		 		   if($instance['role'] == $role[0] )  :
			            $hideAuthor = ($countAuthor >3) ? 'style="display:none"' : '';
						echo ' <li '.$hideAuthor.' >';
						echo '<a href="'.get_author_posts_url( $author_id ).'" title="'. get_the_author_meta('display_name', $author_id).'">';
							if( $_SERVER['REMOTE_ADDR'] != "127.0.0.1" ) :
							if(get_avatar($author_id,90) ) :
			   			       echo get_avatar($author_id,90,$default = plugins_url('images/blog-author.jpg' , __FILE__));
						    endif; // end of userphoto_thumb_file check
                            else :
                              echo '<img src="'.plugins_url('images/blog-author.jpg' , __FILE__).'" >';
 							endif; 
							// show if display name given 
							if(get_the_author_meta('display_name', $author_id)) :
								echo '<h2>'.get_the_author_meta('display_name', $author_id).'</h2>'; 
							else :
							// show defult firstname and last name
								echo ' <h2>'. get_the_author_meta('last_name', $author_id).'</h2>'; 
							endif; // end of display_name check 
							// show designation if given
							if(get_the_author_meta('designation', $author_id)) :
								echo '<h3">'.get_the_author_meta('designation', $author_id).'</h3>';
							endif;  // end of designation check
							echo '</a></li>';
							$countAuthor++;
			    	endif; // end if role check
		   endforeach; // end foreach loop
				echo '<script type="text/javascript">';		
				echo 'jQuery(document).ready(function() { ';
				if($hideAuthor) :
				echo ' jQuery("#BlogAuthorsList").parent().append("<div class=\"showall\"> Show All </div>"); ';
				endif;
				echo " jQuery('.showall').on('click', function(){
					   jQuery('#BlogAuthorsList').animate({ 'min-height':'900px', 'overflow':'visible'},1000);
					   jQuery('#BlogAuthorsList ul li').show();
					   jQuery(this).hide();   
					});
				}); ";
				echo '</script>';
				echo '</ul></div></div> <br class="clear">'.$after_widget;
	}  // end of widgets
	 
	function abrp_posts_groupby($groupby) {
			 global $wpdb;
			 $groupby = "{$wpdb->posts}.post_author";
			 return $groupby;
	} // end of abrp_posts_groupby function
	//Update the widget function begain

	function update( $new_instance, $old_instance ) {
				$instance = $old_instance;
				//Strip tags from title and name to remove HTML 
				$instance['title'] = strip_tags( $new_instance['title'] );
				// Strip tags from role and role to remove HTML 
				$instance['role']  = strip_tags( $new_instance['role'] );
				// Strip tags from role and role to remove HTML 
				$instance['profile_style']  = strip_tags( $new_instance['profile_style'] );
				return $instance;
	}  // end of function update

	function form( $instance ) {
				//Set up some default widget settings.
				//
				$instance['profile_style'] = array();
				$defaults = array( 'title' => __('Blog Authors', 'default'));
				$instance = wp_parse_args( (array) $instance, $defaults ); 
				// Widget Title: Text Input.
				?>
			<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'default'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:95%;" />
			</p>
			<p>
			<label for="<?php echo $this->get_field_id( 'role' ); ?>"><?php _e('Role:', 'default'); ?></label>
			<select id="<?php echo $this->get_field_id( 'role' ); ?>" name="<?php echo $this->get_field_name( 'role' ); ?>" style="width:95%;">
		   <?php wp_dropdown_roles( $instance['role'] ); ?>
			</select>  
			</p> 
			<p>
			<label for="<?php echo $this->get_field_id( 'profile_style' ); ?>"><?php _e('Profile Style:', 'default'); ?></label>
			<select id="<?php echo $this->get_field_id( 'profile_style' ); ?>" name="<?php echo $this->get_field_name( 'profile_style' ); ?>" style="width:95%;">
			<option value="square" <?php echo ($instance['profile_style'] =='square' ) ?  'selected="selected"' : "";  ?> >square</option>
			<option value="circular"  <?php echo ($instance['profile_style'] =='circular' ) ?  'selected="selected"' : "";  ?> >Rounded</option>
			</select> 
			</p> 
	<?php
	}  // end on form function 
	
	function abrp_show_extra_profile_fields($user){ ?>
				<h3>Additional profile information</h3>
				<table class="form-table">
				<tr>
					<th><label for="designation">Designation</label></th>
					<td>
						<input type="text" name="designation" id="designation" value="<?php echo esc_attr( get_the_author_meta( 'designation', $user->ID ) ); ?>" class="regular-text" /><br />
						<span class="description">Please enter your Designation.</span>
					</td>
				</tr>
		        <tr>
					<th><label for="company">Company</label></th>
					<td>
						<input type="text" name="company" id="company" value="<?php echo esc_attr( get_the_author_meta( 'company', $user->ID ) ); ?>" class="regular-text" /><br />
						<span class="description">Please enter your Company Name.</span>
					</td>
				</tr>
			</table>
<?php } 
		function abrp_save_extra_profile_fields( $user_id ) {

			if ( !current_user_can( 'edit_user', $user_id ) )
				return false;
			/* Copy and paste this line for additional fields. Make sure to change 'twitter' to the field ID. */
			update_usermeta( $user_id, 'designation', $_POST['designation'] );
			update_usermeta( $user_id, 'company', $_POST['company'] );
	}
} // end of class abrp_Blog_Authors

add_action( 'widgets_init', 'abrp_Blog_Authors_init');

	function abrp_Blog_Authors_init() {
		register_widget( 'abrp_Blog_Authors' );
	 }

add_shortcode( 'BLOG-AUTHORS', array( 'abrp_Blog_Authors', 'widget' ) );	 
