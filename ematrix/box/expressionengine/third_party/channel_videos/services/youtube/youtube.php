<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Videos YOUTUBE service
 *
 * @package			DevDemon_ChannelVideos
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_videos/
 */
class Video_Service_youtube extends Video_Service
{

	/**
	 * Service info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Youtube',
		'name'		=>	'youtube',
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
		$videos = array();
		return $videos;

		//http://code.google.com/apis/youtube/2.0/reference.html
		$key = 'AI39si7C2SGsy0Kqit-am7Bg0dMfXFXNVZLPsXTxHyf3VBEEYYXjeElrjJkNTJ-uybKJqQdoMtKFf9CavpsO80_143BghD5pZg';
		$url = 'http://gdata.youtube.com/feeds/api/videos?v=2&key='.$key;

		// Do we have an author?
		if ($search['author'] != FALSE) $search['author'] = "&author={$search['author']}";

		// Execute the search
		$response = $this->EE->channel_videos_helper->fetch_url_file("{$url}&q={$search['keywords']}{$search['author']}&max-results={$search['limit']}&format=5");

		// Parse XML
		$response = @simplexml_load_string($response);

		// Failed?
		if (isset($response->entry[0]) == FALSE)
		{
			return FALSE;
		}

		// -----------------------------------------
		// Loop over all videos
		// -----------------------------------------
		foreach($response->entry as $vid)
		{
			$id = explode(':', (string) $vid->id);
			$id = end($id);

			$temp = array();
			$temp['id']	= $id;
			$temp['title']	= (string) $vid->title;
			$temp['img_url'] = 'http://i.ytimg.com/vi/' . $id . '/default.jpg';
			$temp['vid_url'] = 'http://www.youtube.com/embed/' . $id;

			$videos[] = $temp;
		}

		return $videos;
	}

	// ********************************************************************************* //

	public function parse_url($url)
	{
		$res = array('id' => FALSE, 'service' => 'youtube');

		// Is this Youtube?
		if (strpos($url, 'youtube') === FALSE AND strpos($url, 'youtu.be') === FALSE)
		{
			return $res;
		}

		// Quick Way
		if (strpos($url, 'youtube.com/watch') !== FALSE)
		{
			parse_str( parse_url( $url, PHP_URL_QUERY ) );
  			if (isset($v) == TRUE) $res['id'] = $v;
		}

		// Short URL (eg: http://youtu.be/dDXvJDyAG5E)
		elseif (strpos($url, 'youtu.be') !== FALSE)
		{
			$url = explode('/', $url); //print_r($url);
			$res['id'] = end($url);
		}

		// Embed? (eg: http://www.youtube.com/embed/dDXvJDyAG5E)
		elseif (strpos($url, 'youtube.com/embed/') !== FALSE)
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
		// http://www.ibm.com/developerworks/xml/library/x-youtubeapi/
		// http://code.google.com/apis/youtube/2.0/developers_guide_protocol_video_entries.html

		$key = 'AI39si7C2SGsy0Kqit-am7Bg0dMfXFXNVZLPsXTxHyf3VBEEYYXjeElrjJkNTJ-uybKJqQdoMtKFf9CavpsO80_143BghD5pZg';
		$RAWresponse = $this->EE->channel_videos_helper->fetch_url_file("http://gdata.youtube.com/feeds/api/videos/{$video_id}?v=2&key=".$key);

		// Parse XML
		$response = @simplexml_load_string($RAWresponse);
		$media = $response->children('http://search.yahoo.com/mrss/');
		$yt = $response->children('http://gdata.youtube.com/schemas/2007');

		// Get Duration
		$yt = $media->children('http://gdata.youtube.com/schemas/2007');
		$attrs = $yt->duration->attributes();
		$duration = (string) $attrs['seconds'];

		// Get Views
		$yt = $response->children('http://gdata.youtube.com/schemas/2007');

		// Sometimes the viewcount is 0, then this fails..
		if ($yt->statistics && $yt->statistics->attributes())
		{
			$attrs = $yt->statistics->attributes();
			$viewCount = (string) $attrs['viewCount'];
		}
		else
		{
			$viewCount = 0;
		}

		// Video Array
		$video = array();
		$video['service']= 'youtube';
		$video['service_video_id']	= $video_id;
		$video['video_title']	= (string) $response->title;
		$video['video_desc']	= (string) $media->group->description;
		$video['video_username'] = (string) $response->author->name;
		$video['video_author'] = (string) $response->author->name;
		$video['video_author_id'] = 0;
		$video['video_date']	= $this->EE->channel_videos_helper->tstamptotime((string) $response->published);
		$video['video_views']	= $viewCount;
		$video['video_duration'] = $duration;
		$video['video_img_url'] = 'http://i.ytimg.com/vi/' . $video_id . '/default.jpg';
		$video['video_url'] = 'http://www.youtube.com/embed/' . $video_id;

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
			if ($hd == TRUE)
			{
				$settings['hd'] = 1;
				$settings['vq'] = 'hd720';
			}
			foreach ($settings as $key => $val) $params .= "&amp;{$key}={$val}";

			$url = 'http://www.youtube.com/embed/'.$video_id.'?wmode=transparent'.$params;

			return "<iframe width='{$width}' height='{$height}' id='{$attr_id}' class='{$attr_class}' src=\"{$url}\" frameborder='0' webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>";
		}
		else
		{
			$width = (isset($this->EE->TMPL->tagparams['embed_width']) == TRUE) ? $this->EE->TMPL->tagparams['embed_width'] : 560;
			$height = (isset($this->EE->TMPL->tagparams['embed_height']) == TRUE) ? $this->EE->TMPL->tagparams['embed_height'] : 349;
			$url_params = (isset($this->EE->TMPL->tagparams['youtube:url_params']) == TRUE) ? $this->EE->TMPL->tagparams['youtube:url_params'] : '';
			if ($hd == TRUE) 'hd=1&amp;vq=hd720&amp;'.$url_params;
			return "<iframe width='{$width}' height='{$height}' id='{$attr_id}' class='{$attr_class}' src='http://www.youtube.com/embed/{$video_id}?wmode=transparent&amp;{$url_params}' frameborder='0' webkitAllowFullScreen mozallowfullscreen allowfullscreen></iframe>";
		}
	}

	// ********************************************************************************* //

}

/* End of file youtube.php */
/* Location: ./system/expressionengine/third_party/channel_videos/services/youtube/youtube.php */
