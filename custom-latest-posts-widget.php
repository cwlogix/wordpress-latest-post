<?php
/*
Plugin Name: Custom Latest Posts Widget
Plugin URI:  http://clientuat.xyz/plugin/latest-posts/
Description: Latest posts widget to display recent posts with post thumbnail.
Version:     1.0
Author:      Sunil Kumar
License:     GPL2
 
Custom latest posts widget is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
 
Custom latest posts widget is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
 
You should have received a copy of the GNU General Public License
along with {Plugin Name}. If not, see {License URI}.
*/

//CLPW

// Don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

define( 'CLPW_LATEST_POSTS_URL', plugins_url('/') . plugin_basename( dirname( __FILE__ ) ) . '/' );
define( 'CLPW_LATEST_POSTS_PATH', plugin_dir_path( __FILE__ ) );


/* Register thumbnail size */
if(!function_exists('custom_lpw_add_image_size')){
function custom_lpw_add_image_size() {
	$sizes = get_option( 'custom_lpw_posts_thumb_sizes' );

	if ( $sizes ) {
		foreach ( $sizes as $id => $size ) {
			add_image_size( 'custom_lpw_posts_thumb_sizes' . $id, $size[0], $size[1], true );
		}
	}
}
}
add_action( 'init', 'custom_lpw_add_image_size' );



/**
 * Register our styles
 *
 * @return void
 */
if ( !function_exists( 'custom_lpw_styles' ) ) { 
function custom_lpw_styles(){
	wp_enqueue_style( 'custom_lpw_style', CLPW_LATEST_POSTS_URL.'assets/style.css');
}
}
add_action( 'wp_enqueue_scripts','custom_lpw_styles');


