<?php
/**
 * Post rendering content according to caller of get_template_part
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;
?>
<div class="col-12 col-sm-6 col-md-4 col-lg-3 d-flex align-items-stretch">
	<div <?php post_class("card"); ?> id="post-<?php the_ID(); ?>">
		<div class="row">
			<div class="col-6 col-sm-12">
				<a href="<?php the_permalink(); ?>">
					<?php
					$image_id = get_post_meta($post->ID, "custom_thumbnail_image", true);

					if (!empty($image_id)) {
						$image = wp_get_attachment_image($image_id, "full");
						echo $image;
					} else {
						the_post_thumbnail('thumbnail', array(170, 170));
					}
					?>
				</a>
			</div>
			<div class="col-6 col-sm-12">
				<div class="card-body">
					<?php understrap_posted_on(); ?>
					<h5 class="card-title"><a
								href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>
					<p class="card-text"><?php echo get_the_excerpt(); ?></p>
					<?php
					wp_link_pages(
							array(
									'before' => '<div class="page-links">' . __('Pages:', 'understrap'),
									'after' => '</div>',
							)
					);
					?>
					<?php understrap_entry_footer(); ?>
				</div>
			</div>
		</div>
	</div>
</div>
