<?php
/**
 * Template Name: Individual Page Page
 *
 * @package Unknown
 * @subpackage Unknown
 * @since Unknown
 */
get_header(); 

if (have_posts()) {
	the_post();
}
else {
	// 404
}
?>

<div class="page-single">

</div><?php
get_footer();

