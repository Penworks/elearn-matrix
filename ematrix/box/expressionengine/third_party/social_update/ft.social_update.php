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
 File: ft.social_update.php
-----------------------------------------------------
 Purpose: Send updates to social networks upon entry publishing
=====================================================
*/


if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}

require PATH_THIRD.'social_update/config.php';

class Social_update_ft extends EE_Fieldtype {
	
	var $info = array(
		'name'		=> SOCIAL_UPDATE_ADDON_NAME,
		'version'	=> SOCIAL_UPDATE_ADDON_VERSION
	);
	
	var $maxlen 	= array(
                        'twitter'  => 140,
                        'facebook' => 420,
                        'linkedin' => 700
                    );
	
	var $module_settings = array();
	
	var $has_array_data = true;
	
	// --------------------------------------------------------------------
	public function __construct($params = array())
	{
		$this->EE =& get_instance();
		if ($this->EE->db->table_exists('social_update_settings'))
		{
			$query = $this->EE->db->select('settings')
							->from('social_update_settings')
		                    ->where('site_id', $this->EE->config->item('site_id'))
		                    ->limit(1)
							->get();
			if ($query->num_rows()>0)
			{
				$this->module_settings = unserialize($query->row('settings'));
			}
		}
		$this->EE->lang->loadfile('social_update');  
	}
	

	
	
