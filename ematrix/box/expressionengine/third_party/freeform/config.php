<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Freeform - Config
 *
 * NSM Addon Updater config file.
 *
 * @package		Solspace:Freeform
 * @author		Solspace, Inc.
 * @copyright	Copyright (c) 2008-2013, Solspace, Inc.
 * @link		http://solspace.com/docs/freeform
 * @license		http://www.solspace.com/license_agreement
 * @version		4.0.10
 * @filesource	freeform/config.php
 */

//since we are 1.x/2.x compatible, we only want this to run in 1.x just in case
if ( ! defined('FREEFORM_VERSION'))
{
	require_once 'constants.freeform.php';
}

$config['name']									= 'Freeform';
$config['version'] 								= FREEFORM_VERSION;
$config['nsm_addon_updater']['versions_xml'] 	= 'http://www.solspace.com/software/nsm_addon_updater/freeform';