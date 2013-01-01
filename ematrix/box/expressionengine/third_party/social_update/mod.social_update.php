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
 File: mod.social_update.php
-----------------------------------------------------
 Purpose: Send updates to social networks upon entry publishing
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}


class Social_update {

    var $return_data	= ''; 	
    
    var $settings = array();

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function __construct()
    {        
    	$this->EE =& get_instance(); 
    }
    /* END */
    
    
    function data()
    {
    	if ($this->EE->TMPL->fetch_param('entry_id')===false)
    	{
    		return $this->EE->TMPL->no_results();
    	}

    	$this->EE->db->select()->from('social_update_posts')->where('entry_id', $this->EE->TMPL->fetch_param('entry_id'));
    	$q = $this->EE->db->get();
    	if ($q->num_rows()==0)
    	{
    		return $this->EE->TMPL->no_results();
    	}

    	$this->EE->load->library('typography');
		$this->EE->typography->initialize();
    	
    	$output = '';

    	foreach ($q->result_array() as $row)
    	{
    		$var_row = array();
			switch ($row['service'])
    		{
    			case 'twitter':
    				$var_row['twitter_post'] = true;
    				$var_row['facebook_post'] = false;
    				$var_row['post_link'] = 'http://twitter.com/#!/'.$row['remote_user'].'/status/'.$row['remote_post_id'];
    				break;
    			case 'facebook':
    				$var_row['facebook_post'] = true;
    				$var_row['twitter_post'] = false;
    				$var_row['post_link'] = 'http://www.facebook.com/'.$row['remote_user'].'/posts/'.$row['remote_post_id'];
    				break;	
    		}
    		$var_row['post_id'] = $row['remote_post_id'];
    		$var_row['post_date'] = $row['post_date'];
    		$var_row['post_text'] = $this->EE->typography->parse_type($row['post']);
    		
    		$tagdata = $this->EE->TMPL->parse_variables_row($this->EE->TMPL->tagdata, $var_row);

    		$output .= $tagdata;

    	}
    	return $output;
    }

}
/* END */
?>