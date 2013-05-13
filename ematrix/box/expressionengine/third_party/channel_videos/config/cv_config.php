<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

if (isset($this->EE) == FALSE) $this->EE =& get_instance(); // For EE 2.2.0+

// Default Field Columns
$config['cv_columns']['image']		= $this->EE->lang->line('cv:video');
$config['cv_columns']['title']		= $this->EE->lang->line('cv:title');
$config['cv_columns']['desc']		= $this->EE->lang->line('cv:desc');
$config['cv_columns']['duration']	= $this->EE->lang->line('cv:duration');
$config['cv_columns']['views']		= $this->EE->lang->line('cv:views');
$config['cv_columns']['date']		= $this->EE->lang->line('cv:date');
$config['cv_columns']['cvfield_1']	= '';
$config['cv_columns']['cvfield_2']	= '';
$config['cv_columns']['cvfield_3']	= '';
$config['cv_columns']['cvfield_4']	= '';
$config['cv_columns']['cvfield_5']	= '';

// Defaults!
$config['cv_defaults']['video_limit'] = '';
