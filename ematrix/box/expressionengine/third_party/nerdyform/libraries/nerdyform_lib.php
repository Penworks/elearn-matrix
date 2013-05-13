<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'nerdyform/libraries/emogrifier/emogrifier.php';
require_once PATH_THIRD . 'nerdyform/classes/NerdyConfig.php';


class Nerdyform_lib
{
    public $EE;
    public $global_config;

    // Extra config can be set from an email template to provide defaults.
    public static $extra_config = array();

    // Vars within the current request
    // For passing vars from one part of the template to another.
    public static $_vars = array();

    public function __construct()
    {
        $this->EE =& get_instance();
        $this->EE->lang->loadfile('nerdyform');

        $this->global_config =
            $this->EE->config->item('nerdyform');
    }

    public function module_name()
    {
        return 'Nerdyform';
    }

    public function version()
    {
        return '1.0';
    }

    public function split_template_name($name)
    {
        // Split template group and name
        // 'test/index' becomes array('test', 'index')

        $parts = explode("/", $name);
        if (count($parts) == 0)
        {
            return array('home', 'index');
        }
        elseif (count($parts) == 1)
        {
            return array($parts[0], 'index');
        }
        else
        {
            return $parts;
        }
    }

    public function prefix_array($a, $prefix)
    {
        // Prefix all keys in the array $a with $prefix.
        
        $new = array();
        foreach ($a as $key => $value)
        {
            $new[$prefix . $key] = $value;
        }

        return $new;
    }

    public function deep_prefix_array($a, $prefix)
    {
        // Recursively prefix all keys in the array $a with $prefix.
        
        foreach ($a as $k => $v)
        {
            if (is_array($v))
            {
                $a[$k] = $this->deep_prefix_array($v, $prefix);
            }
        }

        $new = $this->prefix_array($a);
        return $new;
    }

    public function ensure_keys($a, $keys, $default = '')
    {
        // Ensure the array contains keys named $keys.
        
        foreach ($keys as $key)
        {
            $a[$key] = $default;
        }
        return $a;
    }

    public function array_key_intersect($a, $keys, $default = null)
    {
        // Create a new array with matching keys.

        $new = array();
        foreach ($keys as $key)
        {
            if (isset($a[$key]))
            {
                $new[$key] = $a[$key];
            }
            else
            {
                $new[$key] = $default;
            }
        }
        return $new;
    }

    public function cleanup_embed_vars($template)
    {
        // Remove leftover (unparsed) variables from the template.

        $template = preg_replace('/'.LD.':([^!]+?)'.RD.'/', '', $template);
        return $template;
    }

    public function embed($template, $embed_vars, $site_id = '')
    {
        // Parse an existing EE template.

        // Allow 'my/template' and array('my', 'template')
        if (is_array($template))
        {
            list($template_group, $template_name) = $template;
        }
        else
        {
            list($template_group, $template_name) = $this->split_template_name($template);
        }

        $template_body = $this->fetch_template($template_group, $template_name, FALSE, $site_id);

        return $this->parse($template_body, $embed_vars);
    }

    public function fetch_template($template_group, $template_name,
                                   $show_default = true, $site_id = '')
    {
        // Template lib required for the embed/parse functions.
        $this->EE->load->library('template');

        $tmpl = new EE_Template();
        return $tmpl->fetch_template($template_group, $template_name,
                                     $show_default, $site_id);
    }

    public function parse($template_body, $embed_vars, $sub_template = false)
    {
        // Parse a template

        // Template lib required for the embed/parse functions.
        $this->EE->load->library('template');

        // Temporarily replace the TMPL, because addons rely on it.
        $tmpl = new EE_Template();

        $orig_tmpl =& $this->EE->TMPL;
        $this->EE->TMPL =& $tmpl;

        // Assign variables to the templates.
        // These variables are seen as snippets and therefore parsed early.
        $orig_global_vars =& $this->EE->config->_global_vars;
        $this->EE->config->_global_vars = array_map('strval', array_merge(
            (array)$orig_global_vars,
            $this->prefix_array((array)$embed_vars, 'embed:'),
            $this->prefix_array((array)$embed_vars, ':')
        ));

        $tmpl->parse($template_body, $sub_template);

        $this->EE->config->_global_vars =& $orig_global_vars;

        $final_template = $tmpl->final_template;

        // Global variables.
        $final_template = $tmpl->parse_globals($final_template);

        $this->EE->TMPL =& $orig_tmpl;

        return $this->cleanup_embed_vars($final_template);
    }

