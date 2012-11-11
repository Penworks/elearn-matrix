<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine Sitemap Module
 *
 * @package		Sitemap
 * @category	Module
 * @author		Ben Croker
 * @link		http://www.putyourlightson.net/sitemap-module
 */
 

class Sitemap_mcp {

	// defaults
	var $default_change_frequency = 'weekly';
	var $default_priority = '0.5';

	
	/**
	 * Constructor
	 */
	function __construct( $switch = TRUE )
	{
		$this->EE =& get_instance();
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Index
	  */
	function index($message = '')
	{
		$site_id = $this->EE->config->item('site_id');
		
		$this->EE->load->library('table');
		$this->EE->load->library('javascript');
		$this->EE->load->helper('form');
		
		$this->EE->cp->set_variable('cp_page_title', $this->EE->lang->line('sitemap'));

		$this->EE->cp->set_right_nav(array('insert_new_location' => BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap'.AMP.'method=new_url'));
		 			
		// get sitemap url from template group and name
		$sitemap_url = '';
		
		$query = $this->EE->db->query("SELECT template_name, group_name FROM exp_templates JOIN exp_template_groups ON exp_templates.group_id = exp_template_groups.group_id WHERE template_data LIKE '%{exp:sitemap:get%' AND exp_templates.site_id = '".$site_id."'");
		
		if ($row = $query->row())
		{
			$sitemap_url = $this->EE->functions->create_url($row->group_name.'/'.$row->template_name);
		}
		
		if ($row = $query->row())
		{
			$sitemap_url = $this->EE->functions->create_url($row->group_name.'/'.$row->template_name);
		}
		
		$vars = array(
			'site_index' => $this->EE->functions->fetch_site_index(1),
			'sitemap_url' => $sitemap_url,
			'newer_version_exists' => false //$this->newer_version_exists() - deprecated
		);		
		
		
		/** ----------------------------------------
		/**  Locations
		/** ----------------------------------------*/	
	
		$query = $this->EE->db->query("SELECT * FROM exp_sitemap WHERE channel_id = '' AND site_id = '".$site_id."'");  
		
		if ($query->num_rows == 0)
		{
			$data = array(
				'url' => $this->EE->functions->fetch_site_index(1),
				'site_id' => $this->EE->config->item('site_id'),
				'change_frequency' => '',
				'priority' => ''
			);
			
			// insert new row
			$this->EE->db->insert('sitemap', $data);
			
			$query = $this->EE->db->query("SELECT * FROM exp_sitemap WHERE channel_id = '' AND site_id = '".$site_id."'"); 
		}		
		
		$vars['locations'] = $query->result();
		
		
		/** ----------------------------------------
		/**  Channels
		/** ----------------------------------------*/

		$query = $this->EE->db->query("SELECT exp_channels.channel_id, channel_title, status_group, id, url, included, statuses, change_frequency, priority FROM exp_channels LEFT JOIN exp_sitemap ON exp_channels.channel_id = exp_sitemap.channel_id WHERE exp_channels.site_id = '".$site_id."'"); 			
		$vars['channels'] = $query->result();
		
		
		// get statuses
		$this->EE->load->model('Status_model');
		$query = $this->EE->Status_model->get_statuses();
		$vars['statuses'] = $query->result();
		
		
		return $this->EE->load->view('index', $vars, TRUE);
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Update URLs
	  */
	function update_urls()
	{
		for ($i = 0; $this->EE->input->post('id_'.$i); $i++)
		{
			// update url
			$this->EE->db->query("UPDATE exp_sitemap SET url = '".$this->EE->input->post('url_'.$i)."', change_frequency = '".$this->EE->input->post('change_frequency_'.$i)."', priority = '".$this->EE->input->post('priority_'.$i)."' WHERE id = '".$this->EE->input->post('id_'.$i)."'");		
		}
				
		$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('urls_updated'));
		
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap'); 
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Update Channels
	  */
	function update_channels()
	{ 
		for ($i = 0; $this->EE->input->post('channel_id_'.$i); $i++)
		{
			$data = array(
				'channel_id' => $this->EE->input->post('channel_id_'.$i),
				'url' => $this->EE->input->post('url_'.$i),
				'included' => $this->EE->input->post('included_'.$i),
				'statuses' => ($this->EE->input->post('statuses_'.$i) ? implode(',', $this->EE->input->post('statuses_'.$i)) : ''),
				'change_frequency' => $this->EE->input->post('change_frequency_'.$i),
				'priority' => $this->EE->input->post('priority_'.$i)
			);
			
			// update row
			if ($this->EE->input->post('id_'.$i) != '')
			{	
				$this->EE->db->where('id', $this->EE->input->post('id_'.$i));
				$this->EE->db->update('sitemap', $data);
			}
			
			// insert new row
			else
			{
				$this->EE->db->insert('sitemap', $data);
			}
		}
		
		$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('channels_updated'));
		
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap'); 
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Create new url
	  */
	function new_url()
	{  
		$data = array(
			'url' => $this->EE->functions->fetch_site_index(1),
			'site_id' => $this->EE->config->item('site_id'),
			'change_frequency' => '',
			'priority' => ''
		);
		
		// insert new row
		$this->EE->db->insert('sitemap', $data);
			
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap'); 
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Delete url
	  */
	function delete_url()
	{  
		if ($id = $this->EE->input->get_post('id'))
		{
			$this->EE->db->query("DELETE FROM exp_sitemap WHERE id = '".$id."'");
		}	 
		
		$this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=sitemap'); 
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Check if newer version exists
	  */
	function newer_version_exists()
	{  
		$url = 'http://www.putyourlightson.net/index.php/projects/sitemap_version';
		
		// get module version
		$query = $this->EE->db->query("SELECT module_version FROM exp_modules WHERE module_name = 'Sitemap'");
		
		if (!$row = $query->row())
		{
			return FALSE;
		}
		
		$version = $row->module_version;
		
		$response = '';
				
		// cURL method
		if (function_exists('curl_init'))
		{			
			$curl_handle = curl_init($url);
			curl_setopt($curl_handle, CURLOPT_HEADER, TRUE);
			curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, TRUE);
			$response = curl_exec($curl_handle);
			curl_close($curl_handle);
			
			preg_match('/version="(.*?)"/i', $response, $matches);
			$latest_version = (isset($matches[1])) ? $matches[1] : 1;
			
			if ($latest_version > $version)
			{
				return TRUE;
			}			
		}
		
		// file method
		else
		{
			$response = file_get_contents($url);
			
			preg_match('/version="(.*?)"/i', $response, $matches);
			$latest_version = (isset($matches[1])) ? $matches[1] : 1;
			
			if ($latest_version > $version)
			{
				return TRUE;
			}
		}
		
		return FALSE;	
	}
	
}

// END CLASS

/* End of file mcp.sitemap.php */
/* Location: ./system/expressionengine/third_party/sitemap/mcp.sitemap.php */