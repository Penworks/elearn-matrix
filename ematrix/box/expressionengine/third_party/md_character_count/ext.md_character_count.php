<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * MD Character Count Extension
 *
 * @package    ExpressionEngine
 * @subpackage Addons
 * @category   Extension
 * @author     Benjamin Kohl
 * @link       http://devot-ee.com/add-ons/md-character-count
 */

class Md_character_count_ext {

	public $settings 		= array();
	public $description		= 'Add a customizable character count to CP publish form fields (Textareas and text inputs)';
	public $docs_url		= 'http://devot-ee.com/add-ons/md-character-count';
	public $name			= 'MD Character Count';
	public $settings_exist	= 'y';
	public $version			= '2.0';
	
	private $EE;
	
	// ----------------------------------------------------------------------
	
	/**
	 * Constructor
	 *
	 * @param 	mixed	Settings array or empty string if none exist.
	 */
	public function __construct($settings = '')
	{
		$this->EE =& get_instance();
		
		if(isset($this->EE->session->cache['mdesign']) === FALSE)
		   $this->EE->session->cache['mdesign'] = array();
		
		if ( ! defined('MD_CC_version')){
		  define("MD_CC_version",         "2.0");
		  define("MD_CC_docs_url",        "http://devot-ee.com/add-ons/md-character-count");
		  define("MD_CC_addon_id",        "MD Character Count");
		  define("MD_CC_extension_class", "Md_character_count_ext");
		  define("MD_CC_cache_name",      "mdesign_cache");
		}
		
		$this->settings = $this->_get_settings();
		$this->debug = $this->EE->input->get_post('debug');
	}
	
	// ----------------------------------------------------------------------
	
	/**
     * Get the site specific settings from the extensions table
     *
     * @param  $force_refresh  bool  Get the settings from the DB even if they are in the session
     * @param  $return_all     bool  Return the full array of settings for the installation rather than just this site
     * @return array   If settings are found otherwise false. Site settings are returned by default. 
     *         Installation settings can be returned is $return_all is set to true
     * @since  Version 2.0.0
     */  
		protected function _get_settings($force_refresh = FALSE, $return_all = FALSE)
		{
		// assume there are no settings
		$settings = FALSE;
		$this->EE->load->helper('string'); // For 'strip_slashes'

			// Get the settings for the extension
			if(isset($this->EE->session->cache['mdesign'][MD_CC_addon_id]['settings']) === FALSE || $force_refresh === TRUE)
			{
				// check the db for extension settings
				$this->EE->db->select('settings');
				$query = $this->EE->db->get_where("exp_extensions", array('enabled' => 'y', 'class' => MD_CC_extension_class), 1);
				// if there is a row and the row has settings
				if ($query->num_rows > 0 && $query->row('settings') != '')
				{
					// save them to the cache
					$this->EE->session->cache['mdesign'][MD_CC_addon_id]['settings'] = unserialize($query->row('settings'));
				}
			}

			// check to see if the session has been set
			// if it has return the session
			// if not return false
			if(empty($this->EE->session->cache['mdesign'][MD_CC_addon_id]['settings']) !== TRUE)
			{
				@$settings = ($return_all === TRUE) ?	 $this->EE->session->cache['mdesign'][MD_CC_addon_id]['settings'] : $this->EE->session->cache['mdesign'][MD_CC_addon_id]['settings'][$this->EE->config->item('site_id')];
			}
			return $settings;
		}

	// ----------------------------------------------------------------------