    public function parse_full($template = array('',''))
    {
        // Run the template engine
        
        // Allow 'my/template' and array('my', 'template')
        if (is_array($template))
        {
            list($template_group, $template_name) = $template;
        }
        else
        {
            list($template_group, $template_name) = $this->split_template_name($template);
        }

        // Template lib required for the embed/parse functions.
        $this->EE->load->library('template');

        $this->EE->TMPL = new EE_Template();
        $this->EE->TMPL->run_template_engine($template_group, $template_name);
        //$this->EE->_output($this->EE->output);
        $this->EE->output->_display();
        
    }

    public function fetch_params()
    {
        // Fetch all params from the template.

        $params = array();
        foreach ($this->EE->TMPL->tagparams as $param => $raw_value)
        {
            $params[$param] = $this->EE->TMPL->fetch_param($param);
        }
        return $params;
    }

    public function fetch_param_tree()
    {
        // Create a multi-dimensional array of params, based on colon (":")

        $param_tree = array();

        $param_tree_obj = new NerdyConfig();

        foreach ((array)$this->EE->TMPL->tagparams as $param => $raw_value)
        {
            $value = $this->EE->TMPL->fetch_param($param);

            $param_parts = explode(':', $param);

            $param_tree = $this->param_tree_set($param_tree, $param_parts, $value);
            //$param_tree_obj[explode(':', $param)] = $value;
            $param_tree_obj->set(explode(':', $param), $value);
        }

        return $param_tree_obj;
    }

    public function param_tree_set($a, $keys, $value)
    {
        // Set a value in the param tree.

        if (0 == count($keys))
        {
            return $value;
        }
        else
        {
            $key = array_shift($keys);
            if (!isset($a[$key]) || !is_array($a[$key]))
            {
                $a[$key] = array();
            }
            $a[$key] = $this->param_tree_set($a[$key], $keys, $value);
            return $a;
        }
    }

    public function fetch_body()
    {
        // Get the tag-pair body

        return $this->EE->TMPL->tagdata;
    }

    public function make_form($attributes, $hidden_fields, $body)
    {
        // Create a HTML form-tag.

        $new_body = '';

        foreach ($hidden_fields as $name => $value)
        {
            $new_body .= $this->html_tag('input', array(
                'name' => $name,
                'value' => $value,
                'type' => 'hidden',
            ));
        }

        $new_body .= $body;
        return $this->html_tag('form', $attributes, $new_body);
    }

    public function html_tag($tag, $attributes, $body = null)
    {
        // Create a HTML tag.

        $str = '';
        $str .= '<' . $tag;
        
        foreach ($attributes as $key => $value)
        {
            $str .= ' ' . $key . '="' . htmlspecialchars($value) . '"';
        }

        if (is_null($body))
        {
            $str .= '/>';
        }
        else
        {
            $str .= '>';
            $str .= $body;
            $str .= '</' . $tag . '>';
        }

        return $str;
    }

    public function action_id($method)
    {
        // Find action ID for the current module.
        
        $this->EE->db->where(array(
            'class' => $this->module_name(),
            'method' => $method));
        $result = $this->EE->db->get('actions');
        if ($result->num_rows > 0)
        {
            $row = $result->row();
            return $row->action_id;
        }
        else
        {
            return false;
        }
    }

    public function get_post_data()
    {
        // Get post data, excluding meta data created by this addon.

        $excludes = array('ACT', 'return', 'XID', 'hash', '_meta');
        $params = array();
        foreach ((array)$_POST as $key => $value)
        {
            if (!in_array($key, $excludes))
            {
                $safe_value = $this->EE->input->get_post($key);
                $params[$key] = $value;
            }
        }

        if (($meta = $this->restore_meta())
            && isset($meta['hidden_vars'])
            && ($hidden_vars = $meta['hidden_vars']))
        {
            foreach ($hidden_vars as $key => $value)
            {
                $params[$key] = $value;
            }
        }
        

        return $params;
    }

    public function files()
    {
        // Return only successful uploads.
        $files = array();
        foreach ($_FILES as $k => $v)
        {
            if (UPLOAD_ERR_OK == $v['error'])
            {
                $info = array();
                $info['name'] = $v['name'];
                $info['type'] = $v['type'];
                $info['size'] = $v['size'];
                $info['path'] = $v['tmp_name'];
                
                $files[$k] = $info;
            }
        }
        
        return $files;
    }

