<?php

namespace NewfoldLabs\WP\Module\Data\Listeners;

/**
 * Monitors WonderCart events
 */
class WonderCart extends Listener {

	/**
	 * Register the hooks for the listener
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_action('rest_after_insert_yith_campaign', array( $this, 'register_campaign' ), 10 );
	}

	/**
	 * Campaign created
	 *
	 * @param string $post
	 *
	 * @return string The post value
	 */
	public function register_campaign( $post){
		$campaign   = yith_sales_get_campaign( $post->ID );
		if ($campaign){
			$type = $campaign->get_type();
			
			$data = array(
				"label_key"=> "type",
				"type"=> $type,
			);
			
			$this->push(
				"campaign_created",
				$data
			);
		}
		
		return $post;
	}
}
