<?php

/**
 * Config file for Channel Videos
 *
 * @package			DevDemon_ChannelVideos
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_videos/
 * @see				http://ee-garage.com/nsm-addon-updater/developers
 */

if ( ! defined('CHANNEL_VIDEOS_NAME'))
{
	define('CHANNEL_VIDEOS_NAME',         'Channel Videos');
	define('CHANNEL_VIDEOS_CLASS_NAME',   'channel_videos');
	define('CHANNEL_VIDEOS_VERSION',      '3.1.0');
}

$config['name'] 	= CHANNEL_VIDEOS_NAME;
$config["version"] 	= CHANNEL_VIDEOS_VERSION;
$config['nsm_addon_updater']['versions_xml'] = 'http://www.devdemon.com/'.CHANNEL_VIDEOS_CLASS_NAME.'/versions_feed/';

/* End of file config.php */
/* Location: ./system/expressionengine/third_party/channel_videos/config.php */
