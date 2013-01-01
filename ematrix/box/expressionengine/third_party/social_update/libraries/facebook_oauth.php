<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

error_reporting(0);

class facebook_oauth
{
    const SCHEME = 'https';
    const HOST = 'graph.facebook.com';
    const AUTHORIZE_URI = '/oauth/authorize';
    const REQUEST_URI   = '/oauth/request_token';
    const ACCESS_URI    = '/oauth/access_token';
    const USERINFO_URI    = '/me';
    
    //Array that should contain the consumer secret and
    //key which should be passed into the constructor.
    private $_consumer = false;
    
    /**
     * Pass in a parameters array which should look as follows:
     * array('key'=>'example.com', 'secret'=>'mysecret');
     * Note that the secret should either be a hash string for
     * HMAC signatures or a file path string for RSA signatures.
     *
     * @param array $params
     */
    public function facebook_oauth($params)
    {
        $this->CI = get_instance();
        $this->CI->load->helper('oauth');
                
        if(!array_key_exists('method', $params))$params['method'] = 'GET';
        if(!array_key_exists('algorithm', $params))$params['algorithm'] = OAUTH_ALGORITHMS::HMAC_SHA1;
        
        $this->_consumer = $params;
    }
    
    /**
     * This is called to begin the oauth token exchange. This should only
     * need to be called once for a user, provided they allow oauth access.
     * It will return a URL that your site should redirect to, allowing the
     * user to login and accept your application.
     *
     * @param string $callback the page on your site you wish to return to
     *                         after the user grants your application access.
     * @return mixed either the URL to redirect to, or if they specified HMAC
     *         signing an array with the token_secret and the redirect url
     */
    public function get_request_token($callback)
    {

        $redirect = self::SCHEME.'://'.self::HOST.self::AUTHORIZE_URI."?type=web_server&scope=publish_stream,manage_pages,offline_access&client_id=".$this->_consumer['key']."&redirect_uri=".urlencode($callback);

        header("Location: $redirect");
        exit();
        //If they are using HMAC then we need to return the token secret for them to store.
        /*if($this->_consumer['algorithm'] == OAUTH_ALGORITHMS::RSA_SHA1)return $redirect;
        else return array('token_secret'=>$resarray['oauth_token_secret'], 'redirect'=>$redirect);*/
    }
    
    /**
     * This is called to finish the oauth token exchange. This too should
     * only need to be called once for a user. The token returned should
     * be stored in your database for that particular user.
     *
     * @param string $token this is the oauth_token returned with your callback url
     * @param string $secret this is the token secret supplied from the request (Only required if using HMAC)
     * @param string $verifier this is the oauth_verifier returned with your callback url
     * @return array access token and token secret
     */
    public function get_access_token($callback = false, $secret = false)
    {
  
        if($secret !== false)$tokenddata['oauth_token_secret'] = urlencode($secret);

        $baseurl = self::SCHEME.'://'.self::HOST.self::ACCESS_URI."?client_id=".$this->_consumer['key']."&redirect_uri=".urlencode($callback)."&client_secret=".$this->_consumer['secret']."&code=$secret";

        $response = $this->_connect($baseurl, '');

        //Parse the response into an array it should contain
        //both the access token and the secret key. (You only
        //need the secret key if you use HMAC-SHA1 signatures.)
        if (strpos($response, 'error')!==false)
        {
            if (function_exists('json_decode'))
            {
                $a = json_decode($response);
            }
            else
            {
                require_once(PATH_THIRD.'social_update/libraries/inc/JSON.php');
                $json = new Services_JSON();
                $a = $json->decode($response);
            }
            $oauth['oauth_problem'] = $a->error->message;
        } 
        else
        {                            
            parse_str($response, $oauth);        
        }   
        //Return the token and secret for storage
        
        $baseurl = self::SCHEME.'://'.self::HOST.self::USERINFO_URI."?&access_token=".$oauth['access_token'];
        $response = $this->_connect($baseurl, array());
        if (function_exists('json_decode'))
        {
            $me = json_decode($response);
        }
        else
        {
            require_once(PATH_THIRD.'social_update/libraries/inc/JSON.php');
            $json = new Services_JSON();
            $me = $json->decode($response);
        }
        
        $pages = array($me->id => $me->name);   
        $tokens = array($me->id => $oauth['access_token']);   

        $baseurl = self::SCHEME.'://'.self::HOST.self::USERINFO_URI."/accounts?&access_token=".$oauth['access_token'];
        $response = $this->_connect($baseurl, array());
        if (function_exists('json_decode'))
        {
            $accounts = json_decode($response);
        }
        else
        {
            require_once(PATH_THIRD.'social_update/libraries/inc/JSON.php');
            $json = new Services_JSON();
            $accounts = $json->decode($response);
        }

        if (!empty($accounts->data))
        {
            foreach ($accounts->data as $page)
            {
                if ($page->category != 'Application')
                {
                    $pages[$page->id] = $page->name;
                    $tokens[$page->id] = $page->access_token;
                }
            }         
        }
        $oauth['pages'] = $pages;   
        $oauth['tokens'] = $tokens;   
        
        

        return $oauth;
    }
    
    /**
     * Connects to the server and sends the request,
     * then returns the response from the server.
     * @param <type> $url
     * @param <type> $auth
     * @return <type>
     */
    private function _connect($url, $auth)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
        curl_setopt($ch, CURLOPT_SSLVERSION,3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array($auth));

        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    } 



    function post($link, $message, $oauth_token='', $oauth_token_secret='', $userid='me', $usertoken='')
    {
        
        if ($usertoken!='') $oauth_token = $usertoken;
        
        if ($link!='')
        {
          $baseurl = self::SCHEME.'://'.self::HOST.'/'.$userid."/links?access_token=".$oauth_token;
		  $fields = array(
              'link'=>$link,
              'message'=>urlencode($message)
          );
        }
        else
        {
          $baseurl = self::SCHEME.'://'.self::HOST.'/'.$userid."/feed?access_token=".$oauth_token;
		  $fields = array(
              'message'=>urlencode($message)
          );
        }
        $fields_string = '';
        foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }
        $fields_string = rtrim($fields_string,'&');            
                
        $ch = curl_init($baseurl);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC ) ;
        curl_setopt($ch, CURLOPT_SSLVERSION,3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        
        curl_setopt($ch,CURLOPT_POST,true);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$fields_string);

        $response = curl_exec($ch);
        curl_close($ch);
        
    	if (function_exists('json_decode'))
        {
            $a = json_decode($response);
        }
        else
        {
            require_once(PATH_THIRD.'social_update/libraries/inc/JSON.php');
            $json = new Services_JSON();
            $a = $json->decode($response);
        }

        $id_a = explode("_", $a->id);
        if (count($id_a)>1)
        {
        	$return = array("remote_user"=>$id_a[0], "remote_post_id"=>$id_a[1]);
       	}
       	else
       	{
       		$return = array("remote_user"=>$userid, "remote_post_id"=>$id_a[0]);
       	}

        return $return;

    }
    
    
    
}
// ./system/application/libraries
?>
