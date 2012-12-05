<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine Sitemap Module
 *
 * @package		Sitemap
 * @subpackage	Sitemap
 * @category	Sitemap
 * @author		Ben Croker
 * @link		http://www.putyourlightson.net/sitemap-module
 */


class Sitemap_tab {

	public function Sitemap_tab()
	{
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------	
	
	public function publish_tabs($channel_id, $entry_id = '')
	{	
		// load language file
		$this->EE->lang->loadfile('sitemap');								
		
		
		// check if this channel is included in the sitemap			
		$query = $this->EE->db->query("SELECT channel_id FROM exp_sitemap WHERE channel_id = '".$channel_id."' AND included = 1");
		
		if (!$query->num_rows)
		{
			return array();
		}
		
		// set checked to true if not editing an existing entry
		$checked = !$entry_id ? TRUE : FALSE;
	
		$settings = array(
			'ping_sitemap' => array(
				'field_id'				=> 'ping_sitemap',
				'field_label'			=> 'Sitemap',
				'field_type'			=> 'checkboxes',
				'field_list_items'		=> array('1' => lang('ping_search_engines')),
				'field_required'		=> 'n',
				'field_data'			=> ($checked ? lang('ping_search_engines') : ''),
				'field_pre_populate'	=> 'n',
				'field_instructions'	=> lang('sitemap_ping_instructions'),
				'field_text_direction'	=> 'ltr'
			)
		);
		
		foreach ($settings as $k => $v)
		{
			$this->EE->api_channel_fields->set_settings($k, $v);
		}
		
		return $settings;
	}
		
	// --------------------------------------------------------------------	

	public function validate_publish($params)
	{
    	return FALSE;        
	}
	
}
// END CLASS