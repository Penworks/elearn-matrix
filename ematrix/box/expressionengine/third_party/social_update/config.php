<?php

if ( ! defined('SOCIAL_UPDATE_ADDON_NAME'))
{
	define('SOCIAL_UPDATE_ADDON_NAME',         'Social Update');
	define('SOCIAL_UPDATE_ADDON_VERSION',      '1.0.4');
}

$config['name'] = SOCIAL_UPDATE_ADDON_NAME;
$config['version']= SOCIAL_UPDATE_ADDON_VERSION;

$config['nsm_addon_updater']['versions_xml']='http://www.intoeetive.com/index.php/update.rss/79';


$config['service_providers'] = array('twitter', 'facebook', 'linkedin');