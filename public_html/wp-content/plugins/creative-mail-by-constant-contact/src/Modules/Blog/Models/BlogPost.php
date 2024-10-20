<?php

namespace CreativeMail\Modules\Blog\Models;

class BlogPost {

	public $id;
	public $author;
	public $date;
	public $content;

	public $title;
	public $excerpt;
	public $status;
	public $name;

	public $modified;
	public $parent_id;
	public $menu_order;
	public $comment_count;

	public $url;
	public $thumbnail;

	public function __construct( $wp_post ) {
		$this->id            = $wp_post->ID;
		$this->author        = $wp_post->post_author;
		$this->date          = $wp_post->post_date;
		$this->modified      = $wp_post->post_modified;
		$this->content       = apply_filters('the_content', $wp_post->post_content);
		$this->title         = $wp_post->post_title;
		$this->excerpt       = $wp_post->post_excerpt;
		$this->status        = $wp_post->post_status;
		$this->parent_id     = $wp_post->post_parent;
		$this->menu_order    = $wp_post->menu_order;
		$this->comment_count = $wp_post->comment_count;
		$this->url           = get_permalink($wp_post->ID);
		if ( has_post_thumbnail($wp_post->ID) ) {
			$this->thumbnail = get_the_post_thumbnail_url($wp_post->ID);
		}
	}
}
