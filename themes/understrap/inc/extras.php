<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features.
 *
 * @package UnderStrap
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

add_filter( 'body_class', 'understrap_body_classes' );

if ( ! function_exists( 'understrap_body_classes' ) ) {
	/**
	 * Adds custom classes to the array of body classes.
	 *
	 * @param array $classes Classes for the body element.
	 *
	 * @return array
	 */
	function understrap_body_classes( $classes ) {
		// Adds a class of group-blog to blogs with more than 1 published author.
		if ( is_multi_author() ) {
			$classes[] = 'group-blog';
		}
		// Adds a class of hfeed to non-singular pages.
		if ( ! is_singular() ) {
			$classes[] = 'hfeed';
		}

		return $classes;
	}
}

if ( function_exists( 'understrap_adjust_body_class' ) ) {
	/*
	 * understrap_adjust_body_class() deprecated in v0.9.4. We keep adding the
	 * filter for child themes which use their own understrap_adjust_body_class.
	 */
	add_filter( 'body_class', 'understrap_adjust_body_class' );
}

// Filter custom logo with correct classes.
add_filter( 'get_custom_logo', 'understrap_change_logo_class' );

if ( ! function_exists( 'understrap_change_logo_class' ) ) {
	/**
	 * Replaces logo CSS class.
	 *
	 * @param string $html Markup.
	 *
	 * @return string
	 */
	function understrap_change_logo_class( $html ) {

		$html = str_replace( 'class="custom-logo"', 'class="img-fluid"', $html );
		$html = str_replace( 'class="custom-logo-link"', 'class="navbar-brand custom-logo-link"', $html );
		$html = str_replace( 'alt=""', 'title="Home" alt="logo"', $html );

		return $html;
	}
}

if ( ! function_exists( 'understrap_post_nav' ) ) {
	/**
	 * Display navigation to next/previous post when applicable.
	 */
	function understrap_post_nav() {
		// Don't print empty markup if there's nowhere to navigate.
		$previous = ( is_attachment() ) ? get_post( get_post()->post_parent ) : get_adjacent_post( false, '', true );
		$next     = get_adjacent_post( false, '', false );

		if ( ! $next && ! $previous ) {
			return;
		}
		?>
		<nav class="container navigation post-navigation">
			<h2 class="sr-only"><?php esc_html_e( 'Post navigation', 'understrap' ); ?></h2>
			<div class="row nav-links justify-content-between">
				<?php
				if ( get_previous_post_link() ) {
					previous_post_link( '<span class="nav-previous">%link</span>', _x( '<i class="fa fa-angle-left"></i>&nbsp;%title', 'Previous post link', 'understrap' ) );
				}
				if ( get_next_post_link() ) {
					next_post_link( '<span class="nav-next">%link</span>', _x( '%title&nbsp;<i class="fa fa-angle-right"></i>', 'Next post link', 'understrap' ) );
				}
				?>
			</div><!-- .nav-links -->
		</nav><!-- .navigation -->
		<?php
	}
}

if ( ! function_exists( 'understrap_pingback' ) ) {
	/**
	 * Add a pingback url auto-discovery header for single posts of any post type.
	 */
	function understrap_pingback() {
		if ( is_singular() && pings_open() ) {
			echo '<link rel="pingback" href="' . esc_url( get_bloginfo( 'pingback_url' ) ) . '">' . "\n";
		}
	}
}
add_action( 'wp_head', 'understrap_pingback' );

