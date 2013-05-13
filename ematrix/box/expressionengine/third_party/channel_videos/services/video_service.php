<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Video Service File
 *
 * @package			DevDemon_ChannelVideos
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_videos/
 */
class Video_Service
{
	/**
	 * Constructor
	 *
	 * @access public
	 */
	function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();

		$this->field_name = 'channel_images[action_groups][][actions]['.$this->info['name'].']';
	}

	// ********************************************************************************* //

	public function search()
	{
		return array();
	}

	// ********************************************************************************* //

	public function parse_url($url)
	{
		return array();
	}

	// ********************************************************************************* //

	public function get_video_info($video_id)
	{
		return FALSE;
	}

	// ********************************************************************************* //


} // END CLASS

/* End of file video_service.php  */
/* Location: ./system/expressionengine/third_party/channel_videos/services/video_service.php */