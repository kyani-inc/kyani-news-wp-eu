<?php

/**
 * Sidebar setup for footer full
 *
 * @package understrap
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

$container = get_theme_mod('understrap_container_type');

?>

<?php if (is_active_sidebar('footerfull')) : ?>

	<!-- ******************* The Footer Full-width Widget Area ******************* -->

	<div class="wrapper" id="wrapper-footer-full">

		<div class="container" id="footer-full-content">

			<div class="row footer-menu">

				<?php dynamic_sidebar('footerfull'); ?>

			</div>

			<div class="row footer-logo">
				<?php dynamic_sidebar('footerlogo') ?>
			</div>

			<div class="row footer-social-icons">
				<?php dynamic_sidebar("footersocial") ?>
			</div>

			<div class="row footer-additional-text">
				<?php dynamic_sidebar('footertext') ?>
			</div>

		</div>

	</div><!-- #wrapper-footer-full -->

<?php endif;
