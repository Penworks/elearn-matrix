<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * ExpressionEngine Sitemap Module
 *
 * @package			Sitemap
 * @subpackage		Modules
 * @category		Modules
 * @author			Ben Croker
 * @link			http://www.putyourlightson.net/sitemap-module
 */
 

// get config
require_once PATH_THIRD.'sitemap/config'.EXT;


class Sitemap_upd {

	var $version = SITEMAP_VERSION;


	/**
	 * Constructor
	 */
	function __construct()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
		$this->EE->load->dbforge();
	}

	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */
	function install()
	{		
		$fields = array(
						'id'	=> array(
											'type'			=> 'int',
											'constraint'		=> 7,
											'unsigned'		=> TRUE,
											'null'			=> FALSE,
											'auto_increment'	=> TRUE
										),
						'channel_id'	=> array(
											'type' 			=> 'varchar',
											'constraint'		=> '10',
											'null'			=> FALSE,
											'default'			=> ''
										),
						'url'	=> array(
											'type'			=> 'text',
											'null'			=> FALSE
										),
						'site_id'	=> array(
											'type' 			=> 'varchar',
											'constraint'		=> '10',
											'null'			=> FALSE,
											'default'			=> ''
										),
						'included'	=> array(
											'type' 			=> 'varchar',
											'constraint'		=> '10',
											'null'			=> FALSE,
											'default'			=> ''
										),
						'statuses'	=> array(
											'type' 			=> 'text',
											'null'			=> FALSE
										),
						'change_frequency'	=> array(
											'type' 			=> 'varchar',
											'constraint'		=> '10',
											'null'			=> FALSE,
											'default'			=> ''
										),
						'priority'	=> array(
											'type' 			=> 'varchar',
											'constraint'		=> '10',
											'null'			=> FALSE,
											'default'			=> ''
										)
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('id', TRUE);
		$this->EE->dbforge->create_table('sitemap', TRUE);
		
					
		// add module
		$data = array(
			'module_name' 	=> 'Sitemap',
			'module_version' 	=> $this->version,
			'has_cp_backend' 	=> 'y',
			'has_publish_fields' => 'y'
		);
		$this->EE->db->insert('modules', $data);
		
		
		// add sitemap pinger tab
		$this->EE->load->library('layout');
		$this->EE->layout->add_layout_tabs($this->tabs(), 'sitemap');
			
		
		return TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Module uninstaller
	  */
	function uninstall()
	{
		$this->EE->db->select('module_id');
		$query = $this->EE->db->get_where('modules', array('module_name' => 'Sitemap'));

		$this->EE->db->where('module_id', $query->row('module_id'));
		$this->EE->db->delete('module_member_groups');

		$this->EE->db->where('module_name', 'Sitemap');
		$this->EE->db->delete('modules');

		$this->EE->dbforge->drop_table('sitemap');
		
		$this->EE->load->library('layout');
		$this->EE->layout->delete_layout_tabs($this->tabs(), 'sitemap');

		return TRUE;
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Update Module
	  */
	function update($current='')
	{
		if ($current == '' || $current == $this->version)
		{
			return FALSE;
		}
	
		if ($current < '1.4')
		{
			// add site_id column to table
			$this->EE->db->query("ALTER TABLE exp_sitemap ADD COLUMN site_id VARCHAR(10) NOT NULL AFTER url");
		
			// add current site_id to all locations with blank site_id
			$this->EE->db->query("UPDATE exp_sitemap SET site_id = '".$PREFS->ini('site_id')."' WHERE channel_id = '' AND site_id = ''");
			
			// add urls to channels with blank urls	
			$query = $this->EE->db->query("SELECT * FROM exp_sitemap JOIN (exp_channels, exp_sites) ON exp_sitemap.channel_id = exp_channels.channel_id AND exp_channels.site_id = exp_sites.site_id");

			foreach ($query->result as $row)
			{			
				// get site index for current channel
				$site_prefs = unserialize($row['site_system_preferences']);
				$site_url = $site_prefs['site_url'];
				$site_url = (substr($site_url, -1) == "/") ? $site_url : $site_url."/";
				$site_index = $site_url.$site_prefs['site_index'];
				$site_index = (substr($site_index, -1) == "/") ? $site_index : $site_index."/";
				
				// get url
				$url = $site_index.$row['template'];
				
				// add url to channel
				$this->EE->db->query("UPDATE exp_sitemap SET url = '".$url."' WHERE channel_id = '".$row['channel_id']."'");				
			}
			
			// drop template column
			$this->EE->db->query("ALTER TABLE exp_sitemap DROP COLUMN template");
		}
		
		if ($current < '1.6')
		{
			// add {url_title} to end of channel urls
			$entries = $this->EE->db->query("SELECT id, url FROM exp_sitemap WHERE channel_id != ''");
			
			foreach ($entries->result as $entry)
			{
				$id = $entry['id'];
				$url = $entry['url'];
				
				// check if we need to add a slash to the end of the url
				if (substr($url, -1) != '/')
				{
					$url .=  '/';
				}
				
				$url .= '{url_title}/';
				
				// update db
				$this->EE->db->query("UPDATE exp_sitemap SET url = '$url' WHERE id = '$id'");
			}
		}
		
		if ($current < '2.0')
		{
			// rename column
			$this->EE->db->query("ALTER TABLE exp_sitemap CHANGE weblog_id channel_id VARCHAR(10) NOT NULL");		
		}
		
		if ($current < '2.2')
		{
			// change has_publish_fields to yes
			$this->EE->db->query("UPDATE exp_modules SET has_publish_fields = 'y' WHERE module_name = 'Sitemap'");
			
			// add sitemap pinger tab
			$this->EE->load->library('layout');
			$this->EE->layout->add_layout_tabs($this->tabs(), 'sitemap');
		}
		
		if ($current < '2.5')
		{
			// rename status column
			$this->EE->db->query("ALTER TABLE exp_sitemap CHANGE COLUMN status included VARCHAR(10) NOT NULL");
			
			// add statuses column
			$this->EE->db->query("ALTER TABLE exp_sitemap ADD COLUMN statuses TEXT NOT NULL AFTER included");
		}
		
		$this->EE->db->query("UPDATE exp_modules 
					SET module_version = '".$this->version."' 
					WHERE module_name = 'Sitemap'");
	}
	
	// --------------------------------------------------------------------
	
	/**
	  *  Tabs
	  */
	function tabs()
	{
		$tabs['sitemap'] = array(
			'ping_sitemap'	=> array(
						'visible'	=> 'true',
						'collapse'	=> 'false',
						'htmlbuttons'	=> 'false',
						'width'		=> '100%'
						)
			);	
					
		return $tabs;	
	}
	
}

// END CLASS

/* End of file upd.sitemap.php */
/* Location: ./system/expressionengine/third_party/sitemap/upd.sitemap.php */