    public function parse_config($config_array, $data, $keep_as_is = array())
    {
        // assume a flat config_array.
        
        $parsed_config = new NerdyConfig();
        foreach ($config_array as $key => $value)
        {
            if (in_array($key, $keep_as_is))
            {
                $parsed_config->set($key, $value);
            }
            else
            {
                $parsed_config->set($key, $this->parse($value, array_map('strval', $data)));
            }
        }

        return $parsed_config;
    }

    public function send_email($email_config, $data)
    {
        // Send email, using the $email_config.
        // $data contains the submitted values and is used as
        // parameters for the email template.

        // $email_config is an array of key=>value options.

        $this->EE->load->library('email');

        // Get the required config options.

        // Parse each config option. (EE doesn't parse these,
        // unless parse="inward")
        $keep_as_is = array('attach');
        $parsed_config = $this->parse_config($email_config, $data, $keep_as_is);

        // Allow extra config options set from within the email template.
        $this->clear_extra_email_config();

        $preferences = array();

        // Run the templates
        if (($text_template = $parsed_config->get('text')))
        {
            $text_content = $this->embed($text_template, array_map('strval', $data));
        }

        if (($html_template = $parsed_config->get('html')))
        {
            $html_content = $this->embed($html_template, array_map('strval', $data));

            if (($css_file = $parsed_config->get('css')))
            {
                // Run through emogrifier
                $css_content = $this->get_file_contents($css_file);
                $html_content = $this->emogrify($html_content, $css_content);
            }
            
            $preferences['mailtype'] = 'html';
        }
        else
        {
            $preferences['mailtype'] = 'text';
        }


        // Check for headers modified within the template.
        $extra_config = $this->get_extra_email_config();


        // Parameters from the form tag take precedence.
        $config = NerdyConfig::merge($extra_config, $parsed_config->to_array());
        $config_array = $config->to_array();
        $extract_keys = array('to', 'from', 'subject', 'from_name', 'cc', 'cc_name',
                              'bcc',
                              'reply_to', 'reply_to_name',
                              'html', 'text');
        extract($this->array_key_intersect(
            $config_array,
            $extract_keys
        ));

        $copy_prefs = array(
            'wordwrap', 'wrapchars', 'mailtype', 'charset'
        );
        foreach ($copy_prefs as $pref)
        {
            if (isset($config_array[$pref]))
            {
                $preferences[$pref] = $value;
            }
        }

        // Override to and from
        if (isset($this->global_config['debug_to']))
        {
            $subject .= ' [orig to: ' . $to . ']';
            $to = $this->global_config['debug_to'];
        }
        if (isset($this->global_config['debug_from']))
        {
            $subject .= ' [orig from: ' . $from . ']';
            $from = $this->global_config['debug_from'];
        }
        if (isset($this->global_config['debug_show_template']))
        {
            if ($html_template)
            {
                $subject .= ' [template: ' . $html_template . ']';
            }
            if ($text_template)
            {
                $subject .= ' [text-template: ' . $html_template . ']';
            }
            $from = $this->global_config['debug_from'];
        }

        // Set the e-mail headers
        $this->EE->email->clear(true);
        $this->EE->email->initialize($preferences);
        // for inline attachments
        $this->EE->email->multipart = 'related';
        $this->EE->email->to(explode('|', $to));
        $this->EE->email->from($from, $from_name);
        if ($cc)
        {
            $this->EE->email->cc($cc, $cc_name);
        }
        if ($reply_to)
        {
            $this->EE->email->reply_to($reply_to, $reply_to_name);
        }
        if ($bcc)
        {
            $this->EE->email->bcc($bcc);
        }

        $this->EE->email->subject($subject);


        // And the data
        if (isset($html_content) && $html_content)
        {
            $this->EE->email->message($html_content);

            if (isset($text_content))
            {
                $this->EE->email->set_alt_message($text_content);
            }
        }
        elseif (isset($text_content))
        {
            $this->EE->email->message($text_content);
        }
        else
        {
            // error. missing templates
        }

        
        // Handle attachments
        if ('yes' == $config->get('attach_all'))
        {
            foreach ($this->files() as $name => $file)
            {
                $path = $file['path'];
                $this->email_attach($file['path'], $file['name']);
            }
        }

        if (($attachments = $config->get('attach')))
        {
            $files = $this->files();
            
            foreach ($attachments as $id => $info)
            {
                if ('yes' == $info->get('inline'))
                {
                    $disposition = 'inline';
                }
                else
                {
                    $disposition = 'attachment';
                }
                
                // Uploaded file
                if (($name = $info->get('upload')))
                {
                    if (isset($files[$name])
                        && ($file = $files[$name]))
                    {
                        $path = $file['path'];
                        
                        if ($info->get('filename'))
                        {
                            $filename = $info->get('filename');
                        }
                        elseif ($info->get('rename'))
                        {
                            $filename = basename($this->rename_filename(
                                $file['name'], $info->get('rename')));
                        }
                        elseif ($info->get('prefix'))
                        {
                            $filename = $info->get('prefix') . $file['name'];
                        }
                        else
                        {
                            $filename = basename($path);
                        }

                        $this->email_attach($path, $filename, $disposition);
                    }
                    else
                    {
                        // file not found
                        // Todo: error handling
                    }
                }
                // Local file
                elseif (($path = $info->get('path'))
                        && ($realpath = realpath($path)))
                {
                    if ($info->get('filename'))
                    {
                        $filename = $info->get('filename');
                    }
                    else
                    {
                        $filename = basename($realpath);
                    }

                    $this->email_attach($realpath, $filename, $disposition);
                }
                // Error...
                else
                {
                    // Unknown attachment type (upload/local file)
                    // Todo: error handling
                }
            }
        }

        /* echo '<pre>'; */
        /* var_dump(array( */
        /*     $this->EE->email->_attach_name, */
        /*     $this->EE->email->_attach_type, */
        /*     $this->EE->email->_attach_disp, */
        /* )); */
        /* $this->EE->email->send(); */
        /* exit; */

        $ret = $this->EE->email->send();
        return $ret;
    }


