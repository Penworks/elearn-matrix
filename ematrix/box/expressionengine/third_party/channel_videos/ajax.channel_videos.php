<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Videos AJAX File
 *
 * @package			DevDemon_ChannelVideos
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Channel_Videos_AJAX
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

		$this->EE->load->add_package_path(PATH_THIRD . 'channel_videos/');
		$this->EE->lang->loadfile('channel_videos');
		$this->EE->load->library('channel_videos_helper');
		$this->EE->config->load('cv_config');

		if ($this->EE->input->get_post('site_id')) $this->site_id = $this->EE->input->get_post('site_id');
		elseif ($this->EE->input->cookie('cp_last_site_id')) $this->site_id = $this->EE->input->cookie('cp_last_site_id');
		else $this->site_id = $this->EE->config->item('site_id');
	}

	// ********************************************************************************* //

	/**
	 * Search For Videos
	 *
	 * @access public
	 * @return string
	 */
	public function search_videos()
	{
		// View Data
		$this->vData = array();
		$this->vData['field_id'] = $this->EE->input->get_post('field_id');

		// -----------------------------------------
		// Grab Field Settings
		// -----------------------------------------
		$query = $this->EE->db->select('field_settings')->from('exp_channel_fields')->where('field_id', $this->vData['field_id'])->get();
		$settings = unserialize(base64_decode($query->row('field_settings')));
		$defaults = $this->EE->config->item('cv_defaults');
		$settings = array_merge($defaults, $settings);

		// Grab Module settings
		$module_settings = $this->EE->channel_videos_helper->grab_settings($this->site_id);

		// -----------------------------------------
		// Search Options
		// -----------------------------------------
		$search = array();

		// Keywords
		$search['keywords'] = $this->EE->channel_videos_helper->parse_keywords( $this->EE->input->post('keywords') );
		if ($search['keywords'] != FALSE) $search['keywords'] = urlencode($search['keywords']);

		// Author
		$search['author'] = (trim($this->EE->input->post('author')) != FALSE) ? trim($this->EE->input->post('author')) : FALSE;

		// Limit
		$search['limit'] = ($this->EE->input->post('limit') > 1) ? $this->EE->input->post('limit') : 10;

		// Any Services?
		if (isset($settings['cv_services']) == FALSE OR empty($settings['cv_services']) == TRUE)
		{
			exit('MISSING SERVICES');
		}

		// Search Results
		$results = array();

		// -----------------------------------------
		// Loop over all services
		// -----------------------------------------
		foreach($settings['cv_services'] as $service)
		{
			// -----------------------------------------
			// Load Service!
			// -----------------------------------------
			$video_class = 'Video_Service_'.$service;

			// Load Main Class
			if (class_exists('Video_Service') == FALSE) require PATH_THIRD.'channel_videos/services/video_service.php';

			// Try to load Video Class
			if (class_exists($video_class) == FALSE)
			{
				$location_file = PATH_THIRD.'channel_videos/services/'.$service.'/'.$service.'.php';

				require $location_file;
			}

			// Init!
			$VID = new $video_class();

			// Search!
			$results[$service] = $VID->search($search);
		}

		$out = array('services' => $results);

		exit( $this->EE->channel_videos_helper->generate_json($out) );
	}

	// ********************************************************************************* //

	public function get_video()
	{
		$out = array('success' => 'no', 'body');

		// Params
		$url = $this->EE->input->get_post('url');
		$service = $this->EE->input->get_post('service');
		$video_id = $this->EE->input->get_post('video_id');
		$field_id = $this->EE->input->get_post('field_id');

		// -----------------------------------------
		// Grab Field Settings
		// -----------------------------------------
		$query = $this->EE->db->select('field_settings')->from('exp_channel_fields')->where('field_id', $field_id)->get();
		$settings = unserialize(base64_decode($query->row('field_settings')));
		$defaults = $this->EE->config->item('cv_defaults');
		$settings = array_merge($defaults, $settings);

		// -----------------------------------------
		// Load Services
		// -----------------------------------------
		$this->EE->load->helper('directory');
		if (class_exists('Video_Service') == FALSE) include(PATH_THIRD.'channel_videos/services/video_service.php');
		$services = array();

		// Make the map
		if (($temp = directory_map(PATH_THIRD.'channel_videos/services/', 2)) !== FALSE)
		{
			// Loop over all fields
			foreach ($temp as $classname => $files)
			{
				// Kill YOUTUBE
				if ($classname == 'youtube') continue;

				// If allows
				if (in_array($classname, $settings['cv_services']) === FALSE) continue;

				// Check for empty array and such
				if (is_array($files) == FALSE OR empty($files) == TRUE)
    			{
    				continue;
    			}

    			// Search for the file we need, not there? continue
    			if (array_search($classname.'.php', $files) === FALSE) continue;

    			$final_class = 'Video_Service_'.$classname;

    			// Do a simple check, we don't want fatal errors
    			if (class_exists($final_class) == FALSE)
    			{
    				// Include it of course! and get the class vars
    				require PATH_THIRD.'channel_videos/services/' .$classname.'/'. $classname.'.php';
    			}

    			$obj = new $final_class();

    			// Is it enabled? ready to use?
    			if (isset($obj->info['enabled']) == FALSE OR $obj->info['enabled'] == FALSE) continue;

    			// Store it!
				$services[$classname] = $obj;

				// We need to be sure it's formatted correctly
    			if (isset($obj->info['title']) == FALSE) unset($services[$classname]);
    			if (isset($obj->info['name']) == FALSE) unset($services[$classname]);
			}
		}


		// -----------------------------------------
		// Parse URL
		// -----------------------------------------
		if ($url != FALSE)
		{
			foreach ($services as $ss)
			{
				$res = $ss->parse_url($url);

				if (isset($res['id']) == FALSE OR $res['id'] == FALSE) continue;
				elseif ($res['id'] != FALSE) break;
			}

			//print_r($res);

			// Did we find anything?
			if (isset($res['id']) == FALSE OR $res['id'] == FALSE)
			{
				$out = array('success' => 'no', 'body' => 'Your URL was not recognized.');
				exit( $this->EE->channel_videos_helper->generate_json($out) );
			}
			else
			{
				$video_id = $res['id'];
				$service = $res['service'];
			}

			$video = $services[$service]->get_video_info($video_id);
		}

		// -----------------------------------------
		// Get Video INFO!
		// -----------------------------------------
		$video = $services[$service]->get_video_info($video_id);
		$video['video_id'] = 0;

		$vData = array();
		$vData['vid'] = (object) $video;
		$vData['order'] = 0;
		$vData['layout'] = $this->EE->input->get_post('field_layout');
		$vData['field_name'] = $this->EE->input->get_post('field_name');

		$out = array('success' => 'yes', 'body' => $this->EE->load->view('pbf_single_video.php', $vData, TRUE), 'video' => $video);
		exit( $this->EE->channel_videos_helper->generate_json($out) );
	}

	// ********************************************************************************* //

	public function delete_video()
	{
		$video_id = $this->EE->input->get_post('video_id');

		if ($video_id == 0) exit('Video ID is FALSE');

		$this->EE->db->where('video_id', $video_id);
		$this->EE->db->delete('exp_channel_videos');

		exit('DONE');
	}

} // END CLASS

/* End of file ajax.channel_videos.php  */
/* Location: ./system/expressionengine/third_party/channel_videos/modules/ajax.channel_videos.php */
