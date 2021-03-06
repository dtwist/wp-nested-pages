<?php 

namespace NestedPages\Entities\Post;

/**
* Build Post Data Object
*/
class PostDataFactory 
{

	/**
	* Post Data
	* @var object
	*/
	private $post_data;

	/**
	* Build the Object
	*/
	public function build($post)
	{
		$this->post_data = new \stdClass();
		$this->addPostVars($post);
		$this->addPostMeta($post);
		$this->addOriginalLink($post);
		$this->addDate($post);
		$this->author($post);
		return $this->post_data;
	}

	/**
	* Post Items
	*/
	public function addPostVars($post)
	{
		$this->post_data->id = $post->ID;
		$this->post_data->parent_id = $post->post_parent;
		$this->post_data->title = $post->post_title;
		$this->post_data->password = $post->post_password;
		$this->post_data->status = $post->post_status;
		$this->post_data->type = $post->post_type;
		$this->post_data->comment_status = $post->comment_status;
		$this->post_data->content = $post->post_content;
		$this->post_data->hierarchical = is_post_type_hierarchical($post->post_type);
		$this->post_data->link = get_the_permalink($post->ID);
	}

	/**
	* Post Meta
	*/
	public function addPostMeta($post)
	{
		$meta = get_metadata('post', $post->ID);
		$this->post_data->nav_title = ( isset($meta['np_nav_title'][0]) ) ? $meta['np_nav_title'][0] : null;
		$this->post_data->link_target = ( isset($meta['np_link_target'][0]) ) ? $meta['np_link_target'][0] : null;
		$this->post_data->nav_title_attr = ( isset($meta['np_title_attribute'][0]) ) ? $meta['np_title_attribute'][0] : null;
		$this->post_data->nav_css = ( isset($meta['np_nav_css_classes'][0]) ) ? $meta['np_nav_css_classes'][0] : null;
		$this->post_data->nav_object = ( isset($meta['np_nav_menu_item_object'][0]) ) ? $meta['np_nav_menu_item_object'][0] : null;
		$this->post_data->nav_object_id = ( isset($meta['np_nav_menu_item_object_id'][0]) ) ? $meta['np_nav_menu_item_object_id'][0] : null;
		$this->post_data->nav_type = ( isset($meta['np_nav_menu_item_type'][0]) ) ? $meta['np_nav_menu_item_type'][0] : null;
		$this->post_data->nav_status = ( isset($meta['np_nav_status'][0]) && $meta['np_nav_status'][0] == 'hide' ) ? 'hide' : 'show';
		$this->post_data->np_status = ( isset($meta['nested_pages_status'][0]) && $meta['nested_pages_status'][0] == 'hide' ) ? 'hide' : 'show';
		$this->post_data->template = ( isset($meta['_wp_page_template'][0]) ) ? $meta['_wp_page_template'][0] : false;

		// Yoast Score
		if ( function_exists('wpseo_auto_load') ) {
			$yoast_score = get_post_meta($post->ID, '_yoast_wpseo_meta-robots-noindex', true);
			if ( ! $yoast_score ) {
				$yoast_score = get_post_meta($post->ID, '_yoast_wpseo_linkdex', true);
				$this->post_data->score = \WPSEO_Utils::translate_score($yoast_score);
			} else {
				$this->post_data->score = 'noindex';
			}
		}
	}

	/**
	* Add original item/link to link
	*/
	private function addOriginalLink($post)
	{
		if ( $post->post_type !== 'np-redirect' ) {
			$this->post_data->nav_original_link = null;
			$this->post_data->nav_original_type = null;
			return;
		}

		if ( $this->post_data->nav_type && $this->post_data->nav_type == 'taxonomy' ){
			$term = get_term_by('id', $this->post_data->nav_object_id, $this->post_data->nav_object);
			$this->post_data->nav_original_link = get_term_link($term);
			$this->post_data->nav_original_title = $term->name;
			return;
		}

		$id = $this->post_data->nav_object_id;
		$this->post_data->nav_original_link = get_the_permalink($id);
		$this->post_data->nav_original_title = get_the_title($id);
	}

	/**
	* Date Vars
	*/
	private function addDate($post)
	{
		$this->post_data->date = new \stdClass();
		$time = get_the_time('U', $post->ID);
		$this->post_data->date->d = date('d', $time);
		$this->post_data->date->month = date('m', $time);
		$this->post_data->date->y = date('Y', $time);
		$this->post_data->date->h = date('H', $time);
		$this->post_data->date->m = date('i', $time);
		$this->post_data->date->datepicker = $time;
	}

	/**
	* Add Author Info
	*/
	private function author($post)
	{
		$this->post_data->author = get_the_author_meta('display_name', $post->post_author);
		$this->post_data->author_link = admin_url('edit.php?post_type=' . $post->post_type . '&author=' . $post->post_author);
	}

}