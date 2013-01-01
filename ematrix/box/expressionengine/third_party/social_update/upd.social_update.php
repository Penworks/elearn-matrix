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

        $data = array( 'module_name' => 'Social_update' , 'module_version' => $this->version, 'has_cp_backend' => 'y', 'has_publish_fields' => 'y', 'settings'=> serialize($settings) ); 
        $this->EE->db->insert('modules', $data);
        
        //install Shorteen
        $this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Shorteen')); 
        if ($query->num_rows() == 0)
        {
            $settings = array();
            $data = array( 'module_name' => 'Shorteen' , 'module_version' => '0.2.0', 'has_cp_backend' => 'y', 'settings'=> serialize($settings) ); 
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
        
        $this->EE->db->query("CREATE TABLE IF NOT EXISTS `exp_social_update_posts` (
              `post_id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
              `site_id` INT( 10 ) NOT NULL DEFAULT '1',
              `channel_id` INT( 10 ) NOT NULL DEFAULT '1',
              `entry_id` INT( 10 ) NOT NULL DEFAULT '1',
              `service` varchar(128) NOT NULL,
              `post` TEXT NOT NULL,
              `url` varchar(255) NOT NULL,
              `post_date` INT( 10 ) NOT NULL ,
              `remote_user` varchar(128) NOT NULL DEFAULT '',
              `remote_post_id` varchar(128) NOT NULL DEFAULT '',
              KEY `service` (`service`),
              KEY `site_id` (`site_id`),
              KEY `channel_id` (`channel_id`),
              KEY `entry_id` (`entry_id`)
            )");
            
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
        
        $this->EE->load->library('layout');
        $tabs['social_update'] = array( 
                            'social_update_url_base' => array( 
                                'visible' => true, 
                                'collapse' => false, 
                                'htmlbuttons' => false, 
                                'width' => '100%' 
                            ),
                            'social_update_twitter' => array( 
                                'visible' => true, 
                                'collapse' => false, 
                                'htmlbuttons' => false, 
                                'width' => '100%'
                            ), 
                            'social_update_facebook' => array( 
                                'visible' => true, 
                                'collapse' => false, 
                                'htmlbuttons' => false, 
                                'width' => '100%' 
                            ), 
                            'social_update_linkedin' => array( 
                                'visible' => true, 
                                'collapse' => false, 
                                'htmlbuttons' => false, 
                                'width' => '100%' 
                            )   
                        ); 
        $this->EE->layout->add_layout_tabs($tabs, 'social_update');

        return TRUE; 
        
    } 
    
    function uninstall() { 

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
							'social_update_twitter' => array(), 
                            'social_update_facebook' => array(),
                            'social_update_linkedin' => array()
                        );   
        $this->EE->layout->delete_layout_tabs($tabs);
        
        $this->EE->db->query("DROP TABLE exp_social_update_posts");
        $this->EE->db->query("DROP TABLE exp_social_update_accounts");
        $this->EE->db->query("DROP TABLE exp_social_update_settings");
        
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
        
        if ($current < 0.7) 
        { 
            $this->EE->load->library('layout');
	        $tabs['social_update'] = array( 
	                            'social_update_url_base' => array( 
	                                'visible' => true, 
	                                'collapse' => false, 
	                                'htmlbuttons' => false, 
	                                'width' => '100%' 
	                            ),
	                            'social_update_twitter' => array( 
	                                'visible' => true, 
	                                'collapse' => false, 
	                                'htmlbuttons' => false, 
	                                'width' => '100%'
	                            ), 
	                            'social_update_facebook' => array( 
	                                'visible' => true, 
	                                'collapse' => false, 
	                                'htmlbuttons' => false, 
	                                'width' => '100%' 
	                            ), 
	                            'social_update_linkedin' => array( 
	                                'visible' => true, 
	                                'collapse' => false, 
	                                'htmlbuttons' => false, 
	                                'width' => '100%' 
	                            )   
	                        ); 
	        $this->EE->layout->add_layout_tabs($tabs, 'social_update');
        } 
        
        return TRUE; 
        
    } 
	

}
/* END */
?>