	function _prepare_settings($data)
    {
		
		/*if (empty($this->module_settings))
		{
			show_error(lang('provide_module_settings'));
		}*/
		
		$data['providers'] = array();
		foreach ($this->module_settings as $setting_name=>$setting)
        {
            if (is_array($setting) && $setting_name!='trigger_statuses')
			{
				if ($setting["provider"]!='')
	            {
	            	if ($setting['username']!='')
	            	{
						$this->EE->db->select('display_name')
		                            ->from('exp_social_update_accounts')
		                            ->where('service', $setting['provider'])
		                            ->where('userid', $setting['username'])
		                            ->limit(1);
		                $display_name_q = $this->EE->db->get();
		                if ($display_name_q->num_rows()>0)
		                {
		                    $setting['username'] = $display_name_q->row('display_name');
		                }
      				}
					$data['providers']["$setting_name"] = $setting['username']." - ".lang($setting['provider']);
	            	if ($setting['post_as_page']) $data['providers']["$setting_name"] .= " (".lang('post_as_page').")";
	            }
    		}
        }
    
        $data["provider"] = (isset($data["provider"])) ? $data["provider"] : ''; 

        $data['url_options'] = array('url_title'=>lang('url_auto_url_title'), 'entry_id'=>lang('url_auto_entry_id'));
        $this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Pages')); 
        if ($query->num_rows()>0) 
		{
			$data['url_options']['pages'] = lang('url_auto_pages');
		}
        $this->EE->db->select('module_id'); 
        $query = $this->EE->db->get_where('modules', array('module_name' => 'Structure')); 
        if ($query->num_rows()>0) 
		{
			$data['url_options']['structure'] = lang('url_auto_structure');
		}
        $data['url_options']['channel_url'] = lang('channel_url');
        $data['url_options']['site_url'] = lang('site_url');
        $data['url_options']['manual'] = lang('url_manual');
        
		$data["url_type"] = (isset($data["url_type"])) ? $data["url_type"] : $this->module_settings['default_url_type']; 
		$data['show_url_field'] = (isset($data["show_url_field"]) && $data["show_url_field"]=='y') ? true:false; 
		
		return $data;
	
	}
		
		
	function display_settings($data)
	{
		
		$data = $this->_prepare_settings($data);
		
		$this->EE->table->add_row(
            lang("provider", "provider"), form_dropdown("provider", $data['providers'], $data["provider"])
            );
        
        $this->EE->table->add_row(
            lang("url_type", "url_type"), form_dropdown("url_type", $data['url_options'], $data["url_type"])
            );    
            
        $this->EE->table->add_row(
            lang("show_url_field", "show_url_field"), form_checkbox("show_url_field", 'y', $data['show_url_field'])
            );   
            
        $js = "
$('#ft_social_update select[name=url_type]').change(function(){
	if ($(this).val()=='manual') {
		$('#ft_social_update input[name=show_url_field]').attr('checked', true);
		$('#ft_social_update input[name=show_url_field]').attr('readonly', 'readonly');
	}	
	else
	{
		$('#ft_social_update input[name=show_url_field]').attr('readonly', false);
	}
});
if ($('#ft_social_update select[name=url_type]').val()=='manual')
{
	$('#ft_social_update input[name=show_url_field]').attr('readonly', 'readonly');
}
		";
		
		$this->EE->javascript->output($js);
            
    } 
	
	
	
	
	/**
	 * Display Field on Publish
	 *
	 * @access	public
	 * @param	existing data
	 * @return	field html
	 *
	 */
	function display_field($data)
	{

		$row = array(
			'post'	=> '',
			'url'	=> '',
			'post_date'	=> 0
		);
		if (!empty($data))
		{
			$q = $this->EE->db->select('service, post, url, post_date')
					->from('social_update_posts')
					->where('post_id', $data)
					->get();
			if ($q->num_rows()>0)
			{
				$row = $q->row_array();
			}
		}
		
		$name = (isset($this->cell_name)) ? $this->cell_name : $this->field_name;
		$disabled = ($row['post_date']!=0)?' disabled="disabled"':'';

		$field_id_attr = "field_id_".$this->field_id;
		if (isset($this->col_id)) $field_id_attr .= '_'.$this->col_id;
		
		$input = 	form_textarea($name, $row['post'], ' rows="2" id="'.$field_id_attr.'"'.$disabled);
		
		if ($row['post_date']==0 && !isset($this->col_id) && (!isset($this->module_settings['disable_javascript']) ||  $this->module_settings['disable_javascript']!='y'))
		{
	        $theme_folder_url = trim($this->EE->config->item('theme_folder_url'), '/').'/third_party/social_update/';
	        if (isset($row['service']))
	        {
	        	$service = $row['service'];
	        }
	        else
	        {
	        	$service = $this->module_settings[$this->settings['provider']]['provider'];
	        }
			$this->EE->cp->add_to_foot('<link type="text/css" href="'.$theme_folder_url.'jquery.maxlength.css" rel="stylesheet" />');
	        $this->EE->cp->add_to_foot('<script type="text/javascript" src="'.$theme_folder_url.'jquery.maxlength.min.js"></script>');
	
	        $js = "
$('#".$field_id_attr."').maxlength({ 
    max: ".$this->maxlen[$service].",
    truncate: true,
    showFeedback: true,
    feedbackTarget: '#chars_avail_".$field_id_attr."',
    feedbackText: '{r}'
});  
        ";
        	$char_counter = str_replace('<span id="total">120</span>', '<span class="total_chars" id="chars_total_'.$field_id_attr.'">'.$this->maxlen[$service].'</span>', lang('char_counter'));
        	$char_counter = str_replace('<span id="avail">120</span>', '<span class="avail_chars" id="chars_avail_'.$field_id_attr.'">'.$this->maxlen[$service].'</span>', $char_counter);
        	$input .= '<p>'.$char_counter.'</p>';
        	

	        $this->EE->javascript->output($js);
	        
	        
		}

		
		
		if ((isset($this->settings['show_url_field'])) && $this->settings['show_url_field']=='y')
		{
        	$label = lang('url').NBS;
        	if ($this->settings['url_type']!='manual')
        	{
        		$label .= lang('url_override');
        	}
			$input .=         
					BR.BR.
					form_label($label, $name.'_social_update_url').
					form_input($name.'_social_update_url', $row['url'], 'id="'.$name.'_social_update_url"'.$disabled)
					;
		}
		
		if ($row['post_date']!=0)
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
			$input .= '<p><em>'.lang('sent_on').$this->EE->localize->decode_date($datestr, $row['post_date'], TRUE).'</em></p>';
		}
		
