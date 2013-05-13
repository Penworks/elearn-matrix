<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Nerdyform
{
    public $return_data = false;
    public $EE;

    // The Nerdyform lib
    public $lib;

    public function __construct()
    {
        $this->EE =& get_instance();

        // Nerdyform lib
        $this->EE->load->add_package_path(PATH_THIRD .'nerdyform/');
        $this->EE->load->library('nerdyform_lib');
        $this->lib = $this->EE->nerdyform_lib;
    }

    public function form()
    {
        // Generate a HTML form and dispatch to on_post.
        // Parameters will be saved in the database for retrieval in
        // on_post.
        
        $param_tree = $this->lib->fetch_param_tree();
        $body = $this->lib->fetch_body();

        $form_attributes = $param_tree->get_array('form');

        $meta = array();
        $meta['param_tree'] = $param_tree;
        $meta['return_url'] = $param_tree->get('return_url', $this->lib->current_uri());

        $meta['hidden_vars'] = $param_tree->get_array('hidden');

        // Set form attributes
        
        // Use current_uri as action so that we can load the template
        // based on that uri.
        $form_attributes['action'] = $this->lib->current_uri();
        $form_attributes['enctype'] = 'multipart/form-data';
        $form_attributes['method'] = 'POST';


        // Dispatch to on_post.
        $hidden_fields = array();
        $hidden_fields['ACT'] = $this->lib->action_id('on_post');
        $hidden_fields['_meta'] = $this->lib->store_cache_data($meta);

        // Todo: XID
        //$hidden_fields['XID'] = '';

        
        if ($param_tree->exists('return'))
        {
            $hidden_fields['return'] = $param_tree->get('return');
        }

        // Handle saved vars
        $new_body = $body;

        // When success:inline or fail:inline is set, the same
        // template will be loaded with additional info about
        // error/sucess messages.
        if (($action_params = $this->lib->from_action_redir()))
        {
            if ($data = $this->lib->get_var('data'))
            {
                $vars = $this->lib->prefix_array((array)$data, ':');
                $new_body = $this->EE->TMPL->parse_variables($new_body, array($vars));
            }

            $vars = array();
            if (count($this->lib->get_var('errors', array())) > 0)
            {
                $vars['errors'] = '1';
                $vars['messages'] = $this->lib->template_array($this->lib->get_var('errors'));
            }
            else
            {
                $vars['errors'] = '';
            }
            if ($this->lib->get_var('success'))
            {
                $vars['success'] = '1';
            }
            else 
            {
                $vars['success'] = '';
            }
            $new_body = $this->EE->TMPL->parse_variables($new_body, array($vars));
        }

        $new_body = $this->lib->cleanup_embed_vars($new_body);
        
        $str = $this->lib->make_form($form_attributes, $hidden_fields, $new_body);

        return $str;
    }

    public function _show_error($message)
    {
        // Show the message.
        $this->EE->output->show_user_error('submission', $message);
    }

    public function _validate_required()
    {
        return array();
    }

    public function _validate()
    {
        $meta = $this->lib->restore_meta();
        $param_tree = $meta['param_tree'];
        $data = $this->lib->get_post_data();

        if (!$param_tree)
        {
            // Error.
            /* $errors[] = ; */
            /* return $errors; */
        }

        $errors = array();
        
        // Check whether all required fields are present.
        if ($str_required_fields = $param_tree->get('required', ''))
        {
            $required_fields = explode('|', $param_tree->get('required'));
            foreach ($required_fields as $field)
            {
                if (!(isset($data[$field])
                      && ($data[$field])))
                {
                    $errors[] = lang('field_required') . ' ' . $field;
                }
            }
        }

        // Assume 'email' parameter
        // Validate email field
        // Todo: Create a way to specify validation methods for each field.
        if (isset($data['email'])
            && ($email = $data['email']))
        {
            $validator = $this->lib->ee_validator(array('email' => $email));
            $validator->validate_email();

            if ($validator->errors)
            {
                $errors = array_merge($errors, $validator->errors);
            }
        }

        return $errors;
    }

    public function on_post()
    {
        // Handle submitted form.

        // Original params from the form-tag.
        $meta = $this->lib->restore_meta();
        $param_tree = $meta['param_tree'];

        // Submitted values
        $data = $this->lib->get_post_data();

        // Validate the form.
        $errors = $this->_validate();

        if ($this->EE->extensions->active_hook('nerdyform_validate'))
        {
            // nerdyform_validate(nerdyform, current errors, data, meta, param_tree)
            $errors = $this->EE->extensions->call('nerdyform_validate', $this, $errors, $data, $meta, $param_tree);
        }

        if (count($errors) > 0)
        {
            if (($fail_template = $param_tree->get('fail_template')
                 || $fail_template = $param_tree->get(array('fail', 'template')))
            )
            {
                $this->lib->action_redir('show_template', array(
                    'data' => array_merge($data, array('success' => 1)),
                    'meta' => $meta,
                    'template' => $fail_template,
                ));
            }
            elseif (($fail_url = $param_tree->get('fail_url'))
                    || $fail_url = $param_tree->get(array('fail', 'url')))
            {
                $this->lib->redir_and_exit($fail_url);
            }
            elseif (($fail_inline = ('yes' == $param_tree->get('fail_inline')))
                    || ($fail_inline = ('yes' == $param_tree->get(array('fail', 'inline')))))
            {
                $this->lib->action_redir('retry', array(
                    'data' => array_merge($data),
                    'meta' => $meta,
                    'errors' => $errors,
                ));
            }
            else
            {
                //$this->_show_error('Errors in the form');
                $this->_show_error($errors);
                exit;
            }
        }
        else
        {
            // No errors.

            if ($this->EE->extensions->active_hook('nerdyform_success'))
            {
                // nerdyform_on_success(param_tree, data, files)
                $files = $this->lib->files();
                $ret = $this->EE->extensions->universal_call('nerdyform_success', $param_tree, $data, $files);
                if (is_array($ret) && count($ret) > 2)
                {
                    list($param_tree, $data, $files) = $ret;
                }
            }
            
            // Send emails.
            // Multiple email configs are allowed, each with its own template, headers, etc.
            if ($param_tree->exists('email'))
            {
                foreach ($param_tree->get('email') as $email_config)
                {
                    if ('yes' != $email_config->get('disable'))
                    {
                        $this->lib->send_email($email_config, $data);
                    }
                }
            }

            // EE Template
            if (($success_template = $param_tree->get('success_template'))
                || ($success_template = $param_tree->get(array('success', 'template'))))
            {
                $this->lib->action_redir('show_template', array(
                    'data' => array_merge($data, array('success' => 1)),
                    'meta' => $meta,
                    'template' => $success_template,
                ));
                exit;
            }
            elseif (($success_inline = ('yes' == $param_tree->get('success_inline')))
                    || ($success_inline = ('yes' == $param_tree->get(array('success', 'inline')))))
            {
                $this->lib->action_redir('retry', array(
                    'data' => array_merge($data),
                    'meta' => $meta,
                    'success' => true,
                ));
            }
            elseif (($success_url = $param_tree->get(array('success', 'url'))))
            {
                $this->lib->redir_and_exit($success_url);
            }
            else
            {
                $return_url = $meta['return_url'];
            
                $this->lib->redir_and_exit($return_url);
            }
        }
    }

    public function show_template()
    {
        // show_template action
        if ($action_params = $this->lib->from_action_redir())
        {
            $template = $action_params['template'];
            $data = $action_params['data'];
            $this->lib->set_vars($action_params);
            echo $this->lib->embed($template, $data);
            exit;
        }
        else
        {
            $this->_show_error('Template not found.');
        }
    }

    public function retry()
    {
        // Show current page.
        // :form will then be able to fill all the values.
        if ($action_params = $this->lib->from_action_redir())
        {
            $this->lib->set_vars($action_params);
        }
        
        $this->lib->generate_page();
    }

    public function loop()
    {
        // Loop through a submitted array.
        
        $var = $this->EE->TMPL->fetch_param('var');
        $template = $this->EE->TMPL->tagdata;

        $data = $this->lib->get_post_data();
        if (isset($data[$var])
            && is_array($data[$var])
            && $arr = $data[$var])
        {
            // Don't allow more than one level deep
            $new = array();
            foreach ($arr as $index => $properties)
            {
                $new[] = array_merge(
                    array('key' => $index),
                    $properties);
            }
            
            // Using array_values to renumber keys
            return $this->EE->TMPL->parse_variables($template, $new);
        }
        else
        {
            return '';
        }
    }

    public function all_vars()
    {
        // Loop through all submitted variables.
        
        $template = $this->EE->TMPL->tagdata;
        $data = $this->lib->get_post_data();

        $new = array();
        foreach ($data as $var => $value)
        {
            $new[] = array('var' => $var,
                           'key' => $var,
                           'value' => (string)$value);
        }

        return $this->EE->TMPL->parse_variables($template, $new);
    }

    public function get_var()
    {
        $name = $this->EE->TMPL->fetch_param('name');
        $default = $this->EE->TMPL->fetch_param('default');
        
        $data = $this->lib->get_post_data();

        if (isset($data[$name])
            && $data[$name])
        {
            return $data[$name];
        }
        else
        {
            return $default;
        }
    }

    public function is_set()
    {
        $name = $this->EE->TMPL->fetch_param('name');
        
        $data = $this->lib->get_post_data();

        if (isset($data[$name])
            && $data[$name])
        {
            return '1';
        }
        else
        {
            return '';
        }
    }

    public function yesno()
    {
        // Checks whether a variable is present in get/post.
        // Returns the given values for when it is and isn't.
        
        $name = $this->EE->TMPL->fetch_param('name');
        $yes = $this->EE->TMPL->fetch_param('y', 'yes');
        $no = $this->EE->TMPL->fetch_param('n', 'no');
        
        $data = $this->lib->get_post_data();

        if (isset($data[$name])
            && $data[$name])
        {
            return $yes;
        }
        else
        {
            return $no;
        }
    }

    public function email_config()
    {
        // Sets email headers for the current email.
        // This is called from the email template to provide default
        // config settings.

        $this->lib->add_extra_email_config(
            $this->lib->fetch_param_tree());

        return '';
    }


    // The following functions are just for debugging purposes.
    
    public function test()
    {
        return 'test';
    }

    public function halt()
    {
        // Added here to stop execution to be able to see non
        // fatal errors/warnings instead of EE garbage.
        exit;
    }

    public function dump_vars()
    {
        $data = $this->lib->get_post_data();
        return print_r($data, true);
    }

    public function debug()
    {
        $data = array(
            'vars' => $this->lib->get_post_data(),
            'action_params' => $this->lib->from_action_redir(),
            'param_tree' => $this->lib->fetch_param_tree(),
            'current_uri' => $this->lib->current_uri(),
            'meta' => $this->lib->restore_meta(),
            'files' => $this->lib->files(),
        );

        ob_start();
        var_dump($data);
        $str = ob_get_clean();
        return $str;
    }
}
