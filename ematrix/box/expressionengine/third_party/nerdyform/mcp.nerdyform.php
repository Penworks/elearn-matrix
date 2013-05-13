<?php if ( ! defined('EXT')) { exit('Invalid file request'); }

class Nerdyform_mcp
{
    public $EE;
    
    public function __construct()
    {
        $this->EE =& get_instance();
    }

    public function index()
    {
    }
}
