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
 File: mod.shorteen.php
-----------------------------------------------------
 Purpose: Shorten your URLs using wide range of shortening services
=====================================================
*/


if ( ! defined('BASEPATH'))
{
    exit('Invalid file request');
}


class Shorteen {

    var $return_data	= ''; 						// Bah!
    
    var $settings = array();

    /** ----------------------------------------
    /**  Constructor
    /** ----------------------------------------*/

    function __construct()
    {        
    	$this->EE =& get_instance(); 
        $query = $this->EE->db->query("SELECT settings FROM exp_modules WHERE module_name='Shorteen' LIMIT 1");
        $this->settings = unserialize($query->row('settings')); 
    }
    /* END */
    
    
    
    function process($service='', $url='', $embedded=false)
    {
        if (isset($this->EE->TMPL))
        {
            $service = $this->EE->TMPL->fetch_param('service');
            $url = $this->EE->TMPL->parse_globals($this->EE->TMPL->fetch_param('url'));
            if ($url=='') $url = $this->EE->functions->fetch_current_uri();
        }
        if ($this->EE->input->get('service')!='') $service = $this->EE->input->get('service');
        if ($this->EE->input->get('url')!='') $url = urldecode($this->EE->input->get('url'));
        if ($service=='') $service='googl';
        if ($url=='') return false;
        
        //check whether shorturl is already in DB
        $this->EE->db->select('id, shorturl, created')
                    ->from('shorteen')
                    ->where('service', $service)
                    ->where('url', $url);
        $q = $this->EE->db->get();
        if ($q->num_rows()>0)
        {
            foreach ($q->result_array() as $row)
            {
                $monthago = $this->EE->localize->now - 30*24*60*60;
                if ($row['created']>$monthago)
                {
                    $shorturl = $row['shorturl'];
                }
                else
                {
                    $this->EE->db->delete('shorteen', array('id'=>$row['id']));
                }
            }
            if (isset($shorturl)) 
            {
                if (isset($this->EE->TMPL) || $embedded==true)
                {
                    return $shorturl;
                }
                else
                {
                    error_reporting(0);
                    echo $shorturl;
                    return;
                }
            }
        }

        $url = urlencode($url);
        switch ($service)
        {
            case 'googl':
                $req_url = 'https://www.googleapis.com/urlshortener/v1/url';
                if (isset($this->settings['googl']['api_key']) && $this->settings['googl']['api_key']!='')
                {
                    $req_url .= "?key=".$this->settings['googl']['api_key'];
                }
                $req_type = 'POST';
                $req_ctype = 'json';
                $data_string = '{"longUrl": "'.urldecode($url).'"}';
                break;
            case 'isgd':
                $req_url = 'http://is.gd/create.php?format=simple&url='.$url;
                $req_type = 'GET';
                $req_ctype = 'simple';
                $data_string = '';
                break;
            case 'bitly':
                $req_url = 'http://api.bitly.com/v3/shorten?format=txt&login='.$this->settings['bitly']['login'].'&apiKey='.$this->settings['bitly']['api_key'].'&longUrl='.$url;
                $req_type = 'GET';
                $req_ctype = 'simple';
                $data_string = '';
                break;
            case 'yourls':
                $domain = rtrim($this->settings['yourls']['install_url'], '/');
                if (strpos($domain, 'http')===FALSE) { $domain = 'http://'.$domain; }
                $req_url = $domain.'/yourls-api.php?format=simple&action=shorturl&signature='.$this->settings['yourls']['signature'].'&url='.$url;
                $req_type = 'GET';
                $req_ctype = 'simple';
                $data_string = '';
                break;
            case 'lessn-more':
                $domain = rtrim($this->settings['lessn-more']['install_url'], '/');
                if (strpos($domain, 'http')===FALSE) { $domain = 'http://'.$domain; }
                if (strpos($url, urlencode($domain))===FALSE) {
                  $req_url = $domain.'/-/?api='.$this->settings['lessn-more']['api_key'].'&url='.$url;
                  $req_type = 'GET';
                  $req_ctype = 'simple';
                  $data_string = '';
                } else {
                  // we are trying to shorten the URL again.
                  return urldecode($url);
                }
                break;
        }
        $url = urldecode($url);

        $ch = curl_init($req_url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_SSLVERSION,3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($req_type=='POST')
        {
            curl_setopt($ch,CURLOPT_POST,true);
            curl_setopt($ch,CURLOPT_POSTFIELDS,$data_string);
        }
        if ($req_ctype == 'json')
        {
            curl_setopt($ch,CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        }
		$response = curl_exec($ch);
        $error = curl_error($ch);

        if ($error!='')
        {
            return $url;
        }
        
        if ($req_ctype == 'json')
        {
            if (function_exists('json_decode'))
            {
                $rawdata = json_decode($response);
            }
            else
            {
                require_once($path_third.'shorteen/inc/JSON.php');
                $json = new Services_JSON();
                $rawdata = $json->decode($response);
            }
        }
        curl_close($ch);

        $shorturl = '';
        switch ($service)
        {
            case 'googl':
                $shorturl = $rawdata->id;
                break;
            default:
                $shorturl = $response;
                break;
        }
        
        if ($shorturl == '') $shorturl = $url;
        
        $data = array(
                    'service'=>$service,
                    'url'=>$url,
                    'shorturl'=>$shorturl,
                    'created'=>$this->EE->localize->now
                    );
        $this->EE->db->insert('shorteen', $data);
        
        if (isset($this->EE->TMPL) || $embedded==true)
        {
            return $shorturl;
        }
        else
        {
            error_reporting(0);
            echo $shorturl;
        }
    }



}
/* END */
?>