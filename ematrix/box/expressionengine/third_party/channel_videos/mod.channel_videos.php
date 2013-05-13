<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Vidoes Module Tag Methods
 *
 * @package			DevDemon_ChannelVideos
 * @version			2.3
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#core_module_file
 */
class Channel_videos
{

	/**
	 * Constructor
	 *
	 * @return void
	 */
	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();

		$this->site_id = $this->EE->config->item('site_id');

		$this->EE->load->library('channel_videos_helper');

		return;
	}

	// ********************************************************************************* //

	public function videos()
	{
		// Variable prefix
		$prefix = $this->EE->TMPL->fetch_param('prefix', 'video') . ':';
		$attr_id = $this->EE->TMPL->fetch_param('attr:id');
		$attr_class = $this->EE->TMPL->fetch_param('attr:class');

		// -----------------------------------------
		// Limit?
		// -----------------------------------------
		$limit = ctype_digit( (string)$this->EE->TMPL->fetch_param('limit') ) ? $this->EE->TMPL->fetch_param('limit') : 15;

		// -----------------------------------------
		// Offset?
		// -----------------------------------------
		$offset = ctype_digit( (string)$this->EE->TMPL->fetch_param('offset') ) ? $this->EE->TMPL->fetch_param('offset') : 0;

		// Video ID?
		if ($this->EE->TMPL->fetch_param('video_id') != FALSE)
		{
			$this->EE->db->limit(1);
			$this->EE->db->where('video_id', $this->EE->TMPL->fetch_param('video_id'));
		}
		else
		{
			// Entry ID
			$this->entry_id = $this->EE->channel_videos_helper->get_entry_id_from_param();

			// We need an entry_id
			if ($this->entry_id == FALSE)
			{
				$this->EE->db->_reset_select();
				$this->EE->TMPL->log_item('CHANNEL VIDEOS: Entry ID could not be resolved');
				return $this->EE->channel_videos_helper->custom_no_results_conditional($prefix.'no_videos', $this->EE->TMPL->tagdata);
			}

			$this->EE->db->where('entry_id', $this->entry_id);
		}

		$this->EE->db->select('*');
		$this->EE->db->from('exp_channel_videos');


		// -----------------------------------------
		// Field ID?
		// -----------------------------------------
		if ($this->EE->TMPL->fetch_param('field_id') != FALSE)
		{
			$this->EE->db->where('field_id', $this->EE->TMPL->fetch_param('field_id'));
		}

		// -----------------------------------------
		// Which Field
		// -----------------------------------------
		if ($this->EE->TMPL->fetch_param('field') != FALSE)
		{
			$group = $this->EE->TMPL->fetch_param('field');

			// Multiple Fields
			if (strpos($group, '|') !== FALSE)
			{
				$group = explode('|', $group);
				$groups = array();

				foreach ($group as $name)
				{
					$groups[] = $name;
				}
			}
			else
			{
				$groups = $this->EE->TMPL->fetch_param('groups');
			}

			$this->EE->db->join('exp_channel_fields cf', 'cf.field_id = exp_channel_videos.field_id', 'left');
			$this->EE->db->where_in('cf.field_name', $groups);
		}

		// -----------------------------------------
		// Sort?
		// -----------------------------------------
		$sort = 'asc';
		if ($this->EE->TMPL->fetch_param('sort') == 'desc') $sort = 'desc';

		// -----------------------------------------
		// Order By
		// -----------------------------------------
		$orderby_list = array('video_order' => 'video_order', 'duration' => 'video_duration', 'views' => 'video_views', 'date' => 'video_date');
		$order = $this->EE->TMPL->fetch_param('orderby', 'order');
		if (! $temp = array_search($order, $orderby_list))
		{
			$this->EE->db->order_by('video_order', $sort);
		}
		else
		{
			$this->EE->db->order_by($orderby_list[$order], $sort);
		}

		// -----------------------------------------
		// Shoot!
		// -----------------------------------------
		$this->EE->db->limit($limit, $offset);
		$query = $this->EE->db->get();

		// No Results?
		if ($query->num_rows() == 0)
		{
			$this->EE->TMPL->log_item("CHANNEL VIDEOS: No videos found. (Entry_ID:{$this->entry_id})");
			return $this->EE->channel_videos_helper->custom_no_results_conditional($prefix.'no_videos', $this->EE->TMPL->tagdata);
		}

		// Grab Module settings
		$module_settings = $this->EE->channel_videos_helper->grab_settings($this->site_id);
		if (isset($module_settings['players']['youtube']) == FALSE) $module_settings['players']['youtube'] = array();
		if (isset($module_settings['players']['vimeo']) == FALSE) $module_settings['players']['vimeo'] = array();

		// -----------------------------------------
		// Load our video classes
		// -----------------------------------------

		// Load Main Class
		if (class_exists('Video_Service') == FALSE) require PATH_THIRD.'channel_videos/services/video_service.php';

		// Try to load Video Class
		if (class_exists('Video_Service_youtube') == FALSE)
		{
			$location_file = PATH_THIRD.'channel_videos/services/youtube/youtube.php';
			require $location_file;
		}
		$YOUTUBE = new Video_Service_youtube();

		// Try to load Video Class
		if (class_exists('Video_Service_vimeo') == FALSE)
		{
			$location_file = PATH_THIRD.'channel_videos/services/vimeo/vimeo.php';
			require $location_file;
		}
		$VIMEO = new Video_Service_vimeo();

		//----------------------------------------
		// Switch=""
		//----------------------------------------
		$parse_switch = FALSE;
		$switch_matches = array();
		if ( preg_match_all( "/".LD."({$prefix}switch\s*=.+?)".RD."/is", $this->EE->TMPL->tagdata, $switch_matches ) > 0 )
		{
			$parse_switch = TRUE;

			// Loop over all matches
			foreach($switch_matches[0] as $key => $match)
			{
				$switch_vars[$key] = $this->EE->functions->assign_parameters($switch_matches[1][$key]);
				$switch_vars[$key]['original'] = $switch_matches[0][$key];
			}
		}

		// -----------------------------------------
		// Loop through all results!
		// -----------------------------------------
		$final = '';
		$count = 0;
		$total = $query->num_rows();

		foreach ($query->result() as $vid)
		{
			$temp = '';
			$count++;
			$vars = array();

			$vars[$prefix.'count']		= $count;
			$vars[$prefix.'total']		= $total;
			$vars[$prefix.'id']			= $vid->video_id;
			$vars[$prefix.'service']	= $vid->service;
			$vars[$prefix.'service_id']	= $vid->service_video_id;
			$vars[$prefix.'title']		= $vid->video_title;
			$vars[$prefix.'description']= $vid->video_desc;
			$vars[$prefix.'username']	= $vid->video_username;
			$vars[$prefix.'author']		= $vid->video_author;
			$vars[$prefix.'date']		= $vid->video_date;
			$vars[$prefix.'views']		= $vid->video_views;
			$vars[$prefix.'duration']	= sprintf("%0.2f", $vid->video_duration/60) . ' min';
			$vars[$prefix.'duration_secs']	= $vid->video_duration;
			$vars[$prefix.'img_url']	= $vid->video_img_url;

			// Service specific vars
			if ($vid->service == 'youtube')
			{
				$vars[$prefix.'embed_code'] = $YOUTUBE->render_player($vid->service_video_id, $module_settings['players']['youtube']);
				$vars[$prefix.'embed_code_hd'] = $YOUTUBE->render_player($vid->service_video_id, $module_settings['players']['youtube'], TRUE);

				$vars[$prefix.'web_url'] = 'http://www.youtube.com/watch?v=' . $vid->service_video_id;
				$vars[$prefix.'url'] = 'http://www.youtube.com/v/'.$vid->service_video_id.'?version=3';
				$vars[$prefix.'url_hd']		= 'http://www.youtube.com/v/'.$vid->service_video_id.'?hd=1';
				$vars[$prefix.'img_url_hd']	= str_replace('default.jpg', 'hqdefault.jpg', $vid->video_img_url);
			}
			elseif ($vid->service == 'vimeo')
			{
				$extra_params = $this->EE->TMPL->fetch_param('vimeo:url_params');
				$vars[$prefix.'web_url'] = 'http://vimeo.com/' . $vid->service_video_id;
				$vars[$prefix.'embed_code'] = $VIMEO->render_player($vid->service_video_id, $module_settings['players']['vimeo']);
				$vars[$prefix.'embed_code_hd'] = $VIMEO->render_player($vid->service_video_id, $module_settings['players']['vimeo'], TRUE);
				$vars[$prefix.'url'] = 'http://vimeo.com/moogaloop.swf?clip_id='.$vid->service_video_id.'&server=vimeo.com&show_title=1&show_byline=1&show_portrait=0&color=00ADEF&fullscreen=1&'.$extra_params;
				$vars[$prefix.'url_hd']		= 'http://vimeo.com/moogaloop.swf?clip_id='.$vid->service_video_id.'&server=vimeo.com&show_title=1&show_byline=1&show_portrait=0&color=00ADEF&fullscreen=1&'.$extra_params;
				$vars[$prefix.'img_url_hd']	= $vid->video_img_url;
			}

			$temp = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $vars);

			// -----------------------------------------
			// Parse Switch {switch="one|twoo"}
			// -----------------------------------------
			if ($parse_switch)
			{
				// Loop over all switch variables
				foreach($switch_vars as $switch)
				{
					$sw = '';

					// Does it exist? Just to be sure
					if ( isset( $switch[$prefix.'switch'] ) !== FALSE )
					{
						$sopt = explode("|", $switch[$prefix.'switch']);
						$sw = $sopt[(($count-1) + count($sopt)) % count($sopt)];
					}

					$temp = str_replace($switch['original'], $sw, $temp);
				}
			}


			$final .= $temp;
		}

		// Resources are not free..
		$query->free_result();

		return $final;
	}

	// ********************************************************************************* //

	public function channel_videos_router()
	{
		@header('Access-Control-Allow-Origin: *');
		@header('Access-Control-Allow-Credentials: true');
        @header('Access-Control-Max-Age: 86400');
        @header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        @header('Access-Control-Allow-Headers: Keep-Alive, Content-Type, User-Agent, Cache-Control, X-Requested-With, X-File-Name, X-File-Size');

		// -----------------------------------------
		// Ajax Request?
		// -----------------------------------------
		if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
		{
			// Load Library
			if (class_exists('Channel_Videos_AJAX') != TRUE) include 'ajax.channel_videos.php';

			$AJAX = new Channel_Videos_AJAX();

			// Shoot the requested method
			$method = $this->EE->input->get_post('ajax_method');
			echo $AJAX->$method();
			exit();
		}

		exit('Channel Videos ACT URL');
	}

} // END CLASS

/* End of file mod.channel_videos.php */
/* Location: ./system/expressionengine/third_party/channel_videos/mod.channel_videos.php */
