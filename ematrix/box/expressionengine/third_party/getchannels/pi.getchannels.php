<?php

if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Pvl - Getchannels
 *
 * Get the all channels of a site.
 *
 * @copyright 2011 - Pv Ledoux
 * @since 02 Feb 2011
 * @author Pierre-Vincent Ledoux <ee-addons@pvledoux.be>
 *
 */
$plugin_info = array(
	'pi_name'			=> 'Pvl - Get Channels',
	'pi_version'		=>'0.2',
	'pi_author'			=>'Pierre-Vincent Ledoux',
	'pi_author_email'	=>'pvledoux@gmail.com',
	'pi_author_url'		=> 'http://twitter.com/pvledoux/',
	'pi_description'	=> 'Returns the list of channels for a site',
	'pi_usage'			=> Getchannels::usage()
);

class Getchannels {

	/**
 	 * Data returned from the plugin.
	 *
	 * @access	public
	 * @var array
	 */
	public $return_data = '';


	/**
	 * Constructor.
	 *
	 * @access	public
	 * @return	void
	 */
	public function __construct()
	{
		$this->_ee			=& get_instance();
		$result				= $this->_ee->TMPL->parse_variables($this->_ee->TMPL->tagdata, $this->_fetch());
		$this->return_data	= $this->_ee->TMPL->parse_variables($result, array($this->_ee->config->_global_vars));
	}


	/**
	 * Annoyingly, the supposedly PHP5-only EE2 still requires this PHP4
	 * constructor in order to function.
	 *
	 * @access public
	 * @return void
	 * method first seen used by Stephen Lewis (https://github.com/experience/you_are_here.ee2_addon)
	 */
	public function Getchannels()
	{
		$this->__construct();
	}


	/**
	* Return Channels Array
	*
	* @access	private
	* @return	array
	*/
	private function _fetch()
	{
		//Get parameter
    	$site_id = $this->_ee->TMPL->fetch_param('site_id', $this->_ee->config->item('site_id'));

		//check site id
		if ($site_id == "" && !is_numeric($site_id)) {
			$this->return_data =  "ERROR: site_id parameter MUST BE supplied and numeric.";
		} else {

			//Check if cache is available
			if ( ! isset($this->_ee->session->cache['getchannels'][$site_id]['channels'])) {

				$this->_ee->db->select('exp_channels.channel_id, exp_channels.channel_name, exp_channels.channel_title, COUNT(exp_channel_data.entry_id) as total_entries')
								->from('exp_channels')
								->join('exp_channel_data', 'exp_channels.channel_id = exp_channel_data.channel_id', 'left')
								->where('exp_channels.site_id', $site_id)
								->group_by('exp_channels.channel_id')
								->order_by('exp_channels.channel_name', 'ASC');

				$channels = $this->_ee->db->get()->result_array();

				if (count($channels) === 0) {
					$results[] = array('no_results' => TRUE);
				} else {

					//Check channels member groups
					$this->_ee->db->select('*')
								->from('exp_channel_member_groups')
								->where('exp_channel_member_groups.group_id', $this->_ee->session->userdata["group_id"]);

					$groups = $this->_ee->db->get()->result_array();

					foreach ($channels as $key => $channel) {
						if (count($groups)) {
							foreach ($groups as $group) {
								if ($channel['channel_id'] == $group['channel_id']) {
									$results[] = $channel;
									break;
								}
							}
						} else {
							$results = $channels;
						}
					}

					$this->_ee->session->cache['getchannels'][$site_id]['channels'] = $results;
				}
			} else {
				$results = $this->_ee->session->cache['getchannels'][$site_id]['channels'];
			}
		}

		return $results;

	}

	/**
	 * Usage
	 *
	 * This function describes how the plugin is used.
	 *
	 * @access	public
	 * @return	string
	 */
	static function usage()
	{
		ob_start();
		?>

			Description:

			Returns Channels of a site.

			(c) Copyright 2012 Pv Ledoux

			Author: pvledoux@gmail.com
			------------------------------------------------------

			Examples:
			{exp:getchannels site_id="site_id" restricted="no"}
				{channel_id}<br/>
				{channel_name}<br/>
				{channel_title}<br/>
				{total_entries}
			{/exp:getchannels}

			------------------------------------------------------

			Parameters:
			site_id="1" : Optional (default: use the current site_id)
			restricted="yes|no": Optional. Only get logged in member authorized channels (default: "yes")

		<?php
		$buffer = ob_get_contents();

		ob_end_clean();

		return $buffer;
	}
	  // END

	}


/* End of file pi.pi.getchannels.php */
/* Location: ./system/expressionengine/third_party/getchannels/pi.getchannels.php */