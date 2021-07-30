<?php

/*
 * Custom API Endpoints for Back Office
 */

class NEWS_ENDPOINT extends WP_REST_Controller
{
	private $post_type, $current_lang, $data;
	private $language_switched = false;

	/*
	 * Constructor
	 */
	public function __construct() {
		$this->namespace = 'api';
		$this->rest_base = 'backoffice';
		$this->post_type = 'news';
	}

	/*
	 * Register routes
	 */
	public function register_routes() {
		register_rest_route($this->namespace, $this->rest_base, array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array($this, 'get_backoffice_news'),
			'permission_callback' => array($this, 'get_items_permissions_check'),
			'args' => $this->get_collection_params(),
		));

		register_rest_route($this->namespace, $this->rest_base . '/featured', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array($this, 'get_backoffice_news_featured'),
			'permission_callback' => array($this, 'get_items_permissions_check'),
			'args' => $this->get_collection_params(),
		));

		register_rest_route($this->namespace, $this->rest_base . '/widget', array(
			'methods' => WP_REST_Server::READABLE,
			'callback' => array($this, 'get_backoffice_news_widget'),
			'permission_callback' => array($this, 'get_items_permissions_check'),
			'args' => $this->get_collection_params()
		));
	}

	/*
	 * Retrieve news for backoffice excluding news that are featured
	 */
	public function get_backoffice_news($request){
		if ($request['locale']) {
			if (class_exists('SitePress')) {
				global $sitepress;
				$this->current_lang = $sitepress->get_current_language();
				$wp_locale = $this->get_wp_locale($request['locale']);

				if ($wp_locale && $wp_locale !== $this->current_lang) {
					$sitepress->switch_lang($wp_locale);
					$this->language_switched = true;
				}
			}
		}

		// retrieve 5 featured posts that will be excluded from the archived posts
		$featured_args = array(
			'post_type' => $this->post_type,
			'posts_per_page' => 5,
			'no_found_rows' => true,
			'suppress_filter' => 0,
			'meta_query' => array(
				array(
					'key' => 'display_in_back_office',
					'value' => '1'
				),
				array(
					'key' => 'feature_in_back_office',
					'value' => '1'
				)
			)
		);

		$featured_query = new WP_Query($featured_args);
		$featured_ids = array();
		if (!empty($featured_query->posts)) {
			foreach ($featured_query->posts as $post) {
				$featured_ids[] = $post->ID;
			}
		}

		$args = array(
			'post_type' => $this->post_type,
			'posts_per_page' => $request['per_page'],
			'paged' => $request['page'],
			'suppress_filters' => 0,
			'posts__not_in' => $featured_ids,
			'meta_query' => array(
				array(
					'key' => 'display_in_back_office',
					'value' => '1'
				)
			)
		);

		if ($request['search']) {
			$args = array_replace($args, array('meta_query' => array('key' => 'display_in_back_office', 'value' => '1')));
			$args['s'] = $request['search'];
		}

		if ($request['post']) {
			$args = array_replace($args, array('meta_query' => array('key' => 'display_in_back_office', 'value' => '1')));
			$args['p'] = $request['post'];
		}

		$query = new WP_Query($args);

		if (empty($query->posts)) {
			return new WP_Error('no news', __('no news stories found'), array('status' => 404));
		}

		// get all stories
		$news = $query->posts;

		// get max number of pages and total of news stories
		$max_pages = $query->max_num_pages;
		$total = $query->found_posts;

		foreach	($news as $story) {
			$response = $this->prepare_item_for_backoffice_response($story, $request);
			$this->data[] = $this->prepare_response_for_collection($response);
		}

		if ($this->language_switched) {
			global $sitepress;
			$sitepress->switch_lang($this->current_lang);
			$this->language_switched = false;
		}

		$response = new WP_REST_Response($this->data, 200);
		$response->header('X-WP-Total', $total);
		$response->header('X-WP-TotalPages', $max_pages);

		return $response;
	}

	/*
	 * Retrieve news stories that are featured (carousel)
	 */
	public function get_backoffice_news_featured($request) {
		$args = array(
			'post_type' => $this->post_type,
			'posts_per_page' => 5,
			'no_found_rows' => true,
			'suppress_filters' => 0,
			'meta_query' => array(
				array(
					'key' => 'display_in_back_office',
					'value' => '1'
				),
				array(
					'key' => 'feature_in_back_office',
					'value' => '1'
				)
			)
		);

		// if locale is specified in request change the locale
		if ($request['locale']) {
			if (class_exists('SitePress')) {
				global $sitepress;
				$this->current_lang = $sitepress->get_current_language();
				$wp_locale = $this->get_wp_locale($request['locale']);

				if ($wp_locale && $wp_locale !== $this->current_lang) {
					$sitepress->switch_lang($wp_locale);
					$this->language_switched = true;
				}
			}
		}

		// use WP Query to get news stories with pagination
		$query = new WP_Query($args);

		if ($this->language_switched) {
			global $sitepress;
			$sitepress->switch_lang($this->current_lang);
			$this->language_switched = false;
		}

		if (empty($query->posts)) {
			return new WP_Error('no_news', __('No News Stories found'), array('status' => 404));
		}

		// get all queried news
		$news = $query->posts;

		// get max number of pages and total number of news stories
		$max_pages = $query->max_num_pages;
		$total = $query->found_posts;

		foreach ($news as $story) {
			$response = $this->prepare_item_for_backoffice_featured_response($story, $request);
			$this->data[] = $this->prepare_response_for_collection($response);
		}

		$response = new WP_REST_Response($this->data, 200);
		$response->header('X-WP-Total', $total);
		$response->header('X-WP-TotalPages', $max_pages);

		return $response;
	}

	/*
	 * Retrieve news stories that are displayed in the back office dashboard
	 * news widget
	 */
	public function get_backoffice_news_widget($request) {

		// if locale is specified in request change the locale
		if ($request['locale']) {
			if (class_exists('SitePress')) {
				global $sitepress;
				$this->current_lang = $sitepress->get_current_language();
				$wp_locale = $this->get_wp_locale($request['locale']);

				if ($wp_locale && $wp_locale !== $this->current_lang) {
					$sitepress->switch_lang($wp_locale);
					$this->language_switched = true;
				}
			}
		}
		// get last two months

		$args = array(
			'post_type' => $this->post_type,
			'posts_per_page' => $request['per_page'],
			'paged' => $request['page'],
			'suppress_filters' => 0,
//			'date_query' => array(
//				'column' => 'post_date',
//				'after' => '- 60 day'
//			),
			'meta_query' => array(
				array(
					'key' => 'display_in_back_office',
					'value' => '1'
				),
				array(
					'key' => 'display_in_back_office_widget',
					'value' => '1'
				)
			)
		);

		// use WP Query to get news stories with pagination
		$query = new WP_Query($args);
		if ($this->language_switched) {
			global $sitepress;
			$sitepress->switch_lang($this->current_lang);
			$this->language_switched = false;
		}

		if (empty($query->posts)) {
			return new WP_Error('no_news', __('No News Stories found'), array('status' => 404));
		}

		// get all queried news
		$news = $query->posts;

		// get max number of pages and total number of news stories
		$max_pages = $query->max_num_pages;
		$total = $query->found_posts;

		foreach ($news as $story) {
			$response = $this->prepare_item_for_backoffice_widget_response($story, $request);
			$this->data[] = $this->prepare_response_for_collection($response);
		}

		$response = new WP_REST_Response($this->data, 200);
		$response->header('X-WP-Total', $total);
		$response->header('X-WP-TotalPages', $max_pages);

		return $response;
	}

	/*
	 * prepare response for news archive for backoffice
	 */
	public function prepare_item_for_backoffice_response($story, $request): array {
		$thumbnail_id = get_post_meta($story->ID, "custom_thumbnail_image", true);
		$thumbnail_url = wp_get_attachment_image_url($thumbnail_id, "full");
		$banner_url = get_the_post_thumbnail_url($story->ID, "full");

		if ($thumbnail_url === false) {
			$thumbnail_url = get_the_post_thumbnail_url($story->ID, "thumbnail");
		}

		if ($request['post']) {
			return array(
				'postID' => $story->ID,
				'title' => $story->post_title,
				'postedDate' => $story->post_date,
				'content' => $story->post_content,
				'thumbnailURL' => $thumbnail_url,
				'bannerURL' => $banner_url,
				'recommendedStories' => $this->get_recommended_stories($story)
			);
		}

		return array(
			'postID' => $story->ID,
			'title' => $story->post_title,
			'postedDate' => $story->post_date,
			'thumbnailURL' => $thumbnail_url,
			'excerpt' => $this->get_excerpt($story)
		);
	}

	/*
	 * prepare response for featured news stories
	 */
	public function prepare_item_for_backoffice_featured_response($story, $request): array {
		$thumbnail_id = get_post_meta($story->ID, "custom_thumbnail_image", true);
		$thumbnail_url = wp_get_attachment_image_url($thumbnail_id, "full");
		$banner_url = get_the_post_thumbnail_url($story->ID, "full");

		if ($thumbnail_url === false) {
			$thumbnail_url = get_the_post_thumbnail_url($story->ID, "thumbnail");
		}

		return array(
			'postID' => $story->ID,
			'title' => $story->post_title,
			'postedDate' => $story->post_date,
			'thumbnailURL' => $thumbnail_url,
			'bannerURL' => $banner_url,
			'excerpt' => $this->get_excerpt($story)
		);
	}

	/*
	 * Prepare response for news stories
	 */
	public function prepare_item_for_backoffice_widget_response($story, $request): array {
		$thumbnail_id = get_post_meta($story->ID, "custom_thumbnail_image", true);
		$thumbnail_url = wp_get_attachment_image_url($thumbnail_id, "full");

		if ($thumbnail_url === false) {
			$thumbnail_url = get_the_post_thumbnail_url($story->ID, "thumbnail");
		}

		return array(
			'postID' => $story->ID,
			'title' => $story->post_title,
			'postedDate' => $story->post_date,
			'thumbnailURL' => $thumbnail_url,
		);
	}

	/*
	 * Get wpml locale to retrieve correct news stories
	 */
	private function get_wp_locale($request_locale): string {
		$wp_locale = '';
		$current_site_id = get_current_blog_id();

		$current_site_country_code = str_replace('/', '', get_blog_details($current_site_id)->path);

		$country_locales = json_decode(file_get_contents(dirname(__DIR__) . '/assets/data/locales/' . $current_site_country_code . '.json'));

		foreach ($country_locales->locales as $locale) {
			if ($locale->bo_locale === $request_locale) {
				$wp_locale = $locale->wp_locale;
			}
		}
		return $wp_locale;
	}

	/*
	 * Generate excerpt from content if excerpt is not create by user in admin dashboard
	 */
	private function get_excerpt($story): string {
		if (!$story->post_excerpt) {
			$excerpt = $story->post_content;
			$excerpt = strip_tags($excerpt);
			$excerpt = strip_shortcodes($excerpt);
			$excerpt = trim(preg_replace('/\s+/', ' ', $excerpt));

			$excerpt = explode(' ', $excerpt, 12);
			if (count($excerpt) >= 12) {
				array_pop($excerpt);
				$excerpt = implode(" ", $excerpt) . '...';
			} else {
				$excerpt = implode(" ", $excerpt);
			}

			return $excerpt;
		}
		return $story->post_excerpt;
	}

	/*
	 * Check if a given request has access to post items
	 */
	public function get_items_permissions_check($request): bool {
		return true;
	}

	public function get_collection_params(): array {
		return array(
			'page' => array(
				'description' => 'Current page of the collection.',
				'type' => 'integer',
				'default' => 1,
				'sanitize_callback' => 'absint',
			),
			'per_page' => array(
				'description' => 'Maximum number of items to be returned in result set.',
				'type' => 'integer',
				'default' => 20,
				'sanitize_callback' => 'absint',
			),
			'locale' => array(
				'description' => 'Locale for News Posts',
				'type' => 'string',
				'default' => '',
				'sanitize_callback' => 'sanitize_key'
			),
			'search' => array(
				'description' => 'Search for News Posts',
				'type' => 'string',
				'default' => '',
			),
			'post' => array(
				'description' => 'Get News Story by ID',
				'type' => 'string',
				'default' => '',
				'sanitize_callback' => 'sanitize_key'
			)
		);
	}

	/*
	 * Get up to 5 recommended stories
	 */
	private function get_recommended_stories($story): array {
		$recommended_stories = array();

		$tags = wp_get_post_tags($story->ID);
		if ($tags) {
			$tags_ids = array();

			foreach ($tags as $tag) {
				$tags_ids[] = $tag->term_id;
			}

			$args = array(
				'tag__in' => $tags_ids,
				'post_type' => 'news',
				'post__not_in' => array($story->ID),
				'posts_per_page' => 5
			);

			$tags_query = new WP_Query($args);
			if (!empty($tags_query->posts)) {
				foreach ($tags_query->posts as $post) {
					$thumbnail_id = get_post_meta($post->ID, "custom_thumbnail_image", true);
					$thumbnail_url = wp_get_attachment_image_url($thumbnail_id, "full");

					if ($thumbnail_url === false) {
						$thumbnail_url = get_the_post_thumbnail_url($post->ID, "thumbnail");
					}

					$recommended_stories[] = array(
						'postID' => $post->ID,
						'title' => $post->post_title,
						'postedDate' => $post->post_date,
						'thumbnailURL' => $thumbnail_url,
						'excerpt' => $this->get_excerpt($post)
					);
				}
			}
		}
		return $recommended_stories;
	}
}

add_action('rest_api_init', function() {
	$controller = new NEWS_ENDPOINT();
	$controller->register_routes();
});
