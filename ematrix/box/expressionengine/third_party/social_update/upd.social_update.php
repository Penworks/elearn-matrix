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
 File: upd.social_update.php
-----------------------------------------------------
 Purpose: Send updates to social networks upon entry publishing
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

require_once PATH_THIRD.'social_update/config.php';

class Social_update_upd {

    var $version = SOCIAL_UPDATE_ADDON_VERSION;
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
    } 
    
    function install() { 
  
        $this->EE->load->dbforge(); 
        
        //----------------------------------------
		// EXP_MODULES
		// The settings column, Ellislab should have put this one in long ago.
		// No need for a seperate preferences table for each module.
		//----------------------------------------
		if ($this->EE->db->field_exists('settings', 'modules') == FALSE)
		{
			$this->EE->dbforge->add_column('modules', array('settings' => array('type' => 'TEXT') ) );
		}
        
        $settings = array();

        $data = array( 'module_name' => 'Social_update' , 'module_version' => $this->version, 'has_cp_backend' => 'y', 'has_publish_fields' => 'n', 'settings'=> serialize($settings) ); 
        $this->EE->db->insert('modules', $data);
        
        $data = array( 'class' => 'Social_update' , 'method' => 'post_delayed' ); 
        $this->EE->db->insert('actions', $data); 
        
        //install Shorteen
        $this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Shorteen')); 
        if ($query->num_rows() == 0)
        {
            $settings = array();
            $data = array( 'module_name' => 'Shorteen' , 'module_version' => '0.3.2', 'has_cp_backend' => 'y', 'settings'=> serialize($settings) ); 
            $this->EE->db->insert('modules', $data); 
            
            $data = array( 'class' => 'Shorteen' , 'method' => 'process' ); 
            $this->EE->db->insert('actions', $data); 
            
            $this->EE->db->query("CREATE TABLE IF NOT EXISTS `exp_shorteen` (
              `id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
              `service` varchar(20) NOT NULL,
              `url` varchar(255) NOT NULL,
              `shorturl` varchar(128) NOT NULL,
              `created` INT( 10 ) NOT NULL ,
              KEY `service` (`service`,`url`)
            )");
        }
        
        //exp_social_update_posts
		$fields = array(
			'post_id'			=> array('type' => 'INT',		'unsigned' => TRUE, 'auto_increment' => TRUE),
			'site_id'			=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 1),
			'channel_id'		=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'entry_id'			=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'field_id'			=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'col_id'			=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'row_id'			=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'service'			=> array('type' => 'VARCHAR',	'constraint'=> 128,	'default' => ''),
			'post'				=> array('type' => 'TEXT',		'default' => ''),
			'url'				=> array('type' => 'VARCHAR',	'constraint'=> 255,	'default' => ''),
			'post_date'			=> array('type' => 'INT',		'unsigned' => TRUE, 'default' => 0),
			'remote_user'		=> array('type' => 'VARCHAR',	'constraint'=> 128,	'default' => ''),
			'remote_post_id'	=> array('type' => 'VARCHAR',	'constraint'=> 128,	'default' => ''),
		);

		$this->EE->dbforge->add_field($fields);
		$this->EE->dbforge->add_key('post_id', TRUE);
		$this->EE->dbforge->add_key('site_id');
		$this->EE->dbforge->add_key('channel_id');
		$this->EE->dbforge->add_key('entry_id');
		$this->EE->dbforge->add_key('field_id');
		$this->EE->dbforge->create_table('social_update_posts', TRUE);

            
        $this->EE->db->query("CREATE TABLE IF NOT EXISTS `exp_social_update_accounts` (
          `service` varchar(128) NOT NULL,
          `userid` varchar(128) NOT NULL,
          `display_name` varchar(255) NOT NULL,
          KEY `service` (`service`, `userid`)
        )");
        
        $this->EE->db->query("CREATE TABLE IF NOT EXISTS `exp_social_update_settings` (
	          `site_id` int(11) NOT NULL,
	          `settings` TEXT NOT NULL,
	          UNIQUE KEY `site_id` (`site_id`)
	        )");
        
        return TRUE; 
        
    } 
    
    function uninstall() { 

        $this->EE->load->dbforge(); 
		
		$this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Social_update')); 
        
        $this->EE->db->where('module_id', $query->row('module_id')); 
        $this->EE->db->delete('module_member_groups'); 
        
        $this->EE->db->where('module_name', 'Social_update'); 
        $this->EE->db->delete('modules'); 
        
        $this->EE->db->where('class', 'Social_update'); 
        $this->EE->db->delete('actions'); 
        
        $this->EE->load->library('layout');
        $tabs['social_update'] = array( 
                            'social_update_url_base' => array(), 
                            'social_update_custom_url' => array(), 
							'social_update_twitter' => array(), 
                            'social_update_facebook' => array(),
                            'social_update_linkedin' => array()
                        );   
        $this->EE->layout->delete_layout_tabs($tabs);
        
        $this->EE->dbforge->drop_table('social_update_posts');
        $this->EE->dbforge->drop_table('social_update_accounts');
        $this->EE->dbforge->drop_table('social_update_settings');
        
        return TRUE; 
    } 
    
    function update($current='') { 
        
        if ($current < 0.3) 
        { 
            $this->EE->db->query("CREATE TABLE IF NOT EXISTS `exp_social_update_accounts` (
              `service` varchar(128) NOT NULL,
              `userid` varchar(128) NOT NULL,
              `display_name` varchar(255) NOT NULL,
              KEY `service` (`service`, `userid`)
            )");
        } 
        
        if ($current < 0.4) 
        { 
        	$this->EE->db->query("CREATE TABLE IF NOT EXISTS `exp_social_update_settings` (
	          `site_id` int(11) NOT NULL,
	          `settings` TEXT NOT NULL,
	          UNIQUE KEY `site_id` (`site_id`)
	        )");
	        
         	$this->EE->db->select('module_version, settings')
	                    ->where('module_name', 'Social_update')
	                    ->limit(1);
        	$query = $this->EE->db->get('modules');
        	
        	$data = array(
				'site_id'	=> $this->EE->config->item('site_id'),
				'settings'	=> $query->row('settings')
			);

    		$this->EE->db->insert('social_update_settings', $data);
	
        } 		   
		
		
		if ($current < 0.5) 
        { 
            $this->EE->load->dbforge(); 
   			$this->EE->dbforge->add_column('social_update_posts', array('remote_user' => array('type' => 'VARCHAR', 'constraint' => '128', 'default'=>'') ) );
   			$this->EE->dbforge->add_column('social_update_posts', array('remote_post_id' => array('type' => 'VARCHAR', 'constraint' => '128', 'default'=>'') ) );

        }     

        
        if ($current < 1.0) 
        { 
            $upgraded = false;
			
			$this->EE->load->dbforge(); 
			
			//redo the settings
            $providers = array('twitter', 'facebook', 'linkedin');
            $query = $this->EE->db->select('site_id, settings')
							->from('social_update_settings')
							->get();
			if ($query->num_rows()>0)
			{
				foreach ($query->result_array() as $row)
				{
					$settings = unserialize($row['settings']);
					$new_settings = array();
	            	foreach ($providers as $provider)
	            	{
	            		if (isset($settings['app_id'][$provider]) && $settings['app_id'][$provider]!='')
	            		{
							$key = $settings['app_id'][$provider];
							$new_settings[$key] = array(
								'provider'		=> $provider,
								'app_id'		=> $settings['app_id'][$provider],
								'app_secret'	=> $settings['app_secret'][$provider],
	                			'token'			=> $settings['token'][$provider],
	                			'token_secret'	=> $settings['token_secret'][$provider],
	                			'username'		=> (isset($settings['username'][$provider]))?$settings['username'][$provider]:'',
	                			'post_as_page'	=> (isset($settings['post_as_page'][$provider]))?$settings['post_as_page'][$provider]:'',
							);
						}
	            	}
	            	$new_settings['trigger_statuses'] = (isset($settings['trigger_statuses']))?$settings['trigger_statuses']:array('open');
	            	$new_settings['disable_javascript'] = (isset($settings['disable_javascript']))?$settings['disable_javascript']:'';
	            	$new_settings['url_shortening_service'] = (isset($settings['url_shortening_service']))?$settings['url_shortening_service']:'googl';
	            	$data = array(
						'site_id'	=> $row['site_id'],
						'settings'	=> serialize($new_settings)
					);
			
			    	$this->EE->db->replace('social_update_settings', $data);
    			}
			}
			
			//remove tabs
	        $this->EE->load->library('layout');
	        $tabs['social_update'] = array( 
		                            'social_update_url_base' => array(),
		                            'social_update_twitter' => array(), 
		                            'social_update_facebook' => array(), 
		                            'social_update_linkedin' => array()   
		                        ); 
	        $this->EE->layout->delete_layout_tabs($tabs);
	        
	        $data = array('has_publish_fields' => 'n'); 
	        $this->EE->db->where('module_name', 'Social_update');
			$this->EE->db->insert('modules', $data);
			
			
			//add column
			if ($this->EE->db->field_exists('field_id', 'social_update_posts') == FALSE)
			{
   				$this->EE->dbforge->add_column('social_update_posts', array('field_id' => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0) ) );
   				$upgraded = true;
			}
			if ($this->EE->db->field_exists('row_id', 'social_update_posts') == FALSE)
			{
   				$this->EE->dbforge->add_column('social_update_posts', array('row_id' => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0) ) );
			}
   			
   			//add action
   			$data = array( 'class' => 'Social_update' , 'method' => 'post_delayed' ); 
        	$this->EE->db->insert('actions', $data); 

            //install fieldtype
            $this->EE->load->library('addons/addons_installer');
			$this->EE->addons_installer->install_fieldtype('social_update');
			
			//install extension
			$this->EE->addons_installer->install_extension('social_update');
			
			if ($upgraded == true)
			{
				show_error(lang('social_update_is_now_fieldtype'), 500, lang('social_update').NBS.lang('warning'));
			}
            
        } 
        
        if ($current < 1.01) 
        { 
        	$this->EE->load->dbforge(); 
			if ($this->EE->db->field_exists('col_id', 'social_update_posts') == FALSE)
			{
   				$this->EE->dbforge->add_column('social_update_posts', array('col_id' => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0) ) );
			}
			if ($this->EE->db->field_exists('row_id', 'social_update_posts') == FALSE)
			{
   				$this->EE->dbforge->add_column('social_update_posts', array('row_id' => array('type' => 'INT', 'unsigned' => TRUE, 'default' => 0) ) );
			}
        }
        
        
        
        
        return TRUE; 
        
    } 
	

}
/* END */
?>