if ( ! function_exists( 'understrap_mobile_web_app_meta' ) ) {
	/**
	 * Add mobile-web-app meta.
	 */
	function understrap_mobile_web_app_meta() {
		echo '<meta name="mobile-web-app-capable" content="yes">' . "\n";
		echo '<meta name="apple-mobile-web-app-capable" content="yes">' . "\n";
		echo '<meta name="apple-mobile-web-app-title" content="' . esc_attr( get_bloginfo( 'name' ) ) . ' - ' . esc_attr( get_bloginfo( 'description' ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'understrap_mobile_web_app_meta' );

if ( ! function_exists( 'understrap_default_body_attributes' ) ) {
	/**
	 * Adds schema markup to the body element.
	 *
	 * @param array $atts An associative array of attributes.
	 * @return array
	 */
	function understrap_default_body_attributes( $atts ) {
		$atts['itemscope'] = '';
		$atts['itemtype']  = 'http://schema.org/WebSite';
		return $atts;
	}
}
add_filter( 'understrap_body_attributes', 'understrap_default_body_attributes' );

// Escapes all occurances of 'the_archive_description'.
add_filter( 'get_the_archive_description', 'understrap_escape_the_archive_description' );

if ( ! function_exists( 'understrap_escape_the_archive_description' ) ) {
	/**
	 * Escapes the description for an author or post type archive.
	 *
	 * @param string $description Archive description.
	 * @return string Maybe escaped $description.
	 */
	function understrap_escape_the_archive_description( $description ) {
		if ( is_author() || is_post_type_archive() ) {
			return wp_kses_post( $description );
		}

		/*
		 * All other descriptions are retrieved via term_description() which returns
		 * a sanitized description.
		 */
		return $description;
	}
} // End of if function_exists( 'understrap_escape_the_archive_description' ).

// Escapes all occurances of 'the_title()' and 'get_the_title()'.
add_filter( 'the_title', 'understrap_kses_title' );

// Escapes all occurances of 'the_archive_title' and 'get_the_archive_title()'.
add_filter( 'get_the_archive_title', 'understrap_kses_title' );

if ( ! function_exists( 'understrap_kses_title' ) ) {
	/**
	 * Sanitizes data for allowed HTML tags for post title.
	 *
	 * @param string $data Post title to filter.
	 * @return string Filtered post title with allowed HTML tags and attributes intact.
	 */
	function understrap_kses_title( $data ) {
		// Tags not supported in HTML5 are not allowed.
		$allowed_tags = array(
			'abbr'             => array(),
			'aria-describedby' => true,
			'aria-details'     => true,
			'aria-label'       => true,
			'aria-labelledby'  => true,
			'aria-hidden'      => true,
			'b'                => array(),
			'bdo'              => array(
				'dir' => true,
			),
			'blockquote'       => array(
				'cite'     => true,
				'lang'     => true,
				'xml:lang' => true,
			),
			'cite'             => array(
				'dir'  => true,
				'lang' => true,
			),
			'dfn'              => array(),
			'em'               => array(),
			'i'                => array(
				'aria-describedby' => true,
				'aria-details'     => true,
				'aria-label'       => true,
				'aria-labelledby'  => true,
				'aria-hidden'      => true,
				'class'            => true,
			),
			'code'             => array(),
			'del'              => array(
				'datetime' => true,
			),
			'ins'              => array(
				'datetime' => true,
				'cite'     => true,
			),
			'kbd'              => array(),
			'mark'             => array(),
			'pre'              => array(
				'width' => true,
			),
			'q'                => array(
				'cite' => true,
			),
			's'                => array(),
			'samp'             => array(),
			'span'             => array(
				'dir'      => true,
				'align'    => true,
				'lang'     => true,
				'xml:lang' => true,
			),
			'small'            => array(),
			'strong'           => array(),
			'sub'              => array(),
			'sup'              => array(),
			'u'                => array(),
			'var'              => array(),
		);
		$allowed_tags = apply_filters( 'understrap_kses_title', $allowed_tags );

		return wp_kses( $data, $allowed_tags );
	}
} // End of if function_exists( 'understrap_kses_title' ).

if (!function_exists('understrap_recommended_posts')) {
	/*
	 * Display recommended posts based on tags
	 */
	function understrap_recommended_posts() {
		$tags = wp_get_post_tags(get_the_ID());

		if ($tags) {
			$tags_ids = array(); ?>
			<div class="post-cards-grid">
				<h5><?php echo esc_html__('More News Recommended For You', 'understrap') ?></h5>
				<div class="row">


					<?php foreach ($tags as $tag) {
						$tags_ids[] = $tag->term_id;
					}

					$args = array(
							'tag__in' => $tags_ids,
							'post_type' => 'news',
							'post__not_in' => array(get_the_ID()),
							'posts_per_page' => 5, // Number of related posts to display.
					);

					$my_query = new WP_Query($args);

					while ($my_query->have_posts()) {
						$my_query->the_post(); ?>
						<div class="col-12 col-sm-6 col-md-4 col-lg-3">
							<div class="card">
								<div class="row">
									<div class="col-5 col-sm-12">
										<?php
										$image_id = get_post_meta(get_the_ID(), "_listing_image_id", true);

										if (!empty($image_id)) {
											$image = wp_get_attachment_image($image_id, "full");
											echo $image;
										} else {
											the_post_thumbnail('thumbnail', array(170, 170));
										}
										?>
									</div>
									<div class="col-7 col-sm-12">
										<div class="card-body">
											<?php understrap_posted_on(); ?>
											<h5 class="card-title"><a
														href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h5>
											<p class="card-text"><?php echo get_the_excerpt(); ?></p>
										</div>
									</div>
								</div>
							</div>
						</div>
					<?php } ?>

				</div>
			</div>

			<?php wp_reset_query();
		}
	}
} // End of if function_exists( 'understrap_recommended_posts' ).

if (!function_exists('understrap_featured_carousel')) {
	/*
	 * Display carousel with featured posts
	 */
	function understrap_featured_carousel() {
		$args = array(
				'post_type' => 'news',
				'meta_query' => array(
						array(
								'key' => 'featured_post',
								'value' => '1'
						),
						array(
								'relation' => 'OR',
								array(
										'key' => 'back_office_only',
										'value' => '0'
								),
								array(
										'key' => 'back_office_only',
										'compare' => 'NOT EXISTS'
								)
						)
				)
		);
		$featured = new WP_Query($args);

		if ($featured->have_posts()) { ?>
			<div class="carousel slide" id="carouselFeaturedPosts" data-ride="carousel">
				<ol class="carousel-indicators">
					<?php while ($featured->have_posts()):
						$featured->the_post(); ?>
						<li data-target="#carouselFeaturedPosts" data-slide-to="<?php echo $featured->current_post ?>"
							class="<?php echo esc_attr(($featured->current_post === 0 ? "active" : "")) ?>"></li>
					<?php endwhile; ?>
				</ol>
				<div class="carousel-inner">
					<?php while ($featured->have_posts()):
						$featured->the_post(); ?>
						<div class="carousel-item <?php echo esc_attr(($featured->current_post === 0 ? "active" : "")) ?>">
							<a href="<?php the_permalink(); ?>">
								<?php the_post_thumbnail('full', array('class' => 'd-block w-100 carousel-image-large')); ?>
								<?php
								$image_id = get_post_meta(get_the_ID(), "_listing_image_id", true);

								if (!empty($image_id)) {
									$image = wp_get_attachment_image($image_id, "full", "", array('class' => 'd-block w-100 carousel-image-mobile'));
									echo $image;
								} else {
									the_post_thumbnail('thumbnail', array('class' => 'd-block w-100 carousel-image-mobile'));
								}
								?>
							</a>
							<div class="carousel-caption d-none d-md-block">
								<?php understrap_posted_on(); ?>
								<a href="<?php the_permalink(); ?>">
									<h3 class="carousel-post-title"><?php the_title() ?></h3>
								</a>
								<p class="carousel-post-excerpt"><?php echo get_the_excerpt() ?></p>
							</div>
						</div>
					<?php endwhile; ?>
				</div>
				<a class="carousel-control-prev" href="#carouselFeaturedPosts" role="button" data-slide="prev">
					<span class="carousel-control-prev-icon" aria-hidden="true"></span>
					<span class="sr-only">Previous</span>
				</a>
				<a class="carousel-control-next" href="#carouselFeaturedPosts" role="button" data-slide="next">
					<span class="carousel-control-next-icon" aria-hidden="true"></span>
					<span class="sr-only">Next</span>
				</a>
			</div>
			<?php wp_reset_query();
		}
	}
}

if (!function_exists('understrap_news_social_share')) {
	/*
	 * Display social share buttons on single post
	 */
	function understrap_news_social_share() {
		// current page url
		$sb_url = urlencode(get_permalink());

		// current page title
		$sb_title = str_replace(' ', '%20', get_the_title());

		// construct sharing urls
		$twitterURL = 'https://twitter.com/intent/tweet?text' . $sb_title . '&url=' . $sb_url . '&via=KyaniCorp';
		$facebookURL = 'https://facebook.com/sharer/sharer.php?u=' . $sb_url;

		// render sharing icons
		?>
		<div class="entry-social-share">
			<span><?php echo esc_html__('Share on: ', 'understrap') ?></span>
			<a href="<?php echo esc_url_raw($facebookURL) ?>" onclick="window.open(this.href, 'mywin',
				'left=20,top=20,width=500,height=500,toolbar=1,resizable=0'); return false;">
				<img src="<?php echo esc_url(bloginfo('template_directory') . "/images/facebook.svg") ?>"></a>
		</div>
		<?php
	}
}
