<?php
/**
 * Headers
 *
 * @package THEME_PACKAGE
 * @subpackage THEME_SUBPACKAGE
 * @since THEME_SINCE
 */
//print_r(debug_backtrace());
?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php bloginfo('name'); ?> <?php wp_title( '|', true, 'left' ); ?></title>
	<link rel="profile" href="http://gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
	<!--[if lt IE 9]>
	<script src="<?php echo WORDPRESS_THEME_URL; ?>js/html5.js"></script>
	<![endif]-->


	<!-- Bootstrap core CSS -->
	<link href="<?php echo WORDPRESS_THEME_URL; ?>bootstrap/css/bootstrap.min.css" rel="stylesheet">

	<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
	<!--[if lt IE 9]>
		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
	<![endif]-->


	<!-- System Files -->
	<script src="<?php echo WORDPRESS_THEME_URL; ?>js/jquery-3.3.1.min.js"></script>
	<script src="<?php echo WORDPRESS_THEME_URL; ?>js/velocity.min.js"></script>
	<script src="<?php echo WORDPRESS_THEME_URL; ?>bootstrap/js/bootstrap.min.js"></script>



	<!-- The theme.less file should go last. -->
	<link href="<?php echo WORDPRESS_THEME_URL; ?>resources/css.php?file=theme.less" rel="stylesheet">

	<?php wp_head(); ?>
</head>

<body>
	<!--#BEGIN-TOP-->
	<div class="navbar <?php if (is_front_page()) { echo 'home'; }  ?>" role="navigation">
		<div class="container">
			<?php
			wp_nav_menu( array(
				'menu'              => 'primary',
				'theme_location'    => 'primary',
				'depth'             => 2,
				'container'         => 'div',
				'container_class'   => 'collapse navbar-collapse',
				'container_id'      => 'bs-navbar',
				'menu_class'        => 'nav navbar-nav',
				'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
				'walker'            => new wp_bootstrap_navwalker())
			);
			?>
		</div>
	</div>