<?php if (!defined('BASEPATH')) die('No direct script access allowed');

// include config file
include PATH_THIRD.'channel_videos/config'.EXT;

/**
 * Channel Videos Module FieldType
 *
 * @package			DevDemon_ChannelVideos
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 * @see				http://expressionengine.com/user_guide/development/fieldtypes.html
 */
class Channel_videos_ft extends EE_Fieldtype
{
	/**
	 * Field info (Required)
	 *
	 * @var array
	 * @access public
	 */
	var $info = array(
		'name' 		=> CHANNEL_VIDEOS_NAME,
		'version'	=> CHANNEL_VIDEOS_VERSION,
	);

	/**
	 * The field settings array
	 *
	 * @access public
	 * @var array
	 */
	public $settings = array();


	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		if (version_compare(APP_VER, '2.1.4', '>')) { parent::__construct(); } else { parent::EE_Fieldtype(); }

		if ($this->EE->input->cookie('cp_last_site_id')) $this->site_id = $this->EE->input->cookie('cp_last_site_id');
		else if ($this->EE->input->get_post('site_id')) $this->site_id = $this->EE->input->get_post('site_id');
		else $this->site_id = $this->EE->config->item('site_id');

		$this->EE->load->add_package_path(PATH_THIRD . 'channel_videos/');
		$this->EE->lang->loadfile('channel_videos');
		$this->EE->load->library('channel_videos_helper');
		$this->EE->config->load('cv_config');
		$this->EE->channel_videos_helper->define_theme_url();
	}

	// ********************************************************************************* //

	/**
	 * Display the field in the publish form
	 *
	 * @access public
	 * @param $data String Contains the current field data. Blank for new entries.
	 * @return String The custom field HTML
	 */
	public function display_field($data)
	{
		//----------------------------------------
		// Global Vars
		//----------------------------------------
		$vData = array();
		$vData['field_name'] = $this->field_name;
		$vData['field_id'] = $this->field_id;
		$vData['site_id'] = $this->site_id;
		$vData['channel_id'] = ($this->EE->input->get_post('channel_id') != FALSE) ? $this->EE->input->get_post('channel_id') : 0;
		$vData['entry_id'] = ($this->EE->input->get_post('entry_id') != FALSE) ? $this->EE->input->get_post('entry_id') : FALSE;
		$vData['videos'] = array();
		$vData['total_videos'] = 0;
		$vData['entry_id'] = $this->EE->input->get_post('entry_id');

		// Post DATA?
		if (isset($_POST[$this->field_name])) {
			$data = $_POST[$this->field_name];
		}

		//----------------------------------------
		// Add Global JS & CSS & JS Scripts
		//----------------------------------------
		$this->EE->channel_videos_helper->mcp_js_css('gjs');
		$this->EE->channel_videos_helper->mcp_js_css('css', 'channel_videos_pbf.css?v='.CHANNEL_VIDEOS_VERSION, 'channel_videos', 'main');
		$this->EE->channel_videos_helper->mcp_js_css('css', 'jquery.colorbox.css?v='.CHANNEL_VIDEOS_VERSION, 'jquery', 'colorbox');
		$this->EE->channel_videos_helper->mcp_js_css('js', 'jquery.colorbox.js?v='.CHANNEL_VIDEOS_VERSION, 'jquery', 'colorbox');
		$this->EE->channel_videos_helper->mcp_js_css('js', 'channel_videos_pbf.js?v='.CHANNEL_VIDEOS_VERSION, 'channel_videos', 'main');

		$this->EE->cp->add_js_script(array('ui' => array('sortable')));

		//----------------------------------------
		// Settings
		//----------------------------------------
		$settings = $this->settings;
		//$settings = (isset($settings['channel_videos']) == TRUE) ? $settings['channel_videos'] : array();
		$defaults = $this->EE->config->item('cv_defaults');

		// Columns?
		if (isset($settings['columns']) == FALSE) $settings['columns'] = $this->EE->config->item('cv_columns');

		// Limit Videos
		if (isset($settings['video_limit']) == FALSE OR trim($settings['video_limit']) == FALSE) $settings['video_limit'] = 999999;


		$vData['settings'] = array_merge($defaults, $settings);
		if (isset($vData['settings']['cv_services']) == false) {
			$vData['settings']['cv_services'] = array('youtube', 'vimeo');
		}

		/*
		// Sometimes you forget to fill in field
		// and you will send back to the form
		// We need to fil lthe values in again.. *Sigh* (anyone heard about AJAX!)
		if (is_array($data) == TRUE && isset($data['tags']) == TRUE)
		{
			foreach ($data['tags'] as $tag)
			{
				$vData['assigned_tags'][] = $tag;
			}

			return $this->EE->load->view('pbf_field', $vData, TRUE);
		}
		*/

		//----------------------------------------
		// JSON
		//----------------------------------------
		$vData['json'] = array();
		$vData['json']['layout'] = (isset($settings['cv_layout']) == TRUE) ? $settings['cv_layout'] : 'table';
		$vData['json']['field_name'] = $this->field_name;
		$vData['json']['services'] = $settings['cv_services'];
		$vData['json'] = $this->EE->channel_videos_helper->generate_json($vData['json']);

		$vData['layout'] = (isset($settings['cv_layout']) == TRUE) ? $settings['cv_layout'] : 'table';

		//----------------------------------------
		// Auto-Saved Entry?
		//----------------------------------------
		if ($this->EE->input->get('use_autosave') == 'y')
		{
			$vData['entry_id'] = FALSE;
			$old_entry_id = $this->EE->input->get_post('entry_id');
			$query = $this->EE->db->select('original_entry_id')->from('exp_channel_entries_autosave')->where('entry_id', $old_entry_id)->get();
			if ($query->num_rows() > 0 && $query->row('original_entry_id') > 0) $vData['entry_id'] = $query->row('original_entry_id');
		}

		// Grab Assigned Videos
		if ($vData['entry_id'] != FALSE)
		{
			// Grab all the files from the DB
			$this->EE->db->select('*');
			$this->EE->db->from('exp_channel_videos');
			$this->EE->db->where('entry_id', $vData['entry_id']);
			$this->EE->db->where('field_id', $vData['field_id']);
			$this->EE->db->order_by('video_order');
			$query = $this->EE->db->get();

			$vData['videos'] = $query->result();
			$vData['total_videos'] = $query->num_rows();
			$query->free_result();
		}



		return $this->EE->load->view('pbf_field', $vData, TRUE);
	}

	// ********************************************************************************* //

	/**
	 * Validates the field input
	 *
	 * @param $data Contains the submitted field data.
	 * @access public
	 * @return mixed Must return TRUE or an error message
	 */
	public function validate($data)
	{
		// Is this a required field?
		if ($this->settings['field_required'] == 'y')
		{
			if (isset($data['videos']) == FALSE OR empty($data['videos']) == TRUE)
			{
				return $this->EE->lang->line('video:required_field');
			}
		}

		return TRUE;
	}

	// ********************************************************************************* //

	/**
	 * Preps the data for saving
	 *
	 * @param $data Contains the submitted field data.
	 * @return string Data to be saved
	 */
	public function save($data)
	{
		$this->EE->session->cache['ChannelVideos']['FieldData'][$this->field_id] = $data;

		if (isset($data['videos']) == FALSE)
		{
			return '';
		}
		else
		{
			return 'ChannelVideos';
		}
	}

	// ********************************************************************************* //

	/**
	 * Handles any custom logic after an entry is saved.
	 * Called after an entry is added or updated.
	 * Available data is identical to save, but the settings array includes an entry_id.
	 *
	 * @param $data Contains the submitted field data. (Returned by save())
	 * @access public
	 * @return void
	 */
	public function post_save($data)
	{
		$this->EE->load->library('channel_videos_helper');

		if (isset($this->EE->session->cache['ChannelVideos']['FieldData'][$this->field_id]) == FALSE) return;

		// -----------------------------------------
		// Some Vars
		// -----------------------------------------
		$data = $this->EE->session->cache['ChannelVideos']['FieldData'][$this->field_id];
		$entry_id = $this->settings['entry_id'];
		$channel_id = $this->EE->input->post('channel_id');
		$field_id = $this->field_id;

		// -----------------------------------------
		// Grab all Videos From DB
		// -----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_channel_videos');
		$this->EE->db->where('entry_id', $entry_id);
		$this->EE->db->where('field_id', $field_id);
		$query = $this->EE->db->get();


		// Check for videos
		if (isset($data['videos']) == FALSE OR is_array($data['videos']) == FALSE)
		{
			$data['videos'] = array();
		}

		if ($query->num_rows() > 0)
		{
			// Not fresh, lets see whats new.
			foreach ($data['videos'] as $order => $video)
			{
				// Check for cover first
				//if (isset($file['cover']) == FALSE) $file['cover'] = 0;

				if (isset($video['video_id']) == FALSE) // $this->EE->channel_videos_helper->in_multi_array($video['data']->hash_id, $query->result_array()) === FALSE)
				{
					$video = $this->EE->channel_videos_helper->decode_json($video['data']);

					// -----------------------------------------
					// New Video!
					// -----------------------------------------
					$data = array(	'site_id'	=>	$this->site_id,
									'entry_id'	=>	$entry_id,
									'channel_id'=>	$channel_id,
									'field_id'	=>	$field_id,
									'service'	=>	$video->service,
									'service_video_id'	=>	$video->service_video_id,
									'video_title'	=>	$video->video_title,
									'video_desc'	=>	$video->video_desc,
									'video_username'=>	$video->video_username,
									'video_author'	=>	$video->video_author,
									'video_author_id'=>	$video->video_author_id,
									'video_date'	=>	$video->video_date,
									'video_views'	=>	$video->video_views,
									'video_duration'=>	$video->video_duration,
									'video_url'		=>	$video->video_url,
									'video_img_url'	=>	$video->video_img_url,
									'video_order'	=>	$order,
									'video_cover'	=>	0,
								);

					$this->EE->db->insert('exp_channel_videos', $data);
				}
				else
				{
					// Check for duplicate Videos!
					if (isset($video['video_id']) != FALSE)
					{
						// Update Video
						$data = array(	'video_order'	=>	$order,
										'video_cover'	=>	0,
									);

						$this->EE->db->update('exp_channel_videos', $data, array('video_id' =>$video['video_id']));
					}
				}
			}
		}
		else
		{
			foreach ($data['videos'] as $order => $video)
			{
				$video = $this->EE->channel_videos_helper->decode_json($video['data']);

				// Check for cover first
				//if (isset($file['cover']) == FALSE) $file['cover'] = 0;

				// -----------------------------------------
				// New Video
				// -----------------------------------------
				$data = array(	'site_id'	=>	$this->site_id,
								'entry_id'	=>	$entry_id,
								'channel_id'=>	$channel_id,
								'field_id'	=>	$field_id,
								'service'	=>	$video->service,
								'service_video_id'	=>	$video->service_video_id,
								'video_title'	=>	$video->video_title,
								'video_desc'	=>	$video->video_desc,
								'video_username'=>	$video->video_username,
								'video_author'	=>	$video->video_author,
								'video_author_id'=>	$video->video_author_id,
								'video_date'	=>	$video->video_date,
								'video_views'	=>	$video->video_views,
								'video_duration'=>	$video->video_duration,
								'video_url'		=>	$video->video_url,
								'video_img_url'	=>	$video->video_img_url,
								'video_order'	=>	$order,
								'video_cover'	=>	0,
							);

				$this->EE->db->insert('exp_channel_videos', $data);
			}
		}

		return;
	}

	// ********************************************************************************* //

	/**
	 * Handles any custom logic after an entry is deleted.
	 * Called after one or more entries are deleted.
	 *
	 * @param $ids array is an array containing the ids of the deleted entries.
	 * @access public
	 * @return void
	 */
	public function delete($ids)
	{
		foreach ($ids as $item_id)
		{
			$this->EE->db->where('entry_id', $item_id);
			$this->EE->db->delete('exp_channel_videos');
		}
	}

	// ********************************************************************************* //

	/**
	 * Display the settings page. The default ExpressionEngine rows can be created using built in methods.
	 * All of these take the current $data and the fieltype name as parameters:
	 *
	 * @param $data array
	 * @access public
	 * @return void
	 */
	public function display_settings($data)
	{
		// Does our settings exist?
		if (isset($data['cv_services']) == TRUE)
		{
			if (is_string($data['cv_services']) == TRUE) $d = array($data['cv_services']);
			elseif (is_array($data['cv_services']) == TRUE) $d = $data['cv_services'];
			else $d = array('youtube', 'vimeo');
		}
		else
		{
			$d = array('youtube', 'vimeo');
		}

		$row  = form_checkbox('cv_services[]', 'youtube', in_array('youtube', $d)) .NBS.NBS. lang('cv:service:youtube').NBS.NBS;
		$row .= form_checkbox('cv_services[]', 'vimeo', in_array('vimeo', $d)) .NBS.NBS. lang('cv:service:vimeo').NBS.NBS;
		//$row .= form_checkbox('cv_services[]', 'revver', in_array('revver', $d)) .NBS.NBS. lang('video:service:revver');
		$this->EE->table->add_row( lang('cv:services_option', 'cv_services'), $row);

		$layout = (isset($data['cv_layout']) == TRUE) ? $data['cv_layout'] : 'table';

		$row  = form_radio('cv_layout', 'table', (($layout == 'table') ? TRUE : FALSE)) .NBS.NBS. lang('cv:layout:table').NBS.NBS;
		$row .= form_radio('cv_layout', 'tiles', (($layout == 'tiles') ? TRUE : FALSE)) .NBS.NBS. lang('cv:layout:tiles').NBS.NBS;
		//$row .= form_checkbox('cv_services[]', 'revver', in_array('revver', $d)) .NBS.NBS. lang('video:service:revver');

		$this->EE->table->add_row( lang('cv:layout', 'cv_services'), $row);
		$this->EE->table->add_row('ACT URL', '<a href="'.$this->EE->channel_videos_helper->get_router_url().'" target="_blank">'.$this->EE->channel_videos_helper->get_router_url().'</a>');
	}

	// ********************************************************************************* //

	/**
	 * Save the fieldtype settings.
	 *
	 * @param $data array Contains the submitted settings for this field.
	 * @access public
	 * @return array
	 */
	public function save_settings($data)
	{
		return array(
			'cv_services' => $this->EE->input->post('cv_services'),
			'cv_layout' => $this->EE->input->post('cv_layout'),
		);
	}

	// ********************************************************************************* //
}

/* End of file ft.channel_videos.php */
/* Location: ./system/expressionengine/third_party/channel_videos/ft.channel_videos.php */