    // Default email config to be used from an email template.
    // The extra email config provides default settings.

    public function set_extra_email_config($config)
    {
        self::$extra_config = $config;
    }

    public function add_extra_email_config($config)
    {
        self::$extra_config = NerdyConfig::merge(self::$extra_config, $config);
    }

    public function get_extra_email_config()
    {
        return self::$extra_config;
    }

    public function clear_extra_email_config()
    {
        $this->set_extra_email_config(array());
    }

    public function issetor(&$var, $default = null)
    {
        // Essentially: isset($var) ? $var : $default;
        if (isset($var))
        {
            return $var;
        }
        else
        {
            return $default;
        }
    }

    public function current_uri()
    {
        // Return the current (relative) url.
        return '/' . implode('/', $this->EE->uri->segments);
    }

    public function redir_and_exit($url)
    {
        // Redirect to $url and exit.

        header('Location: ' . $url);
        exit;
    }

    public function action_url($method, $params)
    {
        // Generate the the URL for an EE action.
        
        $action_id = $this->action_id($method);
        
        $query_string = http_build_query(
            array('ACT' => $action_id,
                  'x' => $this->store_cache_data($params)));

        $url = $this->current_uri() . '?' . $query_string;
        return $url;
    }

    public function action_redir($method, $params)
    {
        // Redirect to an EE action.
        
        $url = $this->action_url($method, $params);
        $this->redir_and_exit($url);
    }

    public function from_action_redir()
    {
        // Fetch params for the current action.
        return $this->restore_cache_data($this->EE->input->get_post('x'));
    }

    public function random_string()
    {
        // Generate a random string.
        // Todo: Generate a truly random string. Get rid of the hashing.
        $hash = sha1(base_convert(mt_rand(), 10, 36));
        return $hash;
    }

    public function store_cache_data($data)
    {
        // Save the data in the database for use in the next request.
        // Returns the identifier for this data.

        $cache_key = $this->random_string();
        
        $data = array(
            'cache_key' => $cache_key,
            'session_id' => $this->get_session_id(),
            'data' => $this->encode($data),
        );
        $this->EE->db->insert('nerdyform_cache', $data);

        //$id = $this->EE->db->insert_id();
        $id = $cache_key;
        return $id;
    }

    public function restore_cache_data($id)
    {
        // Return the cache data identified by $id.

        $this->EE->db->where(array(
            'cache_key' => $id,
            'session_id' => $this->get_session_id(),
        ));
        $result = $this->EE->db->get('nerdyform_cache');
        if ($result->num_rows > 0)
        {
            $row = $result->row();
            return $this->decode($row->data);
        }
        else
        {
            return false;
        }
    }

    public function get_session_id()
    {
        // Current session id.
        return $this->EE->session->userdata['session_id'];
    }

    public function cleanup_cache()
    {
        // Remove expired cache data.
    }

    public function encode_meta($meta)
    {
        // Encode meta information
        return $this->encode($meta);
    }

    public function encode($data)
    {
        // Serialize data for use in the database.
        return base64_encode(serialize($data));
    }

    public function decode($data)
    {
        // Unserialize data from the database.
        return unserialize(base64_decode($data));
    }