if ( !class_exists( 'CLPW_Posts_Widget' ) ) {
class CLPW_Posts_Widget extends WP_Widget{
	 
	  public function __construct(){
		  parent::__construct('custom_lpw_latest_posts','Custom Latest posts',array('description'=>'Latest posts widget to display posts'));
	  }	
	  
	  /* Widget form display */
	  public function form($instance){
		  $defaults = array( 
			'title' 	=> 'Latest posts',
			'count' 	=> 5,
			'show_post_date'=>1,
			'show_post_thumbnail'=>1,
			'show_post_excerpt'=>1,
			'excerpt_length'=>50,
			'post_type'=>'post',
			'thumb_width'=>100,
			'thumb_height'=>100
			
		);
		
		
		$instance = wp_parse_args( (array) $instance, $defaults );
		$category="";
		if ( isset( $instance['category'] ) ) {
				$category = $instance['category'];
			}
		
		$selected_post_type="";
		if(isset($instance['post_type'])){
			$selected_post_type=$instance['post_type'];
		}
		
		$post_types = get_post_types(array('public'   => true)); 
		$index = array_search('attachment',$post_types);
	if ( $index !== false ) {
		unset( $post_types[$index] );
	}
	
	
		$style_lists=array('style_1'=>'style 1','style_2'=>'style 2','style_3'=>'style 3');
		  	?>
			<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>">Title</label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
			</p>
            <p>
			<label for="<?php echo $this->get_field_id( 'style' ); ?>">Style</label>
			<select id="<?php echo $this->get_field_id('style');?>" name="<?php echo $this->get_field_name('style');?>" style="width:100%">
            	<?php if(is_array($style_lists)){
					foreach($style_lists as $key=>$style_list){
						
						?>
            	<option value="<?php echo $key; ?>"  <?php selected( $instance['style'], $key ); ?> ><?php echo $style_list;?></option>
                <?php }} ?>
            </select>
            
            </p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'post_type' ); ?>">Post Type</label>
			<select id="<?php echo $this->get_field_id('post_type');?>" name="<?php echo $this->get_field_name('post_type');?>" style="width:100%">
            <?php if(is_array($post_types)):
			 foreach($post_types as $post_type):?>
           
             <option value="<?php echo $post_type;?>"  <?php selected( $selected_post_type, $post_type ); ?>><?php echo ucfirst($post_type);?></option>	
             
             <?php endforeach; endif;?>
            </select>
            
        	</p>
            
            
            
            <p>
			<label for="<?php echo $this->get_field_id( 'category' ); ?>">Select category for post type( post only )</label>
			 <select  id="<?php echo esc_attr( $this->get_field_id( 'category' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'category' ) ); ?>" style="width:100%;">
				<option value="" <?php if($category==""){ echo "selected='selected'";}?>>All category</option>
				<?php 
				$category_lists = get_categories();
				if(is_array($category_lists) && count($category_lists)){
				 foreach($category_lists as $category_list){
					 $term_id=$category_list->term_id;
				?>
                <option value="<?php echo $term_id; ?>" <?php selected( $category, $term_id ); ?> ><?php echo ($category_list->name);?></option>
                <?php }} ?>
			</select>
            
            
			</p>
            
            <p>
			<label for="<?php echo $this->get_field_id( 'count' ); ?>">Number of posts to show</label>
			<input id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>" value="<?php echo $instance['count']; ?>" style="width:100%;" />
			</p>
            
            <p>
        	<label for="<?php echo $this->get_field_id( 'show_post_date' ); ?>"><input type="checkbox" value="1" <?php if($instance['show_post_date']==1){ echo "checked='checked'";}?> id="<?php echo $this->get_field_id( 'show_post_date' ); ?>" name="<?php echo $this->get_field_name( 'show_post_date' ); ?>"/> Show post date</label>
			</p>
            
             <p>
        	<label for="<?php echo $this->get_field_id( 'show_post_thumbnail' ); ?>"><input type="checkbox" value="1" <?php if($instance['show_post_thumbnail']==1){ echo "checked='checked'";}?> id="<?php echo $this->get_field_id( 'show_post_thumbnail' ); ?>" name="<?php echo $this->get_field_name( 'show_post_thumbnail' ); ?>"/> Show post thumbnail

</label>
			</p>
            
          
            
            	<?php if ( function_exists( 'the_post_thumbnail' ) && current_theme_supports( "post-thumbnails" ) ) : ?>
			
			<p>
				<label>
					<?php _e( 'Post thumbnail dimensions (in pixels)' ); ?><br />
					<label for="<?php echo $this->get_field_id( "thumb_width" ); ?>">
						Width: <input  style="width:20%;" type="text" id="<?php echo $this->get_field_id( "thumb_width" ); ?>" name="<?php echo $this->get_field_name( "thumb_width" ); ?>" value="<?php echo $instance["thumb_width"]; ?>" />
					</label>

					<label for="<?php echo $this->get_field_id( "thumb_height" ); ?>">
						Height: <input  style="width:20%;" type="text" id="<?php echo $this->get_field_id( "thumb_height" ); ?>" name="<?php echo $this->get_field_name( "thumb_height" ); ?>" value="<?php echo $instance["thumb_height"]; ?>" />
					</label>
				</label>
			</p>
		<?php endif; ?>
        
			<?php
		  }
		  
      /* Update form field value */
	  public function update( $new_instance, $old_instance ) {
		 
		$instance = $old_instance;
		$instance['title'] 						= strip_tags( $new_instance['title'] );
		$instance['category'] 					= strip_tags( $new_instance['category'] );
		$instance['count'] 						= strip_tags( $new_instance['count'] );
		$instance['show_post_date'] 			= strip_tags( $new_instance['show_post_date'] );
		$instance['show_post_thumbnail'] 		= strip_tags( $new_instance['show_post_thumbnail'] );
		$instance['show_post_excerpt'] 			= strip_tags( $new_instance['show_post_excerpt'] );
		$instance['excerpt_length'] 			= strip_tags( $new_instance['excerpt_length'] );
		$instance['post_type'] 					= strip_tags( $new_instance['post_type'] );
		$instance['thumb_width'] 				= strip_tags( $new_instance['thumb_width'] );
		$instance['thumb_height'] 				= strip_tags( $new_instance['thumb_height'] );
		$instance['style'] 				= strip_tags( $new_instance['style'] );
		
		
		$sizes = get_option( 'custom_lpw_posts_thumb_sizes' );
		if (empty($sizes)) {
			$sizes = array( );
		}
		$sizes[$this->id] = array( $new_instance['thumb_width'], $new_instance['thumb_height'] );
		update_option( 'custom_lpw_posts_thumb_sizes', $sizes );
		
		
		return $instance;
	}
	 
	 /* Front end display */
	 public function widget($args, $instance){
	 	 extract($args);
		 $title 			= apply_filters('widget_title', $instance['title'] );
		 $count 			= $instance['count'];
		 $category 		= $instance['category'];
		 echo $before_widget;
		 if($title){ echo $before_title . $title . $after_title; }
		
		 $args="";
		 if(isset($instance['category']) && $instance['category']>0){
			 $args['category__in']=array($instance['category']);
			 }
		 if(isset($instance['count']) && $instance['count']>0){
				 $args['posts_per_page']=$instance['count'];
			}	
		 if(isset($instance['post_type'])){
				 $args['post_type']=$instance['post_type'];
			}
		 
		 $show_post_thumbnail="";	
		 if(isset($instance['show_post_thumbnail']) && $instance['show_post_thumbnail']>0){
			$show_post_thumbnail=$instance['show_post_thumbnail'];
		 }	
		 $style=$instance['style'];
		 	 
	    query_posts( $args);
		 if(have_posts()){
			?><div class="sk-posts-widget <?php echo $style;?>"><?php	 
		 	 while(have_posts()):the_post();
			 ?>
			 <div class="sk-list">
            
                    
             	<?php
                if(has_post_thumbnail() && $show_post_thumbnail){
					?>
					<div class="post-thumbnail">
                    <a href="<?php the_permalink();?>">
					<?php the_post_thumbnail( 'sk_latest_posts_thumb' . $this->id ); ?>
                    </a>
			        </div>
					<?php	
				}
				?>
             	
               
                <div class="post-content">
                  <?php if($style=="style_1"){?>
                 <h4 class="entry-title"><a href="<?php the_permalink();?>"><?php the_title();?></a></h4>
           		 <?php if(isset($instance['show_post_date']) && $instance['show_post_date']==1){?>
              	<div class="entry-meta"><?php echo get_the_date();?></div>
             	<?php } ?>
                	<?php }
					else if($style=="style_2" || $style=="style_3"){
						?>
						<?php if(isset($instance['show_post_date']) && $instance['show_post_date']==1){?>
              	<div class="entry-meta"><?php echo get_the_date();?></div>
             	<?php } ?>
                <h4 class="entry-title"><a href="<?php the_permalink();?>"><?php the_title();?></a></h4>
						<?php
						}
					
					 ?>
              	  
          	  </div>
               
                
             </div><!-- .sk-list -->
             
			 <?php
			 endwhile;
			 wp_reset_query();
			 ?>
			 </div>
			 <?php
		 }
				
		 echo $after_widget;
		 
	 }
	 

				  
	 	
}
}
	
add_action( 'widgets_init', function() { register_widget( 'CLPW_Posts_Widget' ); } );	