		return $input;
        
	}
	
	// --------------------------------------------------------------------
		
	/**
	 * Replace tag
	 *
	 * @access	public
	 * @param	field contents
	 * @return	replacement text
	 *
	 */
	function replace_tag($data, $params = array(), $tagdata = FALSE)
	{
        //display associated social post
        $this->EE->load->library('typography');
        $this->EE->typography->initialize();

        if ($data=='')
        {
        	return '';
        }
		
		$q = $this->EE->db->select()
                        ->from('social_update_posts')
                        ->where('post_id', $data)
						->get();
  		if ($q->num_rows()==0)
  		{
  			return '';
  		}
		
		$vars = array();
		foreach ($q->result_array() as $row)
		{

			$row['post'] = $this->EE->typography->parse_type($row['post']);
	        //conditional whether is posted or not
	        $row['posted'] = ($row['post_date']!=0) ? true : false;
	        switch ($row['service'])
	        {
	        	case 'twitter':
	        		$row['post_link'] = 'http://twitter.com/#!/'.$row['remote_user'].'/status/'.$row['remote_post_id'];
    				break;
    			case 'facebook':
    				$row['post_link'] = 'http://www.facebook.com/'.$row['remote_user'].'/posts/'.$row['remote_post_id'];
    				break;	
   				case 'linkedin':
    				$row['post_link'] = 'http://www.linkedin.com/profile/view?id='.$row['remote_user'];
    				break;
			}
	        $vars[] = $row;
		}
        
        $tagdata = $this->EE->TMPL->parse_variables($tagdata, $vars);
 
        return $tagdata;
	}
	
	
	function validate($data)
	{
		//just cache the data here, as we need entry ID
		$name = (isset($this->cell_name)) ? $this->cell_name : $this->field_name;

		if ($data!='')
		{
			$this->EE->session->cache['social_update'][$name] = $data;
			$this->EE->session->cache['social_update'][$name.'_override_url'] = $this->EE->input->post($name.'_social_update_url');
		}

		unset($_POST[$name.'_social_update_url']);
		unset($_POST[$name]);				

		return TRUE;
	}
    
    function save($data)
	{				
		return '';
	}
	
	
	function post_save($data)
	{
		$name = (isset($this->cell_name)) ? $this->cell_name : $this->field_name;
		
		$entry_id = $this->settings['entry_id'];
		$data = (isset($this->EE->session->cache['social_update'][$name]))?$this->EE->session->cache['social_update'][$name]:'';

		//if field is empty, return
		
		if ($data=='' && !isset($this->cell_name))
		{
			return;
		}
		
		$service = $this->module_settings[$this->settings['provider']]['provider'];
		
		$q = $this->EE->db->select('entry_id, url_title, channel_titles.channel_id, entry_date, status, channel_url, comment_url')
				->from('channel_titles')
				->join('channels', 'channel_titles.channel_id=channels.channel_id', 'left')
				->where('entry_id', $entry_id)
				->get();
		$entry_data = $q->row_array();
		
		//if this is a new entry, and submitted URL is no different that auto-generated?
		//if it's not 'manual' we should add url_title, entry_id, page_url etc. to it
		if (isset($this->EE->session->cache['social_update'][$name.'_override_url']) && $this->EE->session->cache['social_update'][$name.'_override_url']!='')
		{
			$url = $this->EE->session->cache['social_update'][$name.'_override_url'];
		}
		else
		{
			switch ($this->settings['url_type'])
			{
				case 'manual':
					$url = '';
					break;
				case 'site_url':
					$url = $this->EE->config->item('site_url');
					break;
				case 'channel_url':
					$url = $entry_data['channel_url'];
					break;
				case 'entry_id':
					$basepath = ($entry_data['comment_url']!='') ? $entry_data['comment_url'] : $entry_data['channel_url'];
					$url = $this->EE->functions->create_page_url($basepath, $entry_id);
					break;
				case 'pages':
				case 'structure':
					//$site_pages = $this->EE->config->item('site_pages');
					//$url = $site_pages[$this->EE->config->item('site_id')]['uris'][$entry_id];
					$url = '';
					break;
				case 'url_title':
				default:
					$basepath = ($entry_data['comment_url']!='') ? $entry_data['comment_url'] : $entry_data['channel_url'];
					$url = $this->EE->functions->create_page_url($basepath, $entry_data['url_title']);
					break;
			}
		}
		
		//is there an unposted record in database for this entry?
		$post_date = 0;		
		$this->EE->db->select('post_id, post_date')
				->from('social_update_posts')
				->where('entry_id', $entry_id)
				->where('field_id', $this->field_id);
		if (isset($this->col_id))
		{
			$this->EE->db->where('col_id', $this->col_id);
			$this->EE->db->where('row_id', $this->row_id);
		}
		$q = $this->EE->db->get();
		if ($q->num_rows()>0)
		{
			$post_id = $q->row('post_id');
			$post_date = $q->row('post_date');
			
			//if it has been not posted, update it
			if ($data!='')
			{
				$upd = array();
				$upd['post'] = $data;
				$upd['url'] = $url;
				
				$this->EE->db->where('post_id', $post_id);
				$this->EE->db->update('social_update_posts', $upd);
			}
			
		}
		else
		{
			if ($data!='')
			{
				//if no record, prepare new post
				$insert = array(
					'site_id'			=> $this->EE->config->item('site_id'),
					'channel_id'		=> $entry_data['channel_id'],
					'entry_id'			=> $entry_id,
					'field_id'			=> $this->field_id,
					'col_id'			=> (isset($this->col_id))?$this->col_id:0,
					'row_id'			=> (isset($this->row_id))?$this->row_id:0,
					'service'			=> $service,
					'post'				=> $data,
					'url'				=> $url
				);
				$this->EE->db->insert('social_update_posts', $insert);
				
				$post_id = $this->EE->db->insert_id();
			}
			
		}
	
		//update the field with post id
		if (isset($this->col_id) && isset($post_id))
		{
			//matrix is updated always, even if had record before (because is is reset)
			$this->EE->db->where('row_id', $this->row_id);
			$this->EE->db->where('entry_id', $entry_id);
			$this->EE->db->where('field_id', $this->field_id);
			$this->EE->db->update('matrix_data', array('col_id_'.$this->col_id => $post_id));	
		}
		elseif (isset($post_id))
		{
			//entry is updated only if not posted
			$this->EE->db->where('entry_id', $entry_id);
			$this->EE->db->update('channel_data', array('field_id_'.$this->field_id => $post_id));	
		}
		
		if ($post_date!=0)
		{
			//already posted, so we quit
			return;
		}
		
		if ($data=='')
		{
			return;
		}
		
		
		
		if (in_array($this->settings['url_type'], array('pages', 'structure')))
		{
			return;
		}
		
		if ($entry_data['status']=='')
        {
        	//better workflow compatibility
			foreach($_POST as $k => $v) 
			{
				if (preg_match('/^epBwfEntry/',$k))
				{
					$entry_data['status'] = array_pop(explode('|',$v));
					break;
				}
			}
        }
		
		//should we post? call function from library/extension, if needed
		if (in_array($entry_data['status'], $this->module_settings['trigger_statuses']) && $entry_data['entry_date']<=$this->EE->localize->now)
		{
			
			//shorten the stuff
			if ($url!='' && (strlen($data." ".$url) > $this->maxlen[$service] || $this->module_settings['force_url_shortening']=='y'))
	        {
	            if ( ! class_exists('Shorteen'))
	        	{
	        		require_once PATH_THIRD.'shorteen/mod.shorteen.php';
	        	}
	        	
	        	$SHORTEEN = new Shorteen();
	            
	            $shorturl = $SHORTEEN->process($this->module_settings['url_shortening_service'], $url, true);
	            if ($shorturl!='')
	            {
	                $url = $shorturl;
	            }
	        }
	        //still too long? truncate the message
	        //at least one URL should always be included
	        if (strlen($data." ".$url) > $this->maxlen[$service])
	        {
	            $data = $this->_char_limit($data, ($this->maxlen[$service]-strlen($url)-1));
	        }            
			
            $lib = $service.'_oauth';
            $post_params = array(
				'key'=>$this->module_settings[$this->settings['provider']]['app_id'], 
				'secret'=>$this->module_settings[$this->settings['provider']]['app_secret']
			);
			$this->EE->load->add_package_path(PATH_THIRD.'social_update/');
            $this->EE->load->library($lib, $post_params);
            if ($lib=='facebook_oauth')
            {
                $remote_post = $this->EE->$lib->post(
					$url, 
					$data, 
					$this->module_settings[$this->settings['provider']]['token'], 
					$this->module_settings[$this->settings['provider']]['token_secret'], 
					$this->module_settings[$this->settings['provider']]['username']
				); 
            }
            else
            {
                $remote_post = $this->EE->$lib->post(
					$data." ".$url, 
					$this->module_settings[$this->settings['provider']]['token'], 
					$this->module_settings[$this->settings['provider']]['token_secret']
				); 
            }
            $this->EE->load->remove_package_path(PATH_THIRD.'social_update/');
            
            if (!empty($remote_post) && $remote_post['remote_user']!='' && $remote_post['remote_post_id']!='')
            {
            	$upd = array(
					'service'			=> $this->module_settings[$this->settings['provider']]['provider'],
					'post_date'			=> $this->EE->localize->now,
					'remote_user'		=> $remote_post['remote_user'],
					'remote_post_id'	=> $remote_post['remote_post_id'],
				);
				
				$this->EE->db->where('post_id', $post_id);
				$this->EE->db->update('social_update_posts', $upd);

            }
			
		}

	}
    
    
    function save_settings($data) 
	{
        
		/*
		if ($this->EE->input->post('provider')=='')
        {
        	show_error(lang('select_provider'));
        }
        */
        return array(
	        'provider'  => $this->EE->input->post('provider'),
	        'url_type' => $this->EE->input->post('url_type'),
	        'show_url_field'      => $this->EE->input->post('show_url_field')
	    ); 
   }
   
   function post_save_settings($data)
   {
        $field_id = (isset($this->field_id) && $this->field_id!='') ? $this->field_id : $data['field_id'];
        $col_id = (isset($this->col_id) && $this->col_id!='') ? $this->col_id : (isset($data['col_id'])?$data['col_id']:'');

        //update existing orphan records
        if ($col_id=='') //only to non-matrix fields
        {
        $q = $this->EE->db->select('post_id, entry_id')
				->from('social_update_posts')
				->where('service', $this->module_settings[$this->EE->input->post('provider')]['provider'])
				->where('field_id', 0)
				->get();
		if ($q->num_rows()>0)
		{
			$where = array();
			foreach ($q->result_array() as $row)
			{
				$where[] = $row['post_id'];
				$this->EE->db->where('entry_id', $row['entry_id']);
				$this->EE->db->update('channel_data', array('field_id_'.$field_id => $row['post_id']));
			}
			$upd = array(
				'field_id'	=>	$field_id
			);
			$this->EE->db->where_in('post_id', $where);
			$this->EE->db->update('social_update_posts', $upd);
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
    
    
    
   	// ------------------------
	// P&T MATRIX SUPPORT
	// ------------------------
	
	/**
	 * Display Matrix field
	 */
	function display_cell($data) {
		return $this->display_field($data);
    }
	
    function display_cell_settings($data)
	{

	   $data = $this->_prepare_settings($data);
	   
	  /* $js = "
$('#ft_social_update select[name=url_type]').change(function(){
	if ($(this).val()=='manual') {
		$('#ft_social_update input[name=show_url_field]').attr('checked', true);
		$('#ft_social_update input[name=show_url_field]').attr('readonly', 'readonly');
	}	
	else
	{
		$('#ft_social_update input[name=show_url_field]').attr('readonly', false);
	}
});
if ($('#ft_social_update select[name=url_type]').val()=='manual')
{
	$('#ft_social_update input[name=show_url_field]').attr('readonly', 'readonly');
}
		";
		
		$this->EE->javascript->output($js);
		*/
		
		$data['url_options']['manual'] = lang('do_not_include_url');
	   return array(
	   		array(lang('provider'), form_dropdown("provider", $data['providers'], $data["provider"])),
	   		array(lang('url_type'), form_dropdown("url_type", $data['url_options'], $data["url_type"])),
	   		//array(lang('show_url_field'), form_checkbox("show_url_field", 'y', $data['show_url_field']))
	   );  
    }
    
    function save_cell_settings($data) {
		return $data;
	}
	
	function validate_cell($data)
	{
		$this->cell_name = $this->settings['row_name'].'_'.$this->settings['col_name'];
		return $this->validate($data);
	}
    
	function save_cell($data)
	{
		return $this->save($data);
	}
	
	function post_save_cell($data)
	{
		$this->cell_name = $this->settings['row_name'].'_'.$this->settings['col_name'];
		$this->col_id = $this->settings['col_id'];
		$this->field_id = $this->settings['field_id'];
		$this->col_name = $this->settings['col_name'];
		$this->row_id = $this->settings['row_id'];
		return $this->post_save($data);
	}
    
	// --------------------------------------------------------------------
	
	/**
	 * Install Fieldtype
	 *
	 * @access	public
	 * @return	default global settings
	 *
	 */
	function install()
	{
		return array();
	}
	

}
