<?php
/*
 Plugin Name: Force HTTPS Pages
 Plugin URI: http://belabor.org
 Description: Force HTTPS on a post-by-post basis
 Author: gluten
 Version: 1.0
 Author URI: http://belabor.org
 */

function force_ssl_post_checkbox() {
	global $post;
	
	wp_nonce_field(plugin_basename(__FILE__), 'force_ssl_nonce');
		
	?>
	<div class="misc-pub-section misc-pub-section-force_ssl"><label>Force SSL: <input type="checkbox" value="1" name="force_ssl" id="force_ssl" <?php checked( get_post_meta($post->ID, 'force_ssl', true) ); ?> /></label></div>
	<?php
}
add_action('post_submitbox_misc_actions', 'force_ssl_post_checkbox');

function force_ssl_save_post( $post_id ) {
	if ( array_key_exists('force_ssl_nonce', $_POST) ) {
		if ( !wp_verify_nonce($_POST['force_ssl_nonce'], plugin_basename(__FILE__)) ) {
			return $post_id;
		}

		if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
			return $post_id;
		}

		if ( $_POST['post_type'] == 'page' ) {
			if ( !current_user_can('edit_page', $post_id) ) {
				return $post_id;
			}
		} else {
			if ( !current_user_can('edit_post', $post_id) ) {
				return $post_id;
			}
		}

		$force_ssl = (( $_POST['force_ssl'] == 1 ) ? true : false);
		if ( $force_ssl ) {
			update_post_meta($post_id, 'force_ssl', 1);
		} else {
			delete_post_meta($post_id, 'force_ssl');
		}

		return $force_ssl;
	}
	return $post_id;
}
add_action('save_post', 'force_ssl_save_post');

function force_ssl_redirect_page() {
	if( true == get_post_meta(get_the_ID(), 'force_ssl', true) && !is_ssl() ) {
		wp_safe_redirect("https://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"] );
		exit;
	}
}
add_action('template_redirect', 'force_ssl_redirect_page');
