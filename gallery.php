<?php
/*
Plugin Name: WP Gallery Pages
Plugin URI: https://github.com/garyblankenship/wp-gallery-pages
Description: Rewrite the gallery shortcode to display a paginated gallery.
Version: 20151004
Author: Gary Blankenship
Author URI: https://github.com/garyblankenship/
License: GPL v2
*/

defined( 'WPINC' ) OR die();

//* include the options page
include_once 'gallery_settings.php';

//* Override the standard gallery shortcode with our own
add_filter( 'the_post', 'wp_gallery_pages', 10, 1 );

/**
 * gallery pages
 *
 * replace the gallery shortcode output with chunked paginated versions
 */
function wp_gallery_pages( $post ) {

	//* do nothing inside the admin
	if ( is_admin() ) {
		return $post;
	}

	//* skip posts that do not contain a gallery shortcode
	if ( strpos( $post->post_content, '[gallery' ) === false ) {
		return $post;
	}

	//* skip posts that explicitly declare ids
	if ( strpos( $post->post_content, 'ids="' ) !== false ) {
		return $post;
	}

	//* @todo move this to an options page
	$options  = get_option( 'gallery_pages' );
	$per_page = $options['per_page'];

	//* get the attachments ids
	$options = array(
		'post_parent'    => get_the_ID(),
		'post_status'    => 'inherit',
		'post_type'      => 'attachment',
		'post_mime_type' => 'image',
		'posts_per_page' => - 1,
		'fields'         => 'ids'
	);

	$query       = new WP_Query( $options );
	$attachments = $query->get_posts();

	if ( count( $attachments ) < 1 ) {
		return $post;
	}

	$attributes = '';
	if ( preg_match( '/\[gallery(.*)\]/', $post->post_content, $match ) ) {
		$attributes = $match[1];
	}

	$chunks = array_chunk( $attachments, $per_page );

	$gallery = [ ];
	foreach ( $chunks as $chunk ) {
		$gallery_shortcode = sprintf( '[gallery %s ids="%s"]',
			$attributes, implode( ',', $chunk ) );
		$gallery[]         = $gallery_shortcode;
	}
	$gallery_pages = implode( PHP_EOL . '<!--nextpage-->' . PHP_EOL, $gallery );

	$post->post_content = preg_replace( '/\[gallery(.*)\]/', $gallery_pages, $post->post_content );

	// remove_filter( 'the_post', 'wp_gallery_pages' );

	return $post;
}
