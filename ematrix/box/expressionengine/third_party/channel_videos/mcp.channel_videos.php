<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Videos Module Control Panel Class
 *
 * @package			DevDemon_ChannelVideos
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_videos/
 * @see				http://expressionengine.com/user_guide/development/module_tutorial.html#control_panel_file
 */
class Channel_videos_mcp
{

	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();

		// Load Models & Libraries & Helpers
		$this->EE->load->library('channel_videos_helper');
		//$this->EE->load->model('points_model');

		// Some Globals
		$this->base = BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=channel_videos';
		$this->base_short = 'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=channel_videos';
		$this->site_id = $this->EE->config->item('site_id');

		// Global Views Data
		$this->vData['base_url'] = $this->base;
		$this->vData['base_url_short'] = $this->base_short;
		$this->vData['method'] = $this->EE->input->get('method');

		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('channel_videos'));

		if (! defined('DEVDEMON_THEME_URL')) define('DEVDEMON_THEME_URL', $this->EE->config->item('theme_folder_url') . 'third_party/');

		$this->mcp_globals();

		// Add Right Top Menu
		$this->EE->cp->set_right_nav(array(
			'cv:docs' 			=> $this->EE->cp->masked_url('http://www.devdemon.com/channel_videos/docs/'),
		));

		// Debug
		//$this->EE->db->save_queries = TRUE;
		//$this->EE->output->enable_profiler(TRUE);
	}

	// ********************************************************************************* //

	/**
	 * MCP PAGE: Index
	 *
	 * @access public
	 * @return string
	 */
	public function index()
	{
		return $this->players();
	}

	// ********************************************************************************* //

	public function players()
	{
		// Page Title & BreadCumbs
		$this->vData['PageHeader'] = 'players';

		// Grab Settings
		$query = $this->EE->db->query("SELECT settings FROM exp_modules WHERE module_name = 'Channel_videos'");
		if ($query->row('settings') != FALSE)
		{
			$settings = @unserialize($query->row('settings'));

			if (isset($settings['site:'.$this->site_id]) == FALSE)
			{
				$settings['site:'.$this->site_id] = array();
			}
		}

		if (isset($settings['site:'.$this->site_id]['players']) == FALSE OR $settings['site:'.$this->site_id]['players'] == FALSE) $settings['site:'.$this->site_id]['players'] = array();

		$this->vData = array_merge($this->vData, $settings['site:'.$this->site_id]['players']);

		return $this->EE->load->view('mcp/players', $this->vData, TRUE);
	}

	// ********************************************************************************* //

	public function update_players()
	{
		// Grab Settings
		$query = $this->EE->db->query("SELECT settings FROM exp_modules WHERE module_name = 'Channel_videos'");
		if ($query->row('settings') != FALSE)
		{
			$settings = @unserialize($query->row('settings'));

			if (isset($settings['site:'.$this->site_id]) == FALSE)
			{
				$settings['site:'.$this->site_id] = array();
			}
		}

		$settings['site:'.$this->site_id]['players'] = $this->EE->input->post('players');

		// Put it Back
		$this->EE->db->set('settings', serialize($settings));
		$this->EE->db->where('module_name', 'Channel_videos');
		$this->EE->db->update('exp_modules');


		$this->EE->functions->redirect($this->base . '&method=index');
	}

	// ********************************************************************************* //

	private function mcp_globals()
	{
		$this->EE->cp->set_breadcrumb($this->base, $this->EE->lang->line('channel_videos_module_name'));

		$this->EE->channel_videos_helper->mcp_meta_parser('gjs', '', 'ChannelVideos');
		$this->EE->channel_videos_helper->mcp_meta_parser('css', DEVDEMON_THEME_URL . 'channel_videos/channel_videos_mcp.css', 'channel_videos-mcp');
		$this->EE->channel_videos_helper->mcp_meta_parser('js', DEVDEMON_THEME_URL . 'channel_videos/channel_videos_mcp.js', 'channel_videos-mcp');
	}

	// ********************************************************************************* //

	public function ajax_router()
	{

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
	}

	// ********************************************************************************* //

} // END CLASS

/* End of file mcp.shop.php */
/* Location: ./system/expressionengine/third_party/points/mcp.shop.php */