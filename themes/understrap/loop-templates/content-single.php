<?php
/**
 * Single post partial template
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined('ABSPATH') || exit;
?>

<article <?php post_class('news-single-post'); ?> id="post-<?php the_ID(); ?>">

	<header class="entry-header">

		<div class="entry-meta">

			<?php understrap_posted_on(); ?>

		</div><!-- .entry-meta -->

		<?php the_title('<h1 class="entry-title">', '</h1>'); ?>

		<hr/>

		<?php understrap_news_social_share() ?>

	</header><!-- .entry-header -->

	<?php echo get_the_post_thumbnail($post->ID, 'full', array('class' => 'single-post-banner-full')); ?>
	<?php
	$image_id = get_post_meta($post->ID, "custom_thumbnail_image", true);

	if (!empty($image_id)) {
		$image = wp_get_attachment_image($image_id, "full", "", array('class' => 'single-post-banner-mobile'));
		echo $image;
	} else {
		echo get_the_post_thumbnail($post->ID, 'thumbnail', array('class' => 'single-post-banner-mobile'));
	}
	?>

	<div class="entry-content">

		<?php the_content(); ?>

		<?php
		wp_link_pages(
				array(
						'before' => '<div class="page-links">' . __('Pages:', 'understrap'),
						'after' => '</div>',
				)
		);
		?>

	</div><!-- .entry-content -->

	<footer class="entry-footer">

		<?php understrap_entry_footer(); ?>

	</footer><!-- .entry-footer -->

</article><!-- #post-## -->
