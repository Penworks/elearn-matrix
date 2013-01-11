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
 File: tab.social_update.php
-----------------------------------------------------
 Purpose: Send updates to social networks upon entry publishing
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

class Social_update_tab {
    
    var $providers = array('twitter', 'facebook', 'linkedin');
    var $maxlen 		= array(
                                'twitter'  => 140,
                                'facebook' => 420,
                                'linkedin' => 700
                            );
    
    var $settings = array();
	
	function __construct()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
        $this->EE->lang->loadfile('social_update');  
        $this->EE->db->select('module_version, settings')
                    ->where('module_name', 'Social_update')
                    ->limit(1);
        $query = $this->EE->db->get('modules');
        if ($query->row('module_version') >= 0.4)
        {
			$this->EE->db->select('settings')
	                    ->where('site_id', $this->EE->config->item('site_id'))
	                    ->limit(1);
	        $query = $this->EE->db->get('social_update_settings');
		} 
		$this->settings = ($query->num_rows()>0)?unserialize($query->row('settings')):array();
	}

	function publish_tabs($channel_id, $entry_id = '')
	{
		return array();
		
		$theme_folder_url = trim($this->EE->config->item('theme_folder_url'), '/').'/third_party/social_update/';
		
		if (!isset($this->settings['disable_javascript']) ||  $this->settings['disable_javascript']!='y')
		{
	        $this->EE->cp->add_to_foot('<link type="text/css" href="'.$theme_folder_url.'jquery.maxlength.css" rel="stylesheet" />');
	        $this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$theme_folder_url.'jquery.maxlength.min.js"></script>');
	
	        $js = "
$('#social_update__social_update_twitter').maxlength({ 
    max: 120,
    truncate: true,
    showFeedback: true,
    feedbackTarget: '#twitter_chars_avail',
    feedbackText: '{r}'
}); 
$('#social_update__social_update_facebook').maxlength({ 
    max: 370,
    truncate: true,
    showFeedback: true,
    feedbackTarget: '#facebook_chars_avail',
    feedbackText: '{r}'
}); 
$('#social_update__social_update_linkedin').maxlength({ 
    max: 650,
    truncate: true,
    showFeedback: true,
    feedbackTarget: '#linkedin_chars_avail',
    feedbackText: '{r}'
}); 
        ";


	        $this->EE->javascript->output($js);
	        $this->EE->javascript->compile();
		}
        
        if ($entry_id!='')
        {
            $this->EE->db->select('service, post, url, post_date')
                        ->from('exp_social_update_posts')
                        ->where('entry_id', $entry_id);
            $q = $this->EE->db->get();
            if ($q->num_rows()>0)
            {
                $data = array();
                foreach ($q->result_array() as $row)
                {
                    $data[$row['service']]->post = $row['post'];
                    $data[$row['service']]->url = $row['url'];
                    $data[$row['service']]->post_date = $row['post_date'];
                }
            }
        }
        
        $url_options = array('url_title'=>lang('url_title'), 'entry_id'=>lang('entry_id'));
        $this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Pages')); 
        if ($query->num_rows()>0) $url_options['pages'] = 'Pages'.lang('module');
        $this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Structure')); 
        if ($query->num_rows()>0) $url_options['structure'] = 'Structure'.lang('module');
        $url_options['channel_url'] = lang('channel_url');
        $url_options['site_url'] = lang('site_url');
        $url_options['manual'] = lang('do_not_include_url');
        
        $tab_settings[] = array(
           'field_id' => 'social_update_url_base',
           'field_label' => lang('url_base'),
           'field_required' => 'n',
           'field_data' => 'url_title',
           'field_list_items' => $url_options,
           'field_fmt' => '',
           'field_instructions' => '',
           'field_show_fmt' => 'n',
           'field_fmt_options' => array(),
           'field_pre_populate' => 'n',
           'field_text_direction' => 'ltr',
           'field_type' => 'select'
        );
        $tab_settings['string_override'] = lang('social_update_is_now_fieldtype');
        
        foreach ($this->providers as $provider)
        {
            if (!isset($data["$provider"]->post)) $data["$provider"]->post = '';
            $instructions = lang($provider.'_instructions');
            if (!isset($this->settings['disable_javascript']) || $this->settings['disable_javascript']!='y') $instructions .= lang($provider.'_counter');
            $set = array(
                   'field_id' => 'social_update_'.$provider,
                   'field_label' => lang($provider),
                   'field_required' => 'n',
                   'field_data' => $data["$provider"]->post,
                   'field_fmt' => '',
                   'field_instructions' => $instructions,
                   'field_show_fmt' => 'n',
                   'field_fmt_options' => array(),
                   'field_pre_populate' => 'n',
                   'field_text_direction' => 'ltr'
                );
            if (isset($this->settings["token"]["$provider"]) && $this->settings["token"]["$provider"]!='')
            {
                $set['field_type'] = 'textarea';
                $set['field_ta_rows'] = 5;
            }
            else
            {
                $set['field_type'] = 'hidden';
            }
            if (isset($data["$provider"]->post_date) && $data["$provider"]->post_date!=0)
            {
                $date_fmt = ($this->EE->session->userdata('time_format') != '') ? $this->EE->session->userdata('time_format') : $this->EE->config->item('time_format');
    			
                if ($date_fmt == 'us')
    			{
    				$datestr = '%m/%d/%y %h:%i %a';
    			}
    			else
    			{
    				$datestr = '%Y-%m-%d %H:%i';
    			}
                $set['string_override'] = '<p>'.$data["$provider"]->post.' <a href="'.$data["$provider"]->url.'">'.$data["$provider"]->url.'</a></p><p><em>'.lang('sent_on').$this->EE->localize->decode_date($datestr, $data["$provider"]->post_date, TRUE).'</em></p>';
                
            }
            
        }

		return $tab_settings;
	}


	function validate_publish($params)
	{
		return FALSE;
	}
	
    
	function publish_data_db($params)
	{     
        return; 
        
        
		if (isset($params['mod_data']['social_update_url_base']))
        {
            $url_base = $params['mod_data']['social_update_url_base'];
        }
        else
        {
            $url_base = 'url_title';
        }

        if ($url_base=='manual')
        {
        	$url = '';
       	}
       	else
       	{
	        switch ($url_base)
	        {
	            case 'channel_url':
	                $this->EE->db->select('channel_url')
	                            ->from('channels')
	                            ->where('channel_id', $params['meta']['channel_id']);
	                $channel = $this->EE->db->get();
	                $url = $channel->row('channel_url');
	                break;
             	case 'site_url':
	                $url = $this->EE->config->item('site_url');
	                break;
				case 'pages':
	                $url = $this->EE->config->slash_item('site_url').trim($params['mod_data']['pages_uri'], '/');
	                break;
	            case 'structure':
        			/*
					$structure_uri = (isset($params['mod_data']['structure__uri']))?$params['mod_data']['structure__uri']:$params['mod_data']['uri'];
					$url = $this->EE->config->slash_item('site_url').trim($structure_uri, '/');*/
					require_once PATH_THIRD.'structure/mod.structure.php';
					require_once PATH_THIRD.'structure/sql.structure.php';
					$this->structure = new Structure();
					$this->structure_sql = new Sql_structure();
					$structure_channels = $this->structure->get_structure_channels();
					$channel_type = $structure_channels[$params['meta']['channel_id']]['type'];
					$site_pages = $this->structure_sql->get_site_pages();
					
					$structure_uri = (isset($params['mod_data']['structure__uri']) && $params['mod_data']['structure__uri']!='') ? trim($params['mod_data']['structure__uri']) : $params['meta']['url_title'];
					
					if ($channel_type == 'listing')
					{	
						if ((!isset($params['mod_data']['structure__uri']) || $params['mod_data']['structure__uri']=='') && $this->structure_sql->is_duplicate_listing_uri($structure_uri))
						{
							$separator = $this->EE->config->item('word_separator') != 'dash' ? '_' : '-';
							$structure_uri = $structure_uri.$separator.'1';
						}
					}	
					
					$structure_parent_id = array_key_exists('structure__parent_id', $params['mod_data']) ? $params['mod_data']['structure__parent_id'] : 0;	
					$structure_parent_uri = isset($site_pages['uris'][$structure_parent_id]) ? $site_pages['uris'][$structure_parent_id] : '/';
					$url = $this->EE->config->slash_item('site_url').$this->structure->create_page_uri($structure_parent_uri, $structure_uri);
						
	                break;     
	            case 'entry_id':
	                $this->EE->db->select('channel_url, comment_url')
	                            ->from('channels')
	                            ->where('channel_id', $params['meta']['channel_id']);
	                $channel = $this->EE->db->get();
	                $basepath = ($channel->row('comment_url')!='') ? $channel->row('comment_url') : $channel->row('channel_url');
	                $url = rtrim($basepath, '/').'/'.$params['entry_id'];
	                break;
	            case 'url_title':
	            default:
	                $this->EE->db->select('channel_url, comment_url')
	                            ->from('channels')
	                            ->where('channel_id', $params['meta']['channel_id']);
	                $channel = $this->EE->db->get();
	                $basepath = ($channel->row('comment_url')!='') ? $channel->row('comment_url') : $channel->row('channel_url');
	                $url = rtrim($basepath, '/').'/'.$params['meta']['url_title'];
	                break;
	        }
	        $url = $this->EE->functions->remove_double_slashes($url);
        }
        
        $trigger_statuses = (isset($this->settings['trigger_statuses'])?$this->settings['trigger_statuses']:array('open'));
        $status = $params['meta']['status'];
        if ($status=='')
        {
        	//better workflow compatibility
			foreach($_POST as $k => $v) 
			{
				if (preg_match('/^epBwfEntry/',$k))
				{
					$status = array_pop(explode('|',$v));
					break;
				}
			}
        }

        foreach ($this->providers as $provider)
        {
            $msg = $params['mod_data']['social_update_'.$provider];

            if ($msg!='')
            {
                $data = array(
                        'site_id' => $this->EE->config->item('site_id'),
                        'channel_id' => $params['meta']['channel_id'],
                        'entry_id' => $params['entry_id'],
                        'service' => $provider,
                        'post' => $msg
                        );
                         
                if (in_array($status, $trigger_statuses) && $url_base!='manual')
                {
                    if ($url!='' && strlen($msg." ".$url) > $this->maxlen[$provider])
                    {
                        if ( ! class_exists('Shorteen'))
                    	{
                    		require_once PATH_THIRD.'shorteen/mod.shorteen.php';
                    	}
                    	
                    	$SHORTEEN = new Shorteen();
                        
                        $shorturl = $SHORTEEN->process($this->settings['url_shortening_service'], $url, true);
                        if ($shorturl!='')
                        {
                            $url = $shorturl;
                        }
                    }
                    //still too long? truncate the message
                    //at least one URL should always be included
                    if (strlen($msg." ".$url) > $this->maxlen[$provider])
                    {
                        $msg = $this->_char_limit($msg, ($this->maxlen[$provider]-strlen($url)-1));
                        $data['post'] = $msg;
                    }
                    $data['url'] = $url;                  
                }     
                else
                {
                	$data['post'] = $msg;
                    $data['url'] = '';  
                }

                $this->EE->db->select('post_id, post_date')
                        ->from('exp_social_update_posts')
                        ->where('entry_id', $params['entry_id'])
                        ->where('service', $provider)
                        ->limit(1);
                $q = $this->EE->db->get();

                if ($q->num_rows()==0)
                {
                    $this->EE->db->insert('exp_social_update_posts', $data);
                    $post_id = $this->EE->db->insert_id();
                }
                else if ($q->row('post_date')==0)
                {
                    $this->EE->db->where('post_id', $q->row('post_id'));
                    $this->EE->db->update('exp_social_update_posts', $data);
                    $post_id = $q->row('post_id');
                }

                if (in_array($status, $trigger_statuses))
                {    
                    //all is ready! post the message
                    $lib = $provider.'_oauth';
                    $post_params = array('key'=>$this->settings['app_id']["$provider"], 'secret'=>$this->settings['app_secret']["$provider"]);
                    $this->EE->load->library($lib, $post_params);
                    if ($provider=='facebook')
                    {
                        $all_tokens = unserialize($this->settings['all_tokens']["$provider"]);
                        $usertoken = '';
                        if (!empty($all_tokens))
                        {
                            $usertoken = $all_tokens[$this->settings['username']["$provider"]];
                        }
                        $remote_post = $this->EE->$lib->post($url, $msg, $this->settings['token']["$provider"], $this->settings['token_secret']["$provider"], $this->settings['username']["$provider"], $usertoken); 
                    }
                    else
                    {
                        $remote_post = $this->EE->$lib->post($msg." ".$url, $this->settings['token']["$provider"], $this->settings['token_secret']["$provider"]); 
                    }
                    
                    if (!empty($remote_post) && $remote_post['remote_user']!='' && $remote_post['remote_post_id']!='')
                    {
                    	$remote_post['post_date'] = $this->EE->localize->now;
						$this->EE->db->where('post_id', $post_id);
                    	$this->EE->db->update('exp_social_update_posts', $remote_post);
                    }

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
    

	function publish_data_delete_db($params)
	{
		//do nothing :)
	}

}
/* END Class */
