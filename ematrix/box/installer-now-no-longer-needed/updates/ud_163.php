<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * ExpressionEngine - by EllisLab
 *
 * @package		ExpressionEngine
 * @author		EllisLab Dev Team
 * @copyright	Copyright (c) 2003 - 2012, EllisLab, Inc.
 * @license		http://expressionengine.com/user_guide/license.html
 * @link		http://expressionengine.com
 * @since		Version 2.0
 * @filesource
 */
 
// ------------------------------------------------------------------------

/**
 * ExpressionEngine Update Class
 *
 * @package		ExpressionEngine
 * @subpackage	Core
 * @category	Core
 * @author		EllisLab Dev Team
 * @link		http://expressionengine.com
 */
class Updater {


	function Updater()
	{
		$this->EE =& get_instance();
	
		// Grab the config file
		if ( ! @include($this->EE->config->config_path))
		{
			show_error('Your config'.EXT.' file is unreadable. Please make sure the file exists and that the file permissions to 666 on the following file: expressionengine/config/config.php');
		}
		
		if (isset($conf))
		{
			$config = $conf;
		}
		
		// Does the config array exist?
		if ( ! isset($config) OR ! is_array($config))
		{
			show_error('Your config'.EXT.' file does not appear to contain any data.');
		}
		
		$this->config =& $config;
	}

	function do_update()
	{		
		/** ---------------------------------------
		/**  Update the Config File
		/** ---------------------------------------*/
		
		$data['cookie_prefix'] =  $this->EE->config->item('cookie_prefix');
		
		$this->EE->config->_append_config_1x($data);

		return TRUE;
	}
	/* END */
	
}	
/* END CLASS */



/* End of file ud_163.php */
/* Location: ./system/expressionengine/installer/updates/ud_163.php */