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

class Social_update_mcp {

    var $version = SOCIAL_UPDATE_ADDON_VERSION;
    
    var $settings = array();
    
    var $providers = array('twitter', 'facebook', 'linkedin');
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
        $this->EE->lang->loadfile('content');
        $this->EE->lang->loadfile('shorteen');
        $this->EE->lang->loadfile('social_update');
		
    } 
    
    
    function index()
    {
    	$this->EE->load->helper('form');
        $this->EE->load->library('table');  
        $this->EE->load->library('javascript');

        @session_start();
        
        $providers_view = '';
        
		$this->EE->db->select('settings')
                    ->where('site_id', $this->EE->config->item('site_id'))
                    ->limit(1);
        $query = $this->EE->db->get('social_update_settings');
		if ($query->num_rows()>0)
		{
			$this->settings = unserialize($query->row('settings'));
		}

        foreach ($this->providers as $provider)
        {
            $data['name'] = lang($provider);
            $data['provider'] = $provider;

            $data['fields'] = array(	
                0 => array(
                        'label'=>lang($provider.'_app_id'),
                        'field'=>form_input("app_id[$provider]", (isset($this->settings['app_id'][$provider]) && $this->settings['app_id'][$provider]!='')?$this->settings['app_id'][$provider]:((isset($_SESSION['social_update'][$provider]['app_id']))?$_SESSION['social_update'][$provider]['app_id']:''), 'id="'.$provider.'_app_id" style="width: 80%"')
                    ),
                1 => array(
                        'label'=>lang($provider.'_app_secret'),
                        'field'=>form_input("app_secret[$provider]", (isset($this->settings['app_secret'][$provider]) && $this->settings['app_secret'][$provider]!='')?$this->settings['app_secret'][$provider]:((isset($_SESSION['social_update'][$provider]['app_secret']))?$_SESSION['social_update'][$provider]['app_secret']:''), 'id="'.$provider.'_app_secret" style="width: 80%"')
                    )
            );
            
            $data['fields'][2] = array(
                        'label'=>lang('token'),
                        'field'=>form_hidden("token[$provider]", (isset($this->settings['token'][$provider]) && $this->settings['token'][$provider]!='')?$this->settings['token'][$provider]:((isset($_SESSION['social_update'][$provider]['oauth_token']))?$_SESSION['social_update'][$provider]['oauth_token']:''), 'id="'.$provider.'_token"')
            );
            $data['fields'][2]['field'] .= (isset($this->settings['token'][$provider]) && $this->settings['token'][$provider]!='')?$this->settings['token'][$provider]:((isset($_SESSION['social_update'][$provider]['oauth_token']))?$_SESSION['social_update'][$provider]['oauth_token']:'');
            
            if ($provider!='facebook')
            {
                $data['fields'][3] = array(
                        'label'=>lang('token_secret'),
                        'field'=>form_hidden("token_secret[$provider]", (isset($this->settings['token_secret'][$provider]) && $this->settings['token_secret'][$provider]!='')?$this->settings['token_secret'][$provider]:((isset($_SESSION['social_update'][$provider]['oauth_token_secret']))?$_SESSION['social_update'][$provider]['oauth_token_secret']:''), 'id="'.$provider.'_token_secret"')
                    );
                $data['fields'][3]['field'] .= (isset($this->settings['token_secret'][$provider]) && $this->settings['token_secret'][$provider]!='')?$this->settings['token_secret'][$provider]:((isset($_SESSION['social_update'][$provider]['oauth_token_secret']))?$_SESSION['social_update'][$provider]['oauth_token_secret']:'');
            }
            
            $display_name = '';
            if (isset($this->settings['username'][$provider]) && $this->settings['username'][$provider]!='')
            {
                $this->EE->db->select('display_name')
                            ->from('exp_social_update_accounts')
                            ->where('service', $provider)
                            ->where('userid', $this->settings['username'][$provider])
                            ->limit(1);
                $display_name_q = $this->EE->db->get();
                if ($display_name_q->num_rows()>0)
                {
                    $display_name = $display_name_q->row('display_name');
                }
            }
            
            if ($provider=='twitter')
            {
                if (isset($_SESSION['social_update'][$provider]['username']))
                {
                    $data['fields'][] = array(
                        'label'=>lang('post_to'),
                        'field'=>form_hidden("username[$provider]", $_SESSION['social_update'][$provider]['username'])
                    );
                }
                else
                {
                    $data['fields'][] = array(
                            'label'=>lang('post_to'),
                            'field'=>($display_name!='')?'<a href="http://twitter.com/#!/'.$display_name.'" target="_blank">@'.$display_name.'</a>'.form_hidden("username[$provider]", $this->settings['username'][$provider]):''
                        );
                }
            }
            else if ($provider=='facebook')
            {
                if (isset($_SESSION['social_update'][$provider]['username']))
                {
                    $data['fields'][] = array(
                        'label'=>lang('post_to'),
                        'field'=>form_dropdown("username[$provider]", $_SESSION['social_update'][$provider]['username'])
                    );
                    if (count($_SESSION['social_update'][$provider]['username'])>1)
                    {
                        $data['fields'][] = array(
                            'label'=>lang('post_as_page'),
                            'field'=>form_checkbox("post_as_page[$provider]", 'y', false)
                        );
                    }
                }
                else if (isset($this->settings['username'][$provider]) && $this->settings['username'][$provider]!='')
                {
                    if (isset($this->settings['post_as_page'][$provider]) && $this->settings['post_as_page'][$provider]=='y')
                    {
                        $display_name .= ' - '.lang('post_as_page');
                    }
                    $data['fields'][] = array(
                        'label'=>lang('post_to'),
                        'field'=>'<a href="http://www.facebook.com/profile.php?id='.$this->settings['username'][$provider].'" target="_blank">'.$display_name.'</a>'.form_hidden("username[$provider]", $this->settings['username'][$provider]).form_hidden("post_as_page[$provider]", @$this->settings['post_as_page'][$provider])
                    );
                }
                else
                {
                    $data['fields'][] = array(
                        'label'=>lang('post_to'),
                        'field'=>''
                    );
                }
            }

            $providers_view .= $this->EE->load->view('provider', $data, TRUE);
        }

        $vars = array();
        $vars['providers'] = $providers_view;
        
        $this->EE->load->model('status_model');
        $query = $this->EE->status_model->get_statuses();
		
		$statuses = array();
		$statuses['open'] = lang('open');
		$statuses['closed'] = lang('closed');

		if ($query->num_rows() > 0)
		{
			foreach ($query->result() as $row)
			{
				$status_name = ($row->status == 'open' OR $row->status == 'closed') ? lang($row->status) : $row->status;
				$statuses[$row->status] = $status_name;
			}
		}
		
		$selected_statuses = (isset($this->settings['trigger_statuses'])?$this->settings['trigger_statuses']:array('open'));

		$vars['settings']['trigger_statuses']	= '';
		foreach ($statuses as $status=>$lang)
		{
			$vars['settings']['trigger_statuses'] .= form_checkbox('trigger_statuses[]', $status, in_array($status, $selected_statuses)).NBS.NBS.$lang.BR.BR;
		}
        
        $vars['settings']['disable_javascript'] = form_checkbox('disable_javascript', 'y', ((isset($this->settings['disable_javascript']) &&  $this->settings['disable_javascript']=='y')?true:false));
        
        $url_shortening_services = array(
                                    'googl'=>lang('googl'),
                                    'isgd'=>lang('isgd'),
                                    'bitly'=>lang('bitly'),
                                    'yourls'=>lang('yourls'),
                                    'lessn-more'=>lang('lessn-more')
                                );
        
        $vars['settings']['url_shortening_service']	= form_dropdown('url_shortening_service', $url_shortening_services, (isset($this->settings['url_shortening_service'])?$this->settings['url_shortening_service']:'googl'));
        
        $act = $this->EE->db->query("SELECT action_id FROM exp_actions WHERE class='Shorteen' AND method='process'");
        $shotren_url = trim($this->EE->config->item('site_url'), '/').'/?ACT='.$act->row('action_id');
        $outputjs = "
            ts = new Date();
            $('.shortening_reveal').click(function(){
                $('#shorturl').html('');
                $('#shortening_test_table').toggle('slow');
                return false;
            });
            $('#test_shortening').click(function(){
                $('#shorturl').html('<img src=\"".$this->EE->config->item('theme_folder_url')."/cp_global_images/indicator.gif\" alt=\"please wait\" />');
                $.get('$shotren_url', {
                        'service'   : $('select[name=url_shortening_service]').val(),
                        'url'       : encodeURIComponent($('input[name=long_url]').val()),
                        'ts'        : ts.getTime()
                    }, function(msg) {
                        $('#shorturl').html('<a href=\"'+msg+'\">'+msg+'</a>');
                    }
                );
                return false;
            });
        ";
        $vars['shortening_test_table'] = array(
                                    lang('long_url').' '.form_input('long_url', $this->EE->config->item('site_url'), 'style="width: 100%"'),
                                    '<div id="shorturl" style="width: 10em"></div>',
                                    '<a href="#" class="submit" id="test_shortening">'.lang('test_shortening').'</a>'
        );
        
        $this->EE->javascript->output(str_replace(array("\n", "\t"), '', $outputjs));
        
        
        
        $this->EE->cp->set_variable('cp_page_title', lang('social_update_module_name'));
        
    	return $this->EE->load->view('settings', $vars, TRUE);	
    }
    
    
    
    
    function save_settings()
    {
    	if (empty($_POST))
    	{
    		show_error($this->EE->lang->line('unauthorized_access'));
    	}
        
        @session_start();
    	
    	unset($_POST['submit']);
        
        foreach ($this->providers as $provider)
        {
            if ($_POST["app_id"]["$provider"]=="")
            {
                unset($_POST["app_secret"]["$provider"]);
                unset($_POST["token"]["$provider"]);
                unset($_POST["token_secret"]["$provider"]);
                unset($_POST["username"]["$provider"]);
                unset($_POST["post_as_page"]["$provider"]);
            }
            else
            {
                if (isset($_POST["post_as_page"]["$provider"]) && $_POST["post_as_page"]["$provider"]=='y' && isset($_SESSION['social_update']))
                {
                    $all_tokens = $_SESSION['social_update'][$provider]['all_tokens'];
                    if (!empty($all_tokens) && isset($all_tokens[$_POST["username"]["$provider"]]) && $all_tokens[$_POST["username"]["$provider"]]!='')
                    {
                        $_POST["token"]["$provider"] = $all_tokens[$_POST["username"]["$provider"]];
                    }
                }
            }
        }
		
		$data = array(
			'site_id'	=> $this->EE->config->item('site_id'),
			'settings'	=> serialize($_POST)
		);

    	$this->EE->db->replace('social_update_settings', $data);
    	
    	$this->EE->session->set_flashdata(
    		'message_success',
    	 	$this->EE->lang->line('preferences_updated')
    	);
        
        unset($_SESSION['social_update']);
        
        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=social_update'.AMP.'method=index');
    } 
    
    
    function request_token($provider='')
    {        
        
        if ($provider=='')
        {
            $provider = $this->EE->input->get_post('provider');
        }
        
        if ($provider=='')
        {
            show_error(lang('no_service_provider'));
            return;
        }
        
        if (!file_exists(PATH_THIRD.'social_update/libraries/'.$provider.'_oauth.php'))
        {
            show_error(lang('provider_file_missing'));
            return;
        }

        //if one of the settings is empty, we can't proceed
        if ($this->EE->input->get_post('app_id')=='' || $this->EE->input->get_post('app_secret')=='')
        {
            show_error(lang('please_provide_settings_for').' '.lang("$provider"));
            return;
        }
        
        $params = array('key'=>$this->EE->input->get_post('app_id'), 'secret'=>$this->EE->input->get_post('app_secret'));

        $lib = $provider.'_oauth';

        $this->EE->load->library($lib, $params);
        
        @session_start();
        unset($_SESSION['social_update']);
        $_SESSION['social_update']['provider'] = $provider;
        $_SESSION['social_update'][$provider]['app_id'] = $this->EE->input->get_post('app_id');
        $_SESSION['social_update'][$provider]['app_secret'] = $this->EE->input->get_post('app_secret');

        $access_token_url = $this->EE->config->item('cp_url').'?D=cp&C=addons_modules&M=show_module_cp&module=social_update&method=access_token';
        $response = $this->EE->$lib->get_request_token($access_token_url);
        
        $_SESSION['social_update'][$provider]['token_secret'] = $response['token_secret'];

        return $this->EE->functions->redirect($response['redirect']);
    }
       
       
       
        
    function access_token()
    {
        @session_start();
        
        $this->EE->load->helper('url');
        
        $provider = $_SESSION['social_update']['provider'];
        $lib = $provider.'_oauth';
        $params = array('key'=>$_SESSION['social_update'][$provider]['app_id'], 'secret'=>$_SESSION['social_update'][$provider]['app_secret']);
                
        $this->EE->load->library($lib, $params);
        if ($provider=='facebook')
        {
            $access_token_url = $this->EE->config->item('cp_url').'?D=cp&C=addons_modules&M=show_module_cp&module=social_update&method=access_token';
            $response = $this->EE->$lib->get_access_token($access_token_url, $this->EE->input->get('code'));
            $_SESSION['social_update'][$provider]['oauth_token'] = $response['access_token'];
            $_SESSION['social_update'][$provider]['username'] = $response['pages'];
            $_SESSION['social_update'][$provider]['all_tokens'] = $response['tokens'];
            $display_names = $response['pages'];
        }
        else if ($provider=='twitter')
        {
            $response = $this->EE->$lib->get_access_token(false, $_SESSION['social_update'][$provider]['token_secret']);
            $_SESSION['social_update'][$provider]['oauth_token'] = $response['oauth_token'];
            $_SESSION['social_update'][$provider]['oauth_token_secret'] = $response['oauth_token_secret'];
            $_SESSION['social_update'][$provider]['username'] = $response['screen_name'];
            $_SESSION['social_update'][$provider]['all_tokens'] = array($response['screen_name']=>$response['oauth_token']);
            $display_names = array($response['screen_name']=>$response['screen_name']);
        }
        else
        {
			$response = $this->EE->$lib->get_access_token(false, $_SESSION['social_update'][$provider]['token_secret']);
            $_SESSION['social_update'][$provider]['oauth_token'] = $response['oauth_token'];
            $_SESSION['social_update'][$provider]['oauth_token_secret'] = $response['oauth_token_secret'];
            $_SESSION['social_update'][$provider]['username'] = $response['screen_name'];
            $_SESSION['social_update'][$provider]['all_tokens'] = array($response['screen_name']=>$response['oauth_token']);
            $display_names = array($response['screen_name']=>$response['screen_name']);
        }
        
        foreach ($display_names as $userid=>$display_name)
        {
            if ($display_name!='')
            {
				$data = array(
	                'service'      => $provider,
	                'userid'       => $userid,
	                'display_name' => $display_name
	            );
	            $sql = $this->EE->db->insert_string('exp_social_update_accounts', $data);
	            $sql .= " ON DUPLICATE KEY UPDATE display_name='".$this->EE->db->escape_str($display_name)."'";
	            $this->EE->db->query($sql);
    		}
        }

        return $this->EE->functions->redirect(BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=social_update'.AMP.'method=index');

    }  
  
  

}
/* END */
?>