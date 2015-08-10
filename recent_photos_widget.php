<?php
/*

  Plugin Name: Recent photos widget
  Plugin URI: http://www.stigcq.com/donate
  Description: Plugin for widget showing photos from posts
  Author: Stig Hansen
  Author URI: http://www.stigcq.com
  Version: 1.2
  Copyright: © 2015 www.stigcq.com

 */

class wp_photo_widget_plugin extends WP_Widget {

    // constructor
    function wp_photo_widget_plugin() {
        parent::__construct("posts_photos_widget", $name = 'Show post photos widget');
    }

    // widget form creation
    function form($instance) {

        if ($instance) {
            $title = esc_attr($instance['wp_photo_widget_plugin_title']);
            $showx = esc_attr($instance['wp_photo_widget_plugin_showx']);
            $maxwidth = esc_attr($instance['wp_photo_widget_plugin_width']);
            $maxheight = esc_attr($instance['wp_photo_widget_plugin_height']);
        } else {
            $title = 'Recent photos';
            $showx = '10';
            $maxwidth = 50;
            $maxheight = 50;
        }
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('loadurl'); ?>"><?php _e('Title', 'wp_widget_plugin'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('wp_photo_widget_plugin_title'); ?>" name="<?php echo $this->get_field_name('wp_photo_widget_plugin_title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('showx'); ?>"><?php _e('# photos to show:', 'wp_widget_plugin'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('wp_photo_widget_plugin_showx'); ?>" name="<?php echo $this->get_field_name('wp_photo_widget_plugin_showx'); ?>" type="text" value="<?php echo $showx; ?>" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('showx'); ?>"><?php _e('Max thumbnail width:', 'wp_widget_plugin'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('wp_photo_widget_plugin_width'); ?>" name="<?php echo $this->get_field_name('wp_photo_widget_plugin_width'); ?>" type="text" value="<?php echo $maxwidth; ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('showx'); ?>"><?php _e('Max thumbnail height:', 'wp_widget_plugin'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('wp_photo_widget_plugin_height'); ?>" name="<?php echo $this->get_field_name('wp_photo_widget_plugin_height'); ?>" type="text" value="<?php echo $maxheight; ?>" />
        </p>


        <?php
    }

    // widget update
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        // Fields
        $instance['wp_photo_widget_plugin_title'] = strip_tags($new_instance['wp_photo_widget_plugin_title']);
        $instance['wp_photo_widget_plugin_showx'] = strip_tags($new_instance['wp_photo_widget_plugin_showx']);
        $instance['wp_photo_widget_plugin_width'] = strip_tags($new_instance['wp_photo_widget_plugin_width']);
        $instance['wp_photo_widget_plugin_height'] = strip_tags($new_instance['wp_photo_widget_plugin_height']);

        return $instance;
    }

    // display widget
    function widget($args, $instance) {
        extract($args);
        
        wp_enqueue_script("jquery");
        wp_enqueue_style('recent_photos_widget_css');
        wp_enqueue_script('recent_photos_widget_js');

        $showx = $instance['wp_photo_widget_plugin_showx'];
        $title = $instance['wp_photo_widget_plugin_title'];
        $maxwidth = $instance['wp_photo_widget_plugin_width'];
        $maxheight = $instance['wp_photo_widget_plugin_height'];

        echo '    <div class="widgetblock">
    <div class="widgettitleb">
    <h3 class="widgettitle">' . $title . '</h2></div>';


        if ($images = get_posts(array(
            'post_parent' => $post->ID,
            'post_type' => 'attachment',
            'numberposts' => 20,
            'post_mime_type' => 'image',))) {
            
            $counter = 1;
            
            foreach ($images as $image) {
                if ($image->post_parent == 0)
                    continue;
                
                
                if($counter > $showx)
                    break;

                $postParent = get_post($image->post_parent);

                if($postParent->post_status != "publish")
                    continue;
                
                $attachmenturl = wp_get_attachment_url($image->ID);
                $attachmentimage = wp_get_attachment_image_src($image->ID, thumbnail);
                $imageDescription = apply_filters('the_description', $image->post_content);
                $imageTitle = apply_filters('the_title', $image->post_title);
		
                $excl = get_post_meta( $image->ID, '_exclude_recent_photos', true ) ;

		if($excl == 1)
			continue; 
                
                
                    echo '<a href="' . get_permalink($image->post_parent) . '"><img rel="tooltip" title="Testing" style="max-width: ' . $maxwidth. 'px; max-height: ' . $maxheight . 'px; padding: 2px 2px 2px 2px" src="' . $attachmentimage[0] . '" alt="" /></a>';
            
                    $counter++;
                
            }
        } else {
            echo "No photos";
        }
        ?>


        </div>

        <?php
    }

}

add_action('widgets_init', create_function('', 'return register_widget("wp_photo_widget_plugin");'));

wp_register_style('recent_photos_widget_css', plugins_url('style.css',__FILE__ ));
wp_register_script( 'recent_photos_widget_js', plugins_url('recent_photos_widget.js',__FILE__ ));


add_filter('attachment_fields_to_edit', 'edit_media_custom_field', 11, 2 );
add_filter('attachment_fields_to_save', 'save_media_custom_field', 11, 2 );

function edit_media_custom_field( $form_fields, $post ) {
    $form_fields['custom_field'] = array( 'label' => 'Exclude recent photos', 'input' => 'text', 'value' => get_post_meta( $post->ID, '_exclude_recent_photos', true ) );
    return $form_fields;
}

function save_media_custom_field( $post, $attachment ) {
    update_post_meta( $post['ID'], '_exclude_recent_photos', $attachment['custom_field'] );
    return $post;
}

?>