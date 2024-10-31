<?php
/**
 * @package Page_Templates_for_Posts
 * @version 1.0
 */
/*
Plugin Name: Page Templates for Posts
Plugin URI: http://wordpress.org/extend/plugins/templates-for-posts/
Description: This plugin lets you apply page templates to the posts.
Author: Gagan S Goraya
Version: 1.0
Author URI: http://gagangoraya.com/
*/

// Register a callback function for adding a custom metabox to hold the templates dropdown
add_action( 'add_meta_boxes', 'add_post_templates_meta_box' );

// Register a callback function for saving the selected template for the post
add_action( 'save_post', 'save_post_template_info' );

// Apply a filter for modifying the single_template and replace it with the selected one for this particular post
add_filter( 'single_template', 'replace_the_template_for_post', 1, 1 );


// This function adds a metabox to the edit post screen
function add_post_templates_meta_box() {

	add_meta_box( 'posttemplatesdiv', __('Templates'), 'post_templates_meta_box', 'post', 'side', 'core' );	

}

// This function renders the dropdown list of templates inside the metabox
function post_templates_meta_box( $post ) {

	if ( 'post' == $post->post_type && 0 != count( get_page_templates() ) ) {
		$template = !empty($post->page_template) ? $post->page_template : get_post_meta( $post->ID, '_wp_page_template', true);
		?>
		<p><strong><?php _e('Template') ?></strong></p>
		<label class="screen-reader-text" for="post_template"><?php _e('Post Template') ?></label><select name="post_template" id="post_template">
		<option value='default'><?php _e('Default Template'); ?></option>
		<?php page_template_dropdown($template); ?>
		</select>
		<?php
	}
}

// This is the Registered Callback function for saving the selected template info for the particular post
function save_post_template_info($post_ID) {

	// verify if this is an auto save routine. 
	// If it is our form has not been submitted, so we dont want to do anything
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
		return;

	// Check permissions
	if ( 'post' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_post', $post_id ) )
        	return;
	}
	
	$page_template = $_POST['post_template'];
	
	$post = get_post( $post_ID );

	if ( !empty($page_template) && 'post' == $post->post_type ) {
		$post->page_template = $page_template;
		$page_templates = get_page_templates();
		if ( 'default' != $page_template && !in_array( $page_template, $page_templates ) ) {
			if ( $wp_error )
				return new WP_Error( 'invalid_page_template', __( 'The page template is invalid.' ) );
			else
				return 0;
		}

		update_post_meta( $post_ID, '_wp_page_template',  $page_template );
	}
	
}

// This is the Callback function registered for the filter to replace the template for the post
function replace_the_template_for_post( $template ) {

	$id = get_queried_object_id();
	$template_slug = get_post_template_slug();
	
	if( empty( $template_slug ) )
		return $template;
	
	return( locate_template( array( get_post_template_slug() ) ) );	
}

// This function returns the template slug for the current post
function get_post_template_slug( $post_id = null ) {
	
	$post = get_post( $post_id );
	if ( 'post' != $post->post_type )
		return false;
		
	$template = get_post_meta( $post->ID, '_wp_page_template', true );

	if ( ! $template || 'default' == $template )
		return '';

	return $template;
}

?>