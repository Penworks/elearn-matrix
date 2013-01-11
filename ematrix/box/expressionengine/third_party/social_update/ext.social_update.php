<?php

/*
=====================================================
 Social Update
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2011-2012 Yuri Salimovskiy
=====================================================
 This software is intended for usage with
 ExpressionEngine CMS, version 2.0 or higher
=====================================================
 File: mcp.social_update.php
-----------------------------------------------------
 Purpose: Send updates to social networks upon entry publishing
=====================================================
*/

if ( ! defined('BASEPATH'))
{
	exit('Invalid file request');
}

require_once PATH_THIRD.'social_update/config.php';

class Social_update_ext {

	var $name	     	= SOCIAL_UPDATE_ADDON_NAME;
	var $version 		= SOCIAL_UPDATE_ADDON_VERSION;
	var $description	= 'Send updates to social networks upon entry publishing';
	var $settings_exist	= 'y';
	var $docs_url		= 'http://www.intoeetive.com/docs/social_update.html';
    
    var $settings 		= array();

    
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	function __construct($settings = '')
	{
		$this->EE =& get_instance();        
        
        $this->EE->lang->loadfile('social_update');
	}
    
    /**
     * Activate Extension
     */
    function activate_extension()
    {
        
        $hooks = array(

            array(
    			'hook'		=> 'entry_submission_end',
    			'method'	=> 'prepare_pages_url',
    			'priority'	=> 12 //make sure it is run after Structure
    		)
    		
    	);
    	
        foreach ($hooks AS $hook)
    	{
    		$data = array(
        		'class'		=> __CLASS__,
        		'method'	=> $hook['method'],
        		'hook'		=> $hook['hook'],
        		'settings'	=> '',
        		'priority'	=> $hook['priority'],
        		'version'	=> $this->version,
        		'enabled'	=> 'y'
        	);
            $this->EE->db->insert('extensions', $data);
    	}	

    }
    
    /**
     * Update Extension
     */
    function update_extension($current = '')
    {
    	if ($current == '' OR $current == $this->version)
    	{
    		return FALSE;
    	}
    	    	
    	$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->update(
    				'extensions', 
    				array('version' => $this->version)
    	);
    }
    
    
    /**
     * Disable Extension
     */
    function disable_extension()
    {
    	$this->EE->db->where('class', __CLASS__);
    	$this->EE->db->delete('extensions');        
                    
    }
    
    
    function settings()
    {
        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=social_update');	
    }
        
    
    function prepare_pages_url($entry_id, $meta, $data)
    {
		$query = $this->EE->db->select('settings')
						->from('social_update_settings')
	                    ->where('site_id', $this->EE->config->item('site_id'))
	                    ->limit(1)
						->get();
		if ($query->num_rows()>0)
		{
			$this->settings = unserialize($query->row('settings'));
		}
		
		$post = $this->EE->db->select('post_id, field_id, col_id')
			->from('social_update_posts')
			->where('entry_id', $entry_id)
			->where('post_date', 0)
			->where('url', '')
			->get();
		if ($post->num_rows()==0)
		{
			return false;
		}
		
		$field_settings = array();
		
		foreach ($post->result_array() as $row)
		{
			//get field settings
			if (!isset($field_settings[$row['field_id']][$row['col_id']]))
            {
            	if ($row['col_id']==0)
            	{
					$q = $this->EE->db->select('field_settings')
	            			->from('channel_fields')
	            			->where('field_id', $row['field_id'])
	            			->get();
					$field_settings[$row['field_id']][$row['col_id']] = unserialize(base64_decode($q->row('field_settings')));
				}
				else
				{
					$q = $this->EE->db->select('col_settings')
	            			->from('matrix_cols')
	            			->where('field_id', $row['field_id'])
	            			->where('col_id', $row['col_id'])
	            			->get();
					$field_settings[$row['field_id']][$row['col_id']] = unserialize(base64_decode($q->row('col_settings')));
				}
            }
            
            if (in_array($field_settings[$row['field_id']][$row['col_id']]['url_type'], array('pages', 'structure')))
            {
            	$this->EE->db->select('site_pages');
				$this->EE->db->where('site_id', $this->EE->config->item('site_id'));
				$query = $this->EE->db->get('sites');

				$site_pages = unserialize(base64_decode($query->row('site_pages')));
				
				$upd = array();
				if (isset($site_pages[$this->EE->config->item('site_id')]['uris'][$entry_id]))
				{
					$upd['url'] = $this->EE->functions->create_url($site_pages[$this->EE->config->item('site_id')]['uris'][$entry_id]);
				}
				else
				{
					//fallback to default model
					$q = $this->EE->db->select('entry_id, channel_url, comment_url')
							->from('channel_titles')
							->join('channels', 'channel_titles.channel_id=channels.channel_id', 'left')
							->where('entry_id', $entry_id)
							->get();
					$entry_data = $q->row_array();
					switch ($this->settings['default_url_type'])
					{
						case 'site_url':
							$upd['url'] = $this->EE->config->item('site_url');
							break;
						case 'channel_url':
							$upd['url'] = $entry_data['channel_url'];
							break;
						case 'entry_id':
							$basepath = ($entry_data['comment_url']!='') ? $entry_data['comment_url'] : $entry_data['channel_url'];
							$upd['url'] = $this->EE->functions->create_page_url($basepath, $entry_id);
							break;
						case 'url_title':
						default:
							$basepath = ($entry_data['comment_url']!='') ? $entry_data['comment_url'] : $entry_data['channel_url'];
							$upd['url'] = $this->EE->functions->create_page_url($basepath, $entry_data['url_title']);
							break;
					}
				}				
				
				$this->EE->db->where('post_id', $row['post_id']);
				$this->EE->db->update('social_update_posts', $upd);
            }
		}
		
		require_once PATH_THIRD.'social_update/mod.social_update.php';
		$Social_update = new Social_update();
		$Social_update->post_delayed();
    	
   	}
    


}
// END CLASS
