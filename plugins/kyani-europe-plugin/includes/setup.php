<?php
/*
 * Function to use the news archive page as the front page
 */
add_action('pre_get_posts', 'archive_page_as_front_page');
function archive_page_as_front_page($query) {
	if (is_admin()) return;

	if ($query->get('page_id') == get_option('page_on_front')) {
		$query->set('post_type', 'news');
		$query->set('page_id', '');
		$query->is_page = $query->is_singular = 0;
		$query->is_archive = $query->is_post_type_archive = 1;

		if ($query->get('paged')) {
			$paged = $query->get('paged');
		} else if ($query->get('page')) {
			$paged = $query->get('page');
		} else {
			$paged = 1;
		}

		$query->set('paged', $paged);
		$query->set('meta_query', array(
			array(
				'key' => 'featured_post',
				'value' => '0'
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
		));
	}
}

///*
// * Remove backoffice only posts
// */
//add_action('pre_get_posts', 'news_only_update');
//function news_only_update($query)
//{
//	if (!is_admin() && $query->is_main_query()) {
//		if (!is_front_page() && !is_tax() && !is_search()) {
//			$query->set('meta_query', array(
//				array(
//					'key' => 'featured_post',
//					'value' => '0'
//				),
//				array(
//					'relation' => 'OR',
//					array(
//						'key' => 'back_office_only',
//						'value' => '0'
//					),
//					array(
//						'key' => 'back_office_only',
//						'compare' => 'NOT EXISTS'
//					)
//				)
//			));
//		} else if (is_tax() || is_search()) {
//			$query->set('meta_query', array(
//				array(
//					'relation' => 'OR',
//					array(
//						'key' => 'back_office_only',
//						'value' => '0'
//					),
//					array(
//						'key' => 'back_office_only',
//						'compare' => 'NOT EXISTS'
//					)
//				)
//			));
//		}
//	}
//}
