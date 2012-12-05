<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Images Action File
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class Image_Action
{
	/**
	 * Constructor
	 *
	 * @access public
	 */
	function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();

		$this->field_name = 'channel_images[action_groups][][actions]['.$this->info['name'].']';
	}

	// ********************************************************************************* //

	public function run($file, $temp_dir)
	{
		return TRUE;
	}

	// ********************************************************************************* //

	public function settings()
	{
		return '';
	}

	// ********************************************************************************* //

	public function display_settings($settings=array())
	{
		// Final Output
		$out = '';

		$action_path = PATH_THIRD . 'channel_images/actions/' . $this->info['name'] . '/';


		// Only for old EE2 versions!
		if (version_compare(APP_VER, '2.1.5', '<'))
		{
			$this->EE->load->_ci_view_path = $action_path.'views/';

		}

		// Add package path (so view files can render properly)
		$this->EE->load->add_package_path($action_path);

		// Do we need to load LANG file?
		if (@is_dir($action_path . 'language/') == TRUE)
		{
			$this->EE->lang->load($this->info['name'], $this->EE->lang->user_lang, FALSE, TRUE, $action_path);
		}


		// Add some global vars!
		$vars = array();
		$vars['action_field_name'] = $this->field_name;
		$this->EE->load->vars($vars);

		// Execute the settings method
		$out = $this->settings($settings);

		// Cleanup by removing
		$this->EE->load->remove_package_path($action_path);

		return $out;
	}

	// ********************************************************************************* //

	public function save_settings($settings)
	{
		return $settings;
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file image_action.php  */
/* Location: ./system/expressionengine/third_party/channel_images/actions/image_action.php */
