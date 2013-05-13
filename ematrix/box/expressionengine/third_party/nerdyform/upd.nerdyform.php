<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Nerdyform_upd
{
    public $module_name;
    public $version;
    public $EE;

    public function __construct()
    {
        $this->EE =& get_instance();

        $this->EE->load->dbforge();
        
        $this->EE->load->add_package_path(PATH_THIRD .'nerdyform/');
        $this->EE->load->library('nerdyform_lib');
        $this->lib = $this->EE->nerdyform_lib;

        $this->module_name = $this->lib->module_name();
        $this->version = $this->lib->version();
    }

    public function install()
    {
        $actions = array('on_post', 'show_template', 'retry');

        // Add to modules
        $data = array(
            'module_name' => $this->module_name,
            'module_version' => $this->version,
            'has_cp_backend' => 'n',
            'has_publish_fields' => 'n'
        );
        $this->EE->db->insert('modules', $data);

        foreach ($actions as $action)
        {
            $data = array('class' => $this->module_name,
                          'method' => $action);
            $this->EE->db->insert('actions', $data);
        }

        // Create nerdyform_cache table.

        $fields = array(
            'id' => array(
                'type' => 'int',
                'constraint' => 5,
                'unsigned' => true,
                'auto_increment' => true,
                'key' => true),
            'cache_key' => array(
                'type' => 'varchar',
                'constraint' => '64',
                'unique' => true),
            'session_id' => array(
                'type' => 'varchar',
                'constraint' => '40'),
            'data' => array(
                'type' => 'mediumblob'),
            'creation_date' => array(
                'type' => 'timestamp'),
        );
        $this->EE->dbforge->add_key('id', TRUE);
        $this->EE->dbforge->add_field($fields);
        $this->EE->dbforge->create_table('nerdyform_cache');

        return true;
    }

    public function uninstall()
    {
        $this->EE->db->where('module_name', $this->module_name);
        $result = $this->EE->db->get('modules');

        // Remove from modules
        if ($result->num_rows > 0)
        {
            $row = $result->row();
            $module_id = $row->module_id;

            $this->EE->db->where('module_id', $module_id);
            $this->EE->db->delete('modules');
        }

        // Remove actions
        $this->EE->db->where('class', $this->module_name);
        $this->EE->db->delete('actions');

        // Remove nerdyform cache table
        $this->EE->dbforge->drop_table('nerdyform_cache');
        
        return true;
    }

    public function update($current = '')
    {
        return true;
    }

    function tabs()
    {
        return array();
    }
}
