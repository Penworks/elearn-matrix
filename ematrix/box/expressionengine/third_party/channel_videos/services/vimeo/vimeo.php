<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Videos VIMEO service
 *
 * @package			DevDemon_ChannelVideos
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_videos/
 */
class Video_Service_vimeo extends Video_Service
{

	/**
	 * Service info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Vimeo',
		'name'		=>	'vimeo',
		'version'	=>	'1.0',
		'enabled'	=>	TRUE,
	);

	/**
	 * Constructor
	 * Calls the parent constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// ********************************************************************************* //

	public function search($search)
	{
		$videos = false;

		//http://vimeo.com/api/docs/methods/vimeo.videos.search
		if (class_exists('phpVimeo') == FALSE) include PATH_THIRD.'channel_videos/services/vimeo/libraries/vimeo.php';
		$VIMEO = new phpVimeo('1a8a81eaf6658d0dbb955f0386f484c1f9b55ece', '2ffac38d1ee9eac6a2389269aa19429a927ff07b');

		// -----------------------------------------
		// Parameters
		// -----------------------------------------
		$params = array();
		$params['query'] = $search['keywords'];
		$params['full_response'] = TRUE;
		$params['per_page'] = $search['limit'];
		if ($search['author'] != FALSE) $params['user_id'] = $search['author'];

		// Lets disable error reporting
		//error_reporting(-1);
		error_reporting(E_ALL);
		@ini_set('display_errors', 1);

		// -----------------------------------------
		// Lets try Searching!
		// -----------------------------------------
		try
		{
			// Search!
			$result = $VIMEO->call('vimeo.videos.search', $params);
			//print_r($result); exit();

			// Did we find anything?
			if (isset($result->videos->video[0])) $result = $result->videos->video;
			else $result = array();

		}
		catch (Exception $e)
		{
			$result = array();
		}

		// Turn it back on
		error_reporting(E_ALL);
		@ini_set('display_errors', 1);

		// -----------------------------------------
		// Loop over all videos
		// -----------------------------------------
		foreach ($result as $vid)
		{
			$temp = array();
			$temp['id'] = $vid->id;
			$temp['title'] = $vid->title;
			$temp['vid_url'] = 'http://player.vimeo.com/video/'.$vid->id.'?title=0&byline=0&portrait=0';

			// Img URL
			foreach($vid->thumbnails->thumbnail as $img)
			{
				if ($img->width == '100' OR $img->height == '100')
				{
					$temp['img_url'] = $img->_content;
				}
			}

			$videos[] = $temp;
		}

		return $videos;
	}

	// ********************************************************************************* //

	public function parse_url($url)
	{
		$res = array('id' => FALSE, 'service' => 'vimeo');

		// Is this Youtube?
		if (strpos($url, 'vimeo.com') === FALSE)
		{
			return $res;
		}

		// Normal URL? (eg: http://www.youtube.com/watch?v=dDXvJDyAG5E&feature=feedu)
		if (strpos($url, 'vimeo.com/') !== FALSE)
		{
			$url = explode('/', $url); //print_r($url);
			$res['id'] = end($url);
		}

		// Nothing? Quit
		else
		{
			return $res;
		}

		//exit(print_r($res));

		return $res;
	}

	// ********************************************************************************* //

	public function get_video_info($video_id)
	{
		$videos = false;

		//http://vimeo.com/api/docs/methods/vimeo.videos.search
		if (class_exists('phpVimeo') == FALSE) include PATH_THIRD.'channel_videos/services/vimeo/libraries/vimeo.php';
		$VIMEO = new phpVimeo('078a066425e8eaecd75532b4d6aeae00', '47a52d9dc0ec7429');

		// Lets disable error reporting
		error_reporting(0);

		// -----------------------------------------
		// Lets try Searching!
		// -----------------------------------------
		try
		{
			$result = $VIMEO->call('vimeo.videos.getInfo', array('video_id' => $video_id));
			$result = $result->video;
			//print_r($result); exit();
		}
		catch (Exception $e)
		{
			$result = array();
		}

		// -----------------------------------------
		// Did we find anything
		// -----------------------------------------
		if (isset($result[0]) == FALSE)
		{
			return FALSE;
		}
		else
		{
			$vid = $result[0];
		}

		// Turn it back on
		error_reporting(E_ALL);
		@ini_set('display_errors', 1);

		// Video Array
		$video = array();
		$video['service']= 'vimeo';
		$video['service_video_id']	= $video_id;
		$video['video_title']	= $vid->title;
		$video['video_desc']	= $vid->description;
		$video['video_username'] = $vid->owner->username;
		$video['video_author'] = $vid->owner->display_name;
		$video['video_author_id'] = $vid->owner->id;
		$video['video_date']	= strtotime($vid->upload_date);
		$video['video_views']	= $vid->number_of_plays;
		$video['video_duration'] = $vid->duration;
		$video['video_url'] = 'http://player.vimeo.com/video/'.$video_id.'?title=0&byline=0&portrait=0';

		// Img URL
		foreach($vid->thumbnails->thumbnail as $img)
		{
			if ($img->width == '100' OR $img->height == '100')
			{
				$video['video_img_url'] = $img->_content;
			}
		}


		return $video;
	}

	// ********************************************************************************* //

	public function render_player($video_id=0, $settings=array(), $hd=FALSE)
	{
		$attr_id = $this->EE->TMPL->fetch_param('attr:id');
		$attr_class = $this->EE->TMPL->fetch_param('attr:class');

		if (isset($settings['width']) === TRUE)
		{
			$width = (isset($this->EE->TMPL->tagparams['embed_width']) == TRUE) ? $this->EE->TMPL->tagparams['embed_width'] : $settings['width'];
			$height = (isset($this->EE->TMPL->tagparams['embed_height']) == TRUE) ? $this->EE->TMPL->tagparams['embed_height'] : $settings['height'];
			unset($settings['width'], $settings['height']);

			$params = '';
			foreach ($settings as $key => $val) $params .= "&amp;{$key}={$val}";

			$url = 'http://player.vimeo.com/video/'.$video_id.'?wmode=transparent'.$params;

			return "<iframe width='{$width}' height='{$height}' id='{$attr_id}' class='{$attr_class}' src=\"{$url}\" frameborder='0' webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>";
		}
		else
		{
			$width = (isset($this->EE->TMPL->tagparams['embed_width']) == TRUE) ? $this->EE->TMPL->tagparams['embed_width'] : 560;
			$height = (isset($this->EE->TMPL->tagparams['embed_height']) == TRUE) ? $this->EE->TMPL->tagparams['embed_height'] : 349;
			$url_params = (isset($this->EE->TMPL->tagparams['youtube:url_params']) == TRUE) ? $this->EE->TMPL->tagparams['youtube:url_params'] : '';
			return "<iframe width='{$width}' height='{$height}' id='{$attr_id}' class='{$attr_class}' src='http://player.vimeo.com/video/{$video_id}?title=0&amp;byline=0&amp;portrait=0&amp;{$url_params}' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>";
		}
	}

	// ********************************************************************************* //

}

/* End of file vimeo.php */
/* Location: ./system/expressionengine/third_party/channel_videos/services/vimeo/vimeo.php */
