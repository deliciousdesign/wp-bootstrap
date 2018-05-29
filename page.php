<?php
/**
 * Template Name: Default Page Template
 *
 * @package Unknown
 * @subpackage Unknown
 * @since Unknown
 */
 
get_header(); 

if (is_front_page()) {
	?>
	<section class="page">
		....
	</section>
	<?php
}
?>
<?php
get_footer();