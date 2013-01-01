<?php

if ( ! defined('SOCIAL_UPDATE_ADDON_NAME'))
{
	define('SOCIAL_UPDATE_ADDON_NAME',         'Social Update');
	define('SOCIAL_UPDATE_ADDON_VERSION',      '0.7.4');
}

$config['name'] = SOCIAL_UPDATE_ADDON_NAME;
$config['version']= SOCIAL_UPDATE_ADDON_VERSION;

$config['nsm_addon_updater']['versions_xml']='http://www.intoeetive.com/index.php/update.rss/79';