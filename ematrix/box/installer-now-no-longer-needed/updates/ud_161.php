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
		$query = $this->EE->db->query("SHOW tables LIKE 'exp_mailing_list'");
		
		if ($query->num_rows() > 0)
		{
			$Q[] = "ALTER TABLE `exp_mailing_list` ADD `ip_address` VARCHAR(16) NOT NULL AFTER `list_id`";			
		}
						
		// Change default weblog preferences for trackbacks
		$Q[] = "ALTER TABLE `exp_weblogs` CHANGE `enable_trackbacks` `enable_trackbacks` CHAR(1) NOT NULL DEFAULT 'n'";
		$Q[] = "ALTER TABLE `exp_weblogs` CHANGE `trackback_system_enabled` `trackback_system_enabled` CHAR(1) NOT NULL DEFAULT 'n'";
		
		// fix version number for Member module, which may be out of sync for old installations
		$Q[] = "UPDATE `exp_modules` SET `module_version` = '1.3' WHERE `module_name` = 'Member'";

		// Text formatting for emails from the Communicate page
		$Q[] = "ALTER TABLE `exp_email_cache` ADD `text_fmt` VARCHAR(40) NOT NULL AFTER `mailtype`";
		
		// Member Group setting for showing in Author List
		$Q[] = "ALTER TABLE `exp_member_groups` ADD `include_in_authorlist` CHAR(1) NOT NULL DEFAULT 'n' AFTER `can_send_bulletins`";
		
		// Show All Tab in the Publish Area
		$Q[] = "ALTER TABLE `exp_weblogs` ADD `show_show_all_cluster` CHAR( 1 ) NOT NULL DEFAULT 'y' AFTER `show_pages_cluster`;";
	
		
		/** ---------------------------------------
		/**  Run Queries
		/** ---------------------------------------*/
		
		foreach ($Q as $sql)
		{
			$this->EE->db->query($sql);
		}
		
	
		/** ---------------------------------------
		/**  Update the Config File
		/** ---------------------------------------*/

		return TRUE;
	}
	/* END */
	
}	
/* END CLASS */



/* End of file ud_161.php */
/* Location: ./system/expressionengine/installer/updates/ud_161.php */