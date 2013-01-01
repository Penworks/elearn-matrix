<?php

/*
=====================================================
 Shorteen
-----------------------------------------------------
 http://www.intoeetive.com/
-----------------------------------------------------
 Copyright (c) 2012 Yuri Salimovskiy
 Lessn More support by Jerome Brown
=====================================================
 This software is based upon and derived from
 ExpressionEngine software protected under
 copyright dated 2004 - 2012. Please see
 http://expressionengine.com/docs/license.html
=====================================================
 File: mcp.shorteen.php
-----------------------------------------------------
 Purpose: Shorten your URLs using wide range of shortening services
=====================================================
*/

if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}



class Shorteen_mcp {

    var $version = '0.3.1';
    
    var $settings = array();
    
    var $docs_url = "http://www.intoeetive.com/docs/shorteen.html";
    
    public $providers = array(
                        'googl'=>array(
                            'api_key'
                        ),
                        'bitly'=>array(
                            'login',
                            'api_key'
                        ),
                        'yourls'=>array(
                            'signature',
                            'install_url'
                        ),
                        'lessn-more'=>array(
                            'api_key',
                            'install_url'
                        )
                    );
    
    function __construct() { 
        // Make a local reference to the ExpressionEngine super object 
        $this->EE =& get_instance(); 
        $query = $this->EE->db->query("SELECT settings FROM exp_modules WHERE module_name='Shorteen' LIMIT 1");
        $this->settings = unserialize($query->row('settings')); 
    } 
    
    function index()
    {
        $this->EE->load->helper('form');
    	$this->EE->load->library('table');  
        $this->EE->load->library('javascript');
        
        $outputjs = "
            $(\".editAccordion\").css(\"borderTop\", $(\".editAccordion\").css(\"borderBottom\")); 
            $(\".editAccordion h3\").click(function() {
                if ($(this).hasClass(\"collapsed\")) { 
                    $(this).siblings().slideDown(\"fast\"); 
                    $(this).removeClass(\"collapsed\").parent().removeClass(\"collapsed\"); 
                } else { 
                    $(this).siblings().slideUp(\"fast\"); 
                    $(this).addClass(\"collapsed\").parent().addClass(\"collapsed\"); 
                }
            }); 
        ";    
        
        $providers_view = '';
        $providers = array();
        
        foreach ($this->providers as $provider_name=>$provider_fields)
        {
            $data['name'] = lang($provider_name);
            $data['fields'] = array();
            
            foreach ($provider_fields as $field)
            {
                $data['fields'][] = array(
                                        'label'=>lang($field),
                                        'field'=>form_input($field."[$provider_name]", (isset($this->settings[$provider_name][$field])?$this->settings[$provider_name][$field]:''), 'style="width: 80%"')
                                    );
            }
            
            $providers_view .= $this->EE->load->view('provider', $data, TRUE);
        }
        
        $vars = array();
        $vars['providers'] = $providers_view;
        
        $this->EE->javascript->output(str_replace(array("\n", "\t"), '', $outputjs));
        
        $this->EE->cp->set_variable('cp_page_title', lang('shorteen_module_name'));
        
    	return $this->EE->load->view('settings', $vars, TRUE);
        
    }

    
    function save_settings()
    {

        $site_id = $this->EE->config->item('site_id');

        foreach ($this->providers as $provider_name=>$provider_fields)
        {
            foreach ($provider_fields as $field)
            {
                $settings[$provider_name][$field] = $_POST["$field"]["$provider_name"];
            }
        }
        
        $this->EE->db->where('module_name', 'Shorteen');
        $this->EE->db->update('modules', array('settings' => serialize($settings)));
        
        $this->EE->session->set_flashdata('message_success', $this->EE->lang->line('updated'));        
        $this->EE->functions->redirect(BASE.AMP.'C=addons_modules');
        
    }    
   
    

}
/* END */
?>