    public function restore_meta()
    {
        // Restore and decode meta information.
        
        if (isset($_POST['_meta']))
        {
            //return unserialize(base64_decode($_POST['_meta']));
            $id = $_POST['_meta'];
            return $this->restore_cache_data($id);
        }

        if (($action_params = $this->from_action_redir())
            && isset($action_params['meta']))
        {
            return $action_params['meta'];
        }
    }

    public function set_current_action($method, $params)
    {
        self::$_current_action = array($method, $params);
    }

    public function get_current_action()
    {
        return self::$_current_action;
    }

    public function set_var($name, $value)
    {
        self::$_vars[$name] = $value;
    }

    public function set_vars($vars)
    {
        self::$_vars = $vars;
    }

    public function get_var($name, $default = null)
    {
        if (isset(self::$_vars[$name]))
        {
            return self::$_vars[$name];
        }
        else
        {
            return $default;
        }
    }

    public function template_array($arr)
    {
        // Transform an associative array into an EE tag-pair loop
        // So that for example
        // ['john' => 'john@example.com', 'alice' => 'alice@example.com']
        // Can be used as:
        // {people}
        // {key}'s email address is {item}<br/>
        // {/people}
        
        $new = array();
        foreach ($arr as $var => $value)
        {
            $new[] = array('var' => $var,
                           'key' => $var,
                           'value' => (string)$value,
                           'item' => (string)$value);
        }

        return $new;
    }

    public function generate_page()
    {
        // Let EE parse the current page.
        $this->EE->core->generate_page();
    }

    public function ee_validator($arg)
    {
        // Return an instance of EE_Validate.
        
        if (!class_exists('Validate'))
        {
            require APPPATH . 'libraries/Validate' . EXT;
        }

        $validator = new EE_Validate($arg);
        return $validator;
    }

    public function L($x)
    {
        // Translate
        return $this->EE->lang->line($x);
    }

    public function email_attach($path, $filename = null, $disposition = 'attachment')
    {
        // Uses EE->email->attach, but checks whether  the file exist.
        // If we do not do this check, EE sents an empty email.
        
        $tempdir = $this->mktempdir();

        // Ensure file exists
        if (file_exists($path))
        {
            if ($filename)
            {
                $new_path = $tempdir . '/' . $filename;
                copy($path, $new_path);
                $path = $new_path;
            }

            if ('inline' == $disposition)
            {
                $basename = basename($path);
                $disposition .= '; filename="' . $basename . '";' . "\n";
                $disposition .= 'Content-Type: application/octet-stream; name="' . $basename. '"' . "\n";
                $disposition .= 'Content-ID: <' . $basename . '>' . "\n";
                $disposition .= 'Content-Disposition: inline';
                $disposition .= '; filename="' . $basename . '"';

                /* $disposition .= NL .'Content-ID: <' . $basename . '>'; */
            }
            
            $this->EE->email->attach($path, $disposition);
            
            return true;
        }
        else
        {
            echo $path . ' does not exist.';
            return false;
        }
    }

    public function mktempdir()
    {
        // Create a temporary directory.
        if (!class_exists('System'))
        {
            include_once 'System.php';
        }

        return System::mktemp('-d');
    }

    public function coalesce()
    {
        // Return the first argument that evaluates to true.
        $args = func_get_args();
        return $this->coalesce_array($args);
    }

    public function coalesce_array($args)
    {
        // Return the first array item that evaluates to true.
        foreach ($args as &$arg)
        {
            if (isset($arg) && $arg)
            {
                return $arg;
            }
        }

        return null;
    }

    public function rename_filename($filename, $newname)
    {
        // Rename the filename, while preserving the extension.
        // Does not actually rename the file, just returns the new filename.
        $parts = pathinfo($filename);

        $new_path = '';
        
        if ($parts['dirname'])
        {
            $new_path .= $parts['dirname'] . '/';
        }
        
        $new_path .= $newname;
        
        if ($parts['extension'])
        {
            $new_path .= '.' . $parts['extension'];
        }

        return $new_path;
    }

    public function get_file_contents($file)
    {
        // Get contents of a file, relative to document root.
        $docroot = $_SERVER['DOCUMENT_ROOT'];
        $path = $docroot . '/' . $file;
        if (file_exists($path))
        {
            return file_get_contents($path);
        }
        else
        {
            return false;
        }
    }

    public function emogrify($html, $css)
    {
        $emogrifier = new Emogrifier($html, $css);

        return $emogrifier->emogrify();
    }
}
