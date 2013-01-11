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
	
	var $maxlen 	= array(
                        'twitter'  => 140,
                        'facebook' => 420,
                        'linkedin' => 700
                    );	
    
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
    				$var_row['linkedin_post'] = false;
    				$var_row['post_link'] = 'http://twitter.com/#!/'.$row['remote_user'].'/status/'.$row['remote_post_id'];
    				break;
    			case 'facebook':
    				$var_row['facebook_post'] = true;
    				$var_row['twitter_post'] = false;
    				$var_row['linkedin_post'] = false;
    				$var_row['post_link'] = 'http://www.facebook.com/'.$row['remote_user'].'/posts/'.$row['remote_post_id'];
    				break;	
   				case 'linkedin':
    				$var_row['facebook_post'] = false;
    				$var_row['twitter_post'] = false;
    				$var_row['linkedin_post'] = true;
    				$var_row['post_link'] = 'http://www.linkedin.com/profile/view?id='.$row['remote_user'];
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
    
    
    function post_delayed($entry_id=false)
    {
    	//post unposted
    	$this->EE->db->select('social_update_posts.*, entry_date, status')
			->from('social_update_posts')
			->join('channel_titles', 'social_update_posts.entry_id=channel_titles.entry_id', 'left')
			->where('post_date', 0);
		if ($entry_id!==false)
		{
			$this->EE->db->where('social_update_posts.entry_id', $entry_id);
		}
		$posts = $this->EE->db->get();
		if ($posts->num_rows()==0)
		{
			return false;
		}
		
		
		$query = $this->EE->db->select()
						->from('social_update_settings')
						->get();
		if ($query->num_rows()==0)
		{
			return false;
		}
		foreach ($query->result_array() as $row)
		{
			$module_settings[$row['site_id']] = unserialize($row['settings']);
		}
		
		$field_settings = array();
		
		foreach ($posts->result_array() as $post_data)
		{
			if (in_array($post_data['status'], $module_settings[$post_data['site_id']]['trigger_statuses']) && $post_data['entry_date']<=$this->EE->localize->now)
			{
				if ($post_data['field_id']==0) continue;
				
				$data = $post_data['post'];
				$url = $post_data['url'];
				//shorten the stuff
				if ($url!='' && (strlen($data." ".$url) > $this->maxlen[$post_data['service']] || $module_settings[$post_data['site_id']]['force_url_shortening']=='y'))
		        {
		            if ( ! class_exists('Shorteen'))
		        	{
		        		require_once PATH_THIRD.'shorteen/mod.shorteen.php';
		        	}
		        	
		        	$SHORTEEN = new Shorteen();
		            
		            $shorturl = $SHORTEEN->process($module_settings[$post_data['site_id']]['url_shortening_service'], $url, true);
		            if ($shorturl!='')
		            {
		                $url = $shorturl;
		            }
		        }
		        //still too long? truncate the message
		        //at least one URL should always be included
		        if (strlen($data." ".$url) > $this->maxlen[$post_data['service']])
		        {
		            $data = $this->_char_limit($data, ($this->maxlen[$post_data['service']]-strlen($url)-1));
		        }            
				
	            $lib = $post_data['service'].'_oauth';
	            
	            if (!isset($field_settings[$post_data['field_id']][$post_data['col_id']]))
	            {
					if ($post_data['col_id']==0)
	            	{
						$q = $this->EE->db->select('field_settings')
		            			->from('channel_fields')
		            			->where('field_id', $post_data['field_id'])
		            			->get();
						$field_settings[$post_data['field_id']][$post_data['col_id']] = unserialize(base64_decode($q->row('field_settings')));
					}
					else
					{
						$q = $this->EE->db->select('col_settings')
		            			->from('matrix_cols')
		            			->where('field_id', $post_data['field_id'])
		            			->where('col_id', $post_data['col_id'])
		            			->get();
						$field_settings[$post_data['field_id']][$post_data['col_id']] = unserialize(base64_decode($q->row('col_settings')));
					}
	            }
	            
	            $post_params = array(
					'key'=>$module_settings[$post_data['site_id']][$field_settings[$post_data['field_id']][$post_data['col_id']]['provider']]['app_id'], 
					'secret'=>$module_settings[$post_data['site_id']][$field_settings[$post_data['field_id']][$post_data['col_id']]['provider']]['app_secret']
				);
	            $this->EE->load->library($lib, $post_params);
	            if ($lib=='facebook_oauth')
	            {
	                $remote_post = $this->EE->$lib->post(
						$url, 
						$data, 
						$module_settings[$post_data['site_id']][$field_settings[$post_data['field_id']][$post_data['col_id']]['provider']]['token'], 
						$module_settings[$post_data['site_id']][$field_settings[$post_data['field_id']][$post_data['col_id']]['provider']]['token_secret'], 
						$module_settings[$post_data['site_id']][$field_settings[$post_data['field_id']][$post_data['col_id']]['provider']]['username']
					); 
	            }
	            else
	            {
	                $remote_post = $this->EE->$lib->post(
						$data." ".$url, 
						$module_settings[$post_data['site_id']][$field_settings[$post_data['field_id']][$post_data['col_id']]['provider']]['token'], 
						$module_settings[$post_data['site_id']][$field_settings[$post_data['field_id']][$post_data['col_id']]['provider']]['token_secret']
					); 
	            }
	            
	            if (!empty($remote_post) && $remote_post['remote_user']!='' && $remote_post['remote_post_id']!='')
	            {
	            	$upd = array(
						'service'			=> $post_data['service'],
						'post'				=> $data,
						'post_date'			=> $this->EE->localize->now,
						'remote_user'		=> $remote_post['remote_user'],
						'remote_post_id'	=> $remote_post['remote_post_id'],
					);
					
					$this->EE->db->where('post_id', $post_data['post_id']);
					$this->EE->db->update('social_update_posts', $upd);
	            }
				
			}
		}
    }
    
    
    
    
    //trims the string to be exactly of less of the given length
    //the integrity of words is kept 
    function _char_limit($str, $length, $minword = 3)
    {
        $sub = '';
        $len = 0;
       
        foreach (explode(' ', $str) as $word)
        {
            $part = (($sub != '') ? ' ' : '') . $word;
            $sub .= $part;
            $len += strlen($part);
           
            if (strlen($word) > $minword && strlen($sub) >= $length)
            {
                break;
            }
        }
       
        return $sub . (($len < strlen($str)) ? '...' : '');

    }
    

}
/* END */
?>