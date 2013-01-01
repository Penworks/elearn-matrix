<?php

/*
=====================================================
 Shorteen
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2011-2012 Yuri Salimovskiy
 Lessn More support by Jerome Brown
=====================================================
 This software is based upon and derived from
 ExpressionEngine software protected under
 copyright dated 2004 - 2012. Please see
 http://expressionengine.com/docs/license.html
=====================================================
 File: upd.shorteen.php
-----------------------------------------------------
 Purpose: Shorten your URLs using wide range of shortening services
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}



class Shorteen_upd {

    var $version = '0.3.1';
    
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
        
        $data = array( 'module_name' => 'Shorteen' , 'module_version' => $this->version, 'has_cp_backend' => 'y', 'settings'=> serialize($settings) ); 
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
        
        return TRUE; 
        
    } 
    
    function uninstall() { 

        $this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Shorteen')); 
        
        $this->EE->db->where('module_id', $query->row('module_id')); 
        $this->EE->db->delete('module_member_groups'); 
        
        $this->EE->db->where('module_name', 'Shorteen'); 
        $this->EE->db->delete('modules'); 
        
        $this->EE->db->where('class', 'Shorteen'); 
        $this->EE->db->delete('actions'); 
        
        $this->EE->db->query("DROP TABLE exp_shorteen");
        
        return TRUE; 
    } 
    
    function update($current='') { 
        if ($current < 0.2) 
        { 
            $this->EE->db->query("CREATE TABLE IF NOT EXISTS `exp_shorteen` (
              `id` INT( 10 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
              `service` varchar(20) NOT NULL,
              `url` varchar(255) NOT NULL,
              `shorturl` varchar(128) NOT NULL,
              `created` INT( 10 ) NOT NULL ,
              KEY `service` (`service`,`url`)
            )");
        } 
        if ($current < 3.0) { 
            // Do your 3.0 v. update queries 
        } 
        return TRUE; 
    } 
	

}
/* END */
?>