<?php
/**
 * The template for displaying archive pages
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;

get_header();

$container = get_theme_mod('understrap_container_type');
?>

	<div class="wrapper" id="archive-wrapper">

		<div class="<?php echo esc_attr($container); ?>" id="content" tabindex="-1">

			<div class="row">

				<!-- Do the left sidebar check -->
				<?php get_template_part('global-templates/left-sidebar-check'); ?>

				<main class="site-main" id="main">
					<?php

					get_search_form();

					if (is_post_type_archive()) {
						understrap_featured_carousel();
					}

					if (have_posts()) {
						// Start the loop. ?>

						<div class="post-cards-grid">
							<div class="row">
								<?php
								while (have_posts()) {
									the_post();

									/*
									 * Include the Post-Format-specific template for the content.
									 * If you want to override this in a child theme, then include a file
									 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
									 */
									get_template_part('loop-templates/content', get_post_format());
								} ?>
							</div>
						</div>
						<?php
					} else {
						get_template_part('loop-templates/content', 'none');
					}
					?>

				</main><!-- #main -->

				<?php
				// Display the pagination component.
				understrap_pagination();
				// Do the right sidebar check.
				get_template_part('global-templates/right-sidebar-check');
				?>

			</div><!-- .row -->

		</div><!-- #content -->

	</div><!-- #archive-wrapper -->

<?php
get_footer();
