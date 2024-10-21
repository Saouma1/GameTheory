<?php

namespace CreativeMail\Models;

class Campaign {

	/**
	 * ID of the campaign
	 *
	 * @var string
	 */
	public $id;
	/**
	 * Whether the campaign is active or not
	 *
	 * @var bool
	 */
	public $is_draft;
	/**
	 * Name of the campaign
	 *
	 * @var string
	 */
	public $name;
	/**
	 * Amount of money the campaign is set to raise
	 *
	 * @var float
	 */
	public $open_rate;
	/**
	 * The campaign's status
	 *
	 * @var string
	 */
	public $status;
}