	/**
	  * Returns the default settings for this extension
	  * This is used when the extension is activated or when a new site is installed
	  * It returns the default settings for the CURRENT site only.
	  */
	function _build_default_settings()
	{

	    $default_settings = array(
	        'enable'                  => 'y',
					//'enable_nsmbm'			  => 'n',  Not being used currently.
	        'field_defaults'          => array(),
	        //'check_for_updates'       => 'y',
	        'css' => '
.charcounter {
  font-size: 11px;
  float: left;
  clear: left;
  padding: 6px 0 0 2px;
  }

.charcounter_err {
  color: #933;
  font-weight: bold;
  }'

	        );

	    // get all the sites
	    $query = $this->EE->db->get_where("exp_channels", array("site_id" => $this->EE->config->item('site_id')));

	    // if there are channels
	    if ($query->num_rows() > 0)
	    {
	      // for each of the channelss
	      foreach($query->result() as $row)
	      {
	        // duplicate the default settings for this site
	        // that way nothing will break unexpectedly
	        $default_settings['field_defaults'][$row->site_id][$row->channel_id] = array(
	          'count_max'   => '',
	          'count_type'  => 'false', //a string
	          'count_format'  => ''
	        );
	      }
	    }
	    return $default_settings;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Settings Form (written much like a controller action with a view)
	 *
	 * @param	Array	Settings
	 * @return 	void
	 */	
	public function settings_form($current)
	{
		$this->EE->load->helper('html');
		$this->EE->load->helper('form');
		
		$settings = $this->_get_settings();
		
		$query = $this->EE->db->query("SELECT c.field_id, g.group_name, c.field_label, c.field_maxl, c.site_id FROM exp_channel_fields c, exp_field_groups g WHERE c.site_id = ".
	        $this->EE->config->item('site_id')." AND c.group_id = g.group_id AND field_type IN ( 'textarea', 'text', 'markitup' ) ORDER BY g.group_id, c.field_order");

		$channel_fields = $query->result();
		
		$nsmbm_enabled = $this->check_for_nsm_better_meta();
		
		$values = array(
			'ext_settings'         => $settings,
			'version_number'       => $this->version,
			'channel_fields'       => $channel_fields,
			'lang_extension_title' => $this->EE->lang->line('extension_title'),
			'lang_fields_title'    => $this->EE->lang->line('fields_title'),
			'lang_fields_info'     => $this->EE->lang->line('fields_info'),
			'lang_ct_count'        => $this->EE->lang->line('coltitle_count'),
			'lang_ct_count_type'   => $this->EE->lang->line('coltitle_count_type'),
			'lang_ct_count_format' => $this->EE->lang->line('coltitle_count_format'),
			'lang_css_title'       => $this->EE->lang->line('css_title'),
			'lang_css_info'        => $this->EE->lang->line('css_info'),
			'lang_max'             => $this->EE->lang->line('maximum_label')
		);
		
		return $this->EE->load->view('settings_form', $values, TRUE);
	}
	
	
	// ----------------------------------------------------------------------
	
	/**
	 * Save Settings
	 *
	 * This function provides a little extra processing and validation 
	 * than the generic settings form.
	 *
	 * @return void
	 */
	public function save_settings()
	{
		$this->settings = $this->_get_settings(TRUE, TRUE);

	    // unset the name
	    unset($_POST['name']);

	    $good = array("", "", "","", ""); // This is where all this really comes in handy...
	    $bad = array("\'", '\"', '\\', ";", ":"); // These 2 items help keep bad information from being entered into the format string.

	    if (isset($_POST['field_defaults'])) {
	      foreach ($_POST['field_defaults'] as $key => $value)
	      {
	        unset($_POST['field_defaults_' . $key]);
	        $_POST['field_defaults'][$key]['count_max'] = preg_replace("[^0-9]", "", $_POST['field_defaults'][$key]['count_max']);
	        $_POST['field_defaults'][$key]['count_format'] = str_replace($bad, $good, $_POST['field_defaults'][$key]['count_format']);
	      }
	    }
	    // add the posted values to the settings
	    $this->settings[$this->EE->config->item('site_id')] = $_POST;

	    // update the settings
		$ext_class = __CLASS__;
		if ($this->EE->db->update('exp_extensions', array('settings'=>serialize($this->settings)), "class = '$ext_class'"))
			$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('preferences_updated'));
		else
			$this->EE->session->set_flashdata('message_error', 'Update Failed');		
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * Activate Extension
	 *
	 * This function enters the extension into the exp_extensions table
	 *
	 * @see http://codeigniter.com/user_guide/database/index.html for
	 * more information on the db class.
	 *
	 * @return void
	 */
	public function activate_extension()
	{
		// Setup custom settings in this array.
		$default_settings = $this->_build_default_settings();
				
		// get the list of installed sites
	    $query = $this->EE->db->get("exp_sites");
		// if there are sites - we know there will be at least one but do it anyway
	    if ($query->num_rows > 0)
	    {
	      // for each of the sites
	      foreach($query->result_array() as $row)
	      {
	        // build a multi dimensional array for the settings
	        $settings[$row['site_id']] = $default_settings;
	      }
	    } 

	    // get all the sites
	    $query = $this->EE->db->get("exp_channels");

	    // if there are weblogs
	    if ($query->num_rows > 0)
	    {
	      // for each of the sweblogs
	      foreach($query->result_array() as $row)
	      {
	        // duplicate the default settings for this site
	        // that way nothing will break unexpectedly
	        $default_settings['field_defaults'][$row['site_id']][$row['channel_id']] = array(
	          'count_max'   => '',
	          'count_type'  => 'false',
	          'count_format'   => ''
	        );
	      }
	    }
		// No hooks selected, add in your own hooks installation code here.
		
		$hooks = array(
			'cp_js_end'     => 'cp_js_end',
			'cp_css_end'    => 'cp_css_end',
			'publish_form_channel_preferences' => 'publish_form_channel_preferences'
		);
		
		foreach ($hooks as $hook => $method)
		{
			$record_data = array('extension_id'  => '',
	          'class'     => __CLASS__,
	          'method'    => $method,
	          'hook'      => $hook,
	          'settings'  => serialize($settings),
	          'priority'  => 10,
	          'version'   => $this->version,
	          'enabled'   => "y"
	        );
			$qresult = $this->EE->db->insert('exp_extensions', $record_data);
		}
		
	}	

	// ----------------------------------------------------------------------

	/**
	 * Disable Extension
	 *
	 * This method removes information from the exp_extensions table
	 *
	 * @return void
	 */
	function disable_extension()
	{
		$this->EE->db->where('class', __CLASS__);
		$this->EE->db->delete('extensions');
	}

	// ----------------------------------------------------------------------

	/**
	 * Update Extension
	 *
	 * This function performs any necessary db updates when the extension
	 * page is visited
	 *
	 * @return 	mixed	void on update / false if none
	 */
	public function update_extension($current = '')
	{
		if ($current == '' OR $current == $this->version)
		{
			return FALSE;
		}
	}	
	
	
	// ----------------------------------------------------------------------
	
	
	/**
	 * This function loads the necessary JQuery plugin for the 'content_publish' controller.
	 */
	public function publish_form_channel_preferences($prefs)
	{
	    if($this->EE->extensions->last_call !== FALSE)
	    {
	      $prefs = $this->EE->extensions->last_call;
	    }
	
		$this->EE->cp->load_package_js('jquery.charcounter');
	
		return $prefs;
	}
	
	// ----------------------------------------------------------------------
	
	/**
	 * This function uses the 'cp_js_end' hook to add the needed JS to the Control Panel.
	 */
	function cp_js_end($js)
	{
		$this->EE->session->cache['mdesign'][MD_CC_addon_id]['require_scripts'] = TRUE;

	    // Check if we're not the only one using this hook
	    if($this->EE->extensions->last_call !== FALSE)
	    {
	      $js = $this->EE->extensions->last_call;
	    }

		// This block was made possible by Rob Sanchez (https://gist.github.com/1198583)
		$this->EE->load->helper('array');
		parse_str(parse_url(@$_SERVER['HTTP_REFERER'], PHP_URL_QUERY), $get);
		$controller = element('C', $get);
		
	    // if we are on a publish or edit page
	    if (isset($this->EE->session->cache['mdesign'][MD_CC_addon_id]['require_scripts']) === TRUE && ($controller == 'content_publish' || $controller == 'content_edit'))
		{
	    	// multiple-site support is commented for now.
			//if($this->settings['enable'] == 'y')
	        //{
	        	$ccstuff = '';
	        	$s = '';

		        $count_settings = $this->settings['field_defaults'];
		        // // Not really needed here, but it's a redundant act of good measure to keep out the bad characters.
		        $good = array("", "", "","", ""); 
		        // It's really only necessary in the $_POST of the settings, but using it here just for safety sake.
		        // These 2 items help keep bad information from being entered into the format string.
		        $bad  = array("\'", '\"', '\\', ";", ":"); 

				foreach ( $count_settings as $key => $val )
			    {
			        $count_max    = preg_replace("[^0-9]", "", $val['count_max']);
			        $count_type   = $val['count_type'];
			        $count_format = str_replace($bad, $good, $val['count_format']);

			    	// only output those that have something in them
			    	if ($count_max !== "")
					{
			    		// output the jquery for the field(s)
				      	$s .= '$("#field_id_'.$key.'").charCounter('.$count_max.','."\n";

				        // if user has entered something in count_format, set the format to the user's input
				        if ($count_format !== "")
				        {
				        	$s .= "\t".'{'."\n\t".'format: "'. $count_format .'",';
				        }
				        // otherwise, output the default format
				        else
				        {
				        	$s .= "\t".'{'."\n\t".'format: "%1/'. $count_max .' characters remaining",';
				        }

				        // add the softcount
				        $s .= "\n\t".'softcount: ' .$count_type."\n\t".'}'."\n".');'."\n\n";
			    	}
			    }

	        	if ( $s != '' ) 
	          	{
					$ccstuff .= '$(document).ready( function(){' . "\n\n" . $s . "\n" . '});'."\n\n"; 
	          	}
				
	        	// add the script string before the closing head tag
				$js .= $ccstuff;
	        // End of the multi-site enable if block.
			//}   
	    }
	    return $js;
		
	}
	
	
	// ----------------------------------------------------------------------
	
	
	/**
	 * This function uses the 'cp_css_end' hook to append the needed CSS styles to the Control Panel.
	 */
	function cp_css_end($css)
	{
		// Check if other extensions have used this hook.
		if($this->EE->extensions->last_call !== FALSE)
	    {
	      $css = $this->EE->extensions->last_call;
	    }
	
		$out = $this->settings['css']."\n";
    	$css .= $out;

		return $css;
	}
	
	
	// ----------------------------------------------------------------------
	
	
	/**
	 * This function checks to see if NSM Better Meta is installed.
	 */
	protected function check_for_nsm_better_meta()
	{
		$query = $this->EE->db->get_where('exp_extensions', array('class' => 'Nsm_better_meta_ext'), 1);
		if ($query->num_rows() > 0)
			return true;
		else
			return false;
	}

} // END Md_character_count_ext class



/* End of file ext.md_character_count.php */
/* Location: /system/expressionengine/third_party/md_character_count/ext.md_character_count.php */