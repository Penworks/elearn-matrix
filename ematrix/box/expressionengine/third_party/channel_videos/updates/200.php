<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class ChannelVideosUpdate_200
{

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		$this->EE =& get_instance();

		// Load dbforge
		$this->EE->load->dbforge();
	}

	// ********************************************************************************* //

	public function do_update()
	{
		
	}

	// ********************************************************************************* //

}

/* End of file 200.php */
/* Location: ./system/expressionengine/third_party/channel_videos/updates/200.php */