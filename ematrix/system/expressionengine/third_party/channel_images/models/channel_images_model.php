<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Channel Images Model File
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2010 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com
 */
class Channel_images_model
{
	/**
	 * Constructor
	 *
	 * @access public
	 * @return void
	 */
	public function __construct()
	{
		// Creat EE Instance
		$this->EE =& get_instance();
		$this->site_id = $this->EE->config->item('site_id');
		$this->EE->load->library('image_helper');
	}

	// ********************************************************************************* //

	public function get_images($entry_id=FALSE, $field_id=FALSE, $params=array(), $tagdata='')
	{
		// Limit
		$limit = isset($params['limit']) ? $params['limit'] : 30;
		if (strpos($tagdata, LD.'/'."{$this->prefix}paginate".RD) === FALSE) $this->EE->db->limit($limit);

		// Sort
		$sort = (isset($params['sort']) === TRUE && $params['sort'] == 'desc') ? 'DESC': 'ASC';

		// Order by? (only if primary_only is false, since this would override our orderby)
		if (isset($params['cover_only']) === FALSE)
		{
			if (isset($params['orderby']) === FALSE) $params['orderby'] = 'image_order';

			if ($params['orderby'] == 'title') $this->EE->db->order_by('title', $sort);
			elseif ($params['orderby'] == 'random') $this->EE->db->order_by('RAND()', FALSE);
			else $this->EE->db->order_by('image_order', $sort);
		}

		// Category
		if (isset($params['category']) === TRUE)
		{
			$cat = $params['category'];

			// Multiple Categories?
			if (strpos($cat, '|') !== FALSE)
			{
				$cats = explode('|', $cat);
				$this->EE->db->where_in('category', $cats);
			}
			else
			{
				$this->EE->db->where('category', $cat);
			}
		}

		// Field ID
		if ($field_id !== FALSE)
		{
			if (is_array($field_id) === TRUE)
			{
				$this->EE->db->where_in('field_id', $field_id);
			}
			else
			{
				$this->EE->db->where('field_id', $field_id);
			}
		}

		// Offset
		if (isset($params['offset']) === TRUE)
		{
			$this->EE->db->limit($limit, $params['offset']);
		}

		// Do we need to skip the cover image?
        if (isset($params['skip_cover']) === TRUE)
        {
        	$this->EE->db->where('cover', 0);
        }

		// Cover Image
		if (isset($params['cover_only']) == TRUE && (isset($params['force_cover']) === FALSE OR $params['force_cover'] != 'yes'))
		{
			$this->EE->db->limit(1);
			$this->EE->db->order_by('cover DESC, image_order ASC');
		}
		elseif ( (isset($params['force_cover']) === TRUE && $params['force_cover'] == 'yes') )
		{
			$this->EE->db->where('cover', 1);
		}

		// Image ID?
		if (isset($params['image_id']) === TRUE)
		{
			$image_id = $params['image_id'];

			// Multiple File ID?
			if (strpos($image_id, '|') !== FALSE)
			{
				$ids = explode('|', $image_id);
				$this->EE->db->where_in('image_id', $ids);
			}
			else
			{
				$this->EE->db->limit(1);
				$this->EE->db->where('image_id', $image_id);
			}
		}

		// URL Title
		if (isset($params['image_url_title']) === TRUE)
		{
			$this->EE->db->limit(1);
			$this->EE->db->where('url_title', $params['image_url_title']);
		}

		// Entry ID
		if ($entry_id != FALSE)
		{
			$this->EE->db->where('entry_id', $entry_id);
		}

		// Channel?
		if (isset($params['channel']) === TRUE)
		{
			$cid = $this->get_channel_id($params['channel']);
			if (is_array($cid) === TRUE) $this->EE->db->where_in('channel_id', $cid);
			else $this->EE->db->where('channel_id', $cid);
		}

		// Channel ID?
		if (isset($params['channel_id']) === TRUE)
		{
			$channel_id = $params['channel_id'];

			// Multiple Channel ID?
			if (strpos($channel_id, '|') !== FALSE)
			{
				$ids = explode('|', $channel_id);
				$this->EE->db->where_in('channel_id', $ids);
			}
			else
			{
				$this->EE->db->where('channel_id', $channel_id);
			}
		}

		// Member ID?
		if (isset($params['member_id']) === TRUE)
		{
			$member_id = $params['member_id'];

			if ($member_id == 'CURRENT_USER')
			{
				$this->EE->db->where('member_id', $this->EE->session->userdata['member_id']);
			}
			elseif ($member_id != FALSE)
			{
				// Multiple Authors?
				if (strpos($member_id, '|') !== FALSE)
				{
					$cols = explode('|', $member_id);
					$this->EE->db->where_in('member_id', $cols);
				}
				else
				{
					$this->EE->db->where('member_id', $member_id);
				}
			}
		}

		// Better Workflow Draft?
		if (isset($this->EE->session->cache['ep_better_workflow']['is_draft']) && $this->EE->session->cache['ep_better_workflow']['is_draft'])
		{
			$this->EE->db->where('is_draft', 1);
		}
		else
		{
			$this->EE->db->where('is_draft', 0);
		}

		//----------------------------------------
		// Shoot the Query
		//----------------------------------------
		$this->EE->db->select('*');
		$this->EE->db->from('exp_channel_images');
		$query = $this->EE->db->get();

		$result = $query->result();
		$query->free_result();

		return $result;
	}

	// ********************************************************************************* //

	public function parse_template($entry_id=FALSE, $field_id=FALSE, $params=array(), $tagdata)
	{
		// Variable prefix
		$this->prefix = (isset($params['prefix']) === FALSE) ? 'image:' : $params['prefix'].':';

		// Set a default value of false for the is_draft flag
		$is_draft = 0;

		// If we are loading a draft into the publish page update the flag to true
		if (isset($this->session->cache['ep_better_workflow']['is_draft']) && $this->session->cache['ep_better_workflow']['is_draft'])
		{
			$is_draft = 1;
		}

		$temp_params = $params;

		// Lets remove all unwanted params
		unset($temp_params['entry_id'], $temp_params['url_title']);

		// Make our hash
		$hash = crc32(serialize($temp_params));

		if (isset($this->session->cache['channel_images']['images'][$hash]) == TRUE) $images = $this->session->cache['channel_images']['images'][$hash][$entry_id];
		else $images = $this->get_images($entry_id, $field_id, $params, $tagdata);

		// Any Images?
		if (count($images) === 0)
		{
			$this->EE->TMPL->log_item("CHANNEL IMAGES: No images found.");
			return $this->EE->image_helper->custom_no_results_conditional($this->prefix.'no_images', $tagdata);
		}

		$this->total_images = count($images);
		$limit = isset($params['limit']) ? $params['limit'] : 30;
		$paginate = FALSE;

		//----------------------------------------
		// Pagination
		//----------------------------------------
		if (preg_match('/'.LD."{$this->prefix}paginate(.*?)".RD."(.+?)".LD.'\/'."{$this->prefix}paginate".RD."/s", $tagdata, $match))
		{
			// Pagination variables
			$paginate		= TRUE;
			$paginate_data	= $match['2'];
			$current_page	= 0;
			$total_pages	= 1;
			$qstring		= $this->EE->uri->query_string;
			$uristr			= $this->EE->uri->uri_string;
			$pagination_links = '';
			$page_previous = '';
			$page_next = '';

			// We need to strip the page number from the URL for two reasons:
			// 1. So we can create pagination links
			// 2. So it won't confuse the query with an improper proper ID

			if (preg_match("#(^|/)CI(\d+)(/|$)#", $qstring, $match))
			{
				$current_page = $match['2'];
				$uristr  = $this->EE->functions->remove_double_slashes(str_replace($match['0'], '/', $uristr));
				$qstring = trim($this->EE->functions->remove_double_slashes(str_replace($match['0'], '/', $qstring)), '/');
			}

			// Remove the {paginate}
			$tagdata = preg_replace("/".LD."{$this->prefix}paginate.*?".RD.".+?".LD.'\/'."{$this->prefix}paginate".RD."/s", "", $tagdata);

			// What is the current page?

			$current_page = ($current_page == '' OR ($limit > 1 AND $current_page == 1)) ? 0 : $current_page;

			if ($current_page > $this->total_images)
			{
				$current_page = 0;
			}

			$t_current_page = floor(($current_page / $limit) + 1);
			$total_pages	= intval(floor($this->total_images / $limit));

			if ($this->total_images % $limit) $total_pages++;

			if ($this->total_images > $limit)
			{
				$this->EE->load->library('pagination');

				$deft_tmpl = '';

				if ($uristr == '')
				{
					if ($this->EE->config->item('template_group') == '')
					{
						$this->EE->db->select('group_name');
						$query = $this->EE->db->get_where('template_groups', array('is_site_default' => 'y'));

						$deft_tmpl = $query->row('group_name') .'/index';
					}
					else
					{
						$deft_tmpl  = $this->EE->config->item('template_group').'/';
						$deft_tmpl .= ($this->EE->config->item('template') == '') ? 'index' : $this->EE->config->item('template');
					}
				}

				$basepath = $this->EE->functions->remove_double_slashes($this->EE->functions->create_url($uristr, FALSE).'/'.$deft_tmpl);

				if (isset($params['paginate_base']) === TRUE)
				{
					// Load the string helper
					$this->EE->load->helper('string');

					$pbase = trim_slashes($params['paginate_base']);

					$pbase = str_replace("/index", "/", $pbase);

					if ( ! strstr($basepath, $pbase))
					{
						$basepath = $this->EE->functions->remove_double_slashes($basepath.'/'.$pbase);
					}
				}

				// Load Language
				$this->EE->lang->loadfile('channel_images');

				$config['first_url'] 	= rtrim($basepath, '/');
				$config['base_url']		= $basepath;
				$config['prefix']		= 'CI';
				$config['total_rows'] 	= $this->total_images;
				$config['per_page']		= $limit;
				$config['cur_page']		= $current_page;
				$config['suffix']		= '';
				$config['first_link'] 	= $this->EE->lang->line('ci:pag_first_link');
				$config['last_link'] 	= $this->EE->lang->line('ci:pag_last_link');
				$config['full_tag_open']		= '<span class="ci_paginate_links">';
				$config['full_tag_close']		= '</span>';
				$config['first_tag_open']		= '<span class="ci_paginate_first">';
				$config['first_tag_close']		= '</span>&nbsp;';
				$config['last_tag_open']		= '&nbsp;<span class="ci_paginate_last">';
				$config['last_tag_close']		= '</span>';
				$config['cur_tag_open']			= '&nbsp;<strong class="ci_paginate_current">';
				$config['cur_tag_close']		= '</strong>';
				$config['next_tag_open']		= '&nbsp;<span class="ci_paginate_next">';
				$config['next_tag_close']		= '</span>';
				$config['prev_tag_open']		= '&nbsp;<span class="ci_paginate_prev">';
				$config['prev_tag_close']		= '</span>';
				$config['num_tag_open']			= '&nbsp;<span class="ci_paginate_num">';
				$config['num_tag_close']		= '</span>';

				// Allows $config['cur_page'] to override
				$config['uri_segment'] = 0;

				$this->EE->pagination->initialize($config);
				$pagination_links = $this->EE->pagination->create_links();

				if ((($total_pages * $limit) - $limit) > $current_page)
				{
					$page_next = $basepath.$config['prefix'].($current_page + $limit).'/';
				}

				if (($current_page - $limit ) >= 0)
				{
					$page_previous = $basepath.$config['prefix'].($current_page - $limit).'/';
				}
			}
			else
			{
				$current_page = 0;
			}

			$images = array_slice($images, $current_page, $limit);
		}

		//----------------------------------------
		// Check for filesize (only for Local) Since it's an expensive operation
		//----------------------------------------
		$this->parse_filesize = FALSE;
		if (strpos($tagdata, LD.$this->prefix.'filesize') !== FALSE)
		{
			$this->parse_filesize = TRUE;
		}

		//----------------------------------------
		// Check for image_dimensions (only for Local) Since it's an expensive operation
		//----------------------------------------
		$this->parse_dimensions = FALSE;
		if (strpos($tagdata, LD.$this->prefix.'width') !== FALSE OR strpos($tagdata, LD.$this->prefix.'height') !== FALSE)
		{
			$this->parse_dimensions = TRUE;
		}

		//----------------------------------------
		// Switch=""
		//----------------------------------------
		$this->parse_switch = FALSE;
		$this->switch_matches = array();
		if ( preg_match_all( "/".LD."({$this->prefix}switch\s*=.+?)".RD."/is", $tagdata, $this->switch_matches ) > 0 )
		{
			$this->parse_switch = TRUE;

			// Loop over all matches
			foreach($this->switch_matches[0] as $key => $match)
			{
				$this->switch_vars[$key] = $this->EE->functions->assign_parameters($this->switch_matches[1][$key]);
				$this->switch_vars[$key]['original'] = $this->switch_matches[0][$key];
			}
		}

		// Encode HTML Entities
		$this->encode_html = FALSE;
		if (isset($params['encode_html_entities']) === TRUE && $params['encode_html_entities'] == 'yes') $this->encode_html = TRUE;

		// Decode HTML Entities
		$this->decode_html = FALSE;
		if (isset($params['decode_html_entities']) === TRUE && $params['decode_html_entities'] == 'yes') $this->decode_html = TRUE;

		//----------------------------------------
		// Locked URL?
		//----------------------------------------
		$this->locked_url = FALSE;
		if ( strpos($tagdata, $this->prefix.'locked_url') !== FALSE)
		{
			$this->locked_url = TRUE;

			// IP
			$this->IP = $this->EE->input->ip_address();

			// Grab Router URL
			$this->locked_act_url = $this->EE->image_helper->get_router_url('url', 'locked_image_url');
		}

		// SSL?
		$this->IS_SSL = $this->EE->image_helper->is_ssl();

		//----------------------------------------
		// Performance :)
		//----------------------------------------
		if (isset($this->session->cache['channel_images']['locations']) == FALSE)
		{
			$this->session->cache['channel_images']['locations'] = array();
		}

		$this->LOCS &= $this->session->cache['channel_images']['locations'];

		// Another Check, just to be sure
		if (is_array($this->LOCS) == FALSE) $this->LOCS = array();

		$OUT = '';

		//----------------------------------------
		// Loop over all Images
		//----------------------------------------
		foreach ($images as $count => $image)
		{
			$OUT .= $this->parse_single_image_row($count, $image, $tagdata);
		}

		//----------------------------------------
		// Add pagination to result
		//----------------------------------------
		if ($paginate == TRUE)
		{
			$paginate_data = str_replace(LD.$this->prefix.'current_page'.RD, 	$t_current_page, 	$paginate_data);
			$paginate_data = str_replace(LD.$this->prefix.'total_pages'.RD,		$total_pages,  		$paginate_data);
			$paginate_data = str_replace(LD.$this->prefix.'pagination_links'.RD,	$pagination_links,	$paginate_data);

			if (preg_match("/".LD."if {$this->prefix}previous_page".RD."(.+?)".LD.'\/'."if".RD."/s", $paginate_data, $match))
			{
				if ($page_previous == '')
				{
					 $paginate_data = preg_replace("/".LD."if {$this->prefix}previous_page".RD.".+?".LD.'\/'."if".RD."/s", '', $paginate_data);
				}
				else
				{
					$match['1'] = str_replace(array(LD."{$this->prefix}path".RD, LD."{$this->prefix}auto_path".RD), $page_previous, $match['1']);

					$paginate_data = str_replace($match['0'], $match['1'], $paginate_data);
				}
			}

			if (preg_match("/".LD."if {$this->prefix}next_page".RD."(.+?)".LD.'\/'."if".RD."/s", $paginate_data, $match))
			{
				if ($page_next == '')
				{
					 $paginate_data = preg_replace("/".LD."if {$this->prefix}next_page".RD.".+?".LD.'\/'."if".RD."/s", '', $paginate_data);
				}
				else
				{
					$match['1'] = str_replace(array(LD."{$this->prefix}path".RD, LD."{$this->prefix}auto_path".RD), $page_next, $match['1']);

					$paginate_data = str_replace($match['0'], $match['1'], $paginate_data);
				}
			}

			$position = (isset($params['paginate']) === TRUE) ? $params['paginate'] : '';

			switch ($position)
			{
				case "top"	: $OUT  = $paginate_data.$OUT;
					break;
				case "both"	: $OUT  = $paginate_data.$OUT.$paginate_data;
					break;
				default		: $OUT .= $paginate_data;
					break;
			}
		}

		// Apply Backspace
		$backspace = (isset($params['backspace']) === TRUE) ? $params['backspace'] : 0;
		$OUT = ($backspace > 0) ? substr($OUT, 0, - $backspace): $OUT;

		return $OUT;

	}

	// ********************************************************************************* //

	public function parse_single_image_row($count, $image, $tagdata)
	{
		$out = '';
		// Check for linked image!
		if ($image->link_entry_id > 0)
		{
			$image->entry_id = $image->link_entry_id;
			$image->field_id = $image->link_field_id;
		}

		// Get Field Settings!
		$settings = $this->get_field_settings($image->field_id);
		$settings = $settings['channel_images'];

		//----------------------------------------
		// Load Location
		//----------------------------------------
		if (isset($this->LOCS[$image->field_id]) === FALSE)
		{
			$location_type = $settings['upload_location'];
			$location_class = 'CI_Location_'.$location_type;
			$location_settings = $settings['locations'][$location_type];

			// Load Main Class
			if (class_exists('Image_Location') == FALSE) require PATH_THIRD.'channel_images/locations/image_location.php';

			// Try to load Location Class
			if (class_exists($location_class) == FALSE)
			{
				$location_file = PATH_THIRD.'channel_images/locations/'.$location_type.'/'.$location_type.'.php';
				require $location_file;
			}

			// Init!
			$this->LOCS[$image->field_id] = new $location_class($location_settings);
		}

		//----------------------------------------
		// Check for Mime Type
		//----------------------------------------
		if ($image->mime == FALSE)
		{
			// Mime type
			$image->mime = 'image/jpeg';
			if ($image->extension == 'png') $filemime = 'image/png';
			elseif ($image->extension == 'gif') $filemime = 'image/gif';
		}

		//----------------------------------------
		// Image URL
		//----------------------------------------
		$image_url = $this->LOCS[$image->field_id]->parse_image_url($image->entry_id, $image->filename);

		// Did something go wrong?
		if ($image_url == FALSE)
		{
			$this->EE->TMPL->log_item('CHANNEL IMAGES: Image URL Failed for: ' . $image->entry_id.'/'.$image->filename);
			return '';
		}

		// SSL?
		if ($this->IS_SSL == TRUE) $image_url = str_replace('http://', 'https://', $image_url);

		//----------------------------------------
		// Filedir (local only)
		//----------------------------------------
		$filedir = '';
		if ($settings['upload_location'] == 'local')
		{
			$filedir = str_replace($image->entry_id.'/'.$image->filename, '', $image_url);
		}

		//----------------------------------------
		// Encode/Decode fields
		//----------------------------------------
		if ($this->encode_html)
		{
			$image->title = htmlentities($image->title, ENT_QUOTES);
			$image->description = htmlentities($image->description, ENT_QUOTES);
			$image->cifield_1 = htmlentities($image->cifield_1, ENT_QUOTES);
			$image->cifield_2 = htmlentities($image->cifield_2, ENT_QUOTES);
			$image->cifield_3 = htmlentities($image->cifield_3, ENT_QUOTES);
			$image->cifield_4 = htmlentities($image->cifield_4, ENT_QUOTES);
			$image->cifield_5 = htmlentities($image->cifield_5, ENT_QUOTES);
		}

		if ($this->decode_html)
		{
			$image->title = html_entity_decode($image->title, ENT_QUOTES);
			$image->description = html_entity_decode($image->description, ENT_QUOTES);
			$image->cifield_1 = html_entity_decode($image->cifield_1, ENT_QUOTES);
			$image->cifield_2 = html_entity_decode($image->cifield_2, ENT_QUOTES);
			$image->cifield_3 = html_entity_decode($image->cifield_3, ENT_QUOTES);
			$image->cifield_4 = html_entity_decode($image->cifield_4, ENT_QUOTES);
			$image->cifield_5 = html_entity_decode($image->cifield_5, ENT_QUOTES);
		}

		$vars = array();
		$vars[$this->prefix.'count'] = $count + 1;
		$vars[$this->prefix.'total'] = $this->total_images;
		$vars[$this->prefix.'entry_id'] = $image->entry_id;
		$vars[$this->prefix.'channel_id'] = $image->channel_id;
		$vars[$this->prefix.'title'] = $image->title;
		$vars[$this->prefix.'url_title'] = $image->url_title;
		$vars[$this->prefix.'description'] = $image->description;
		$vars[$this->prefix.'category'] = $image->category;
		$vars[$this->prefix.'filename'] = $image->filename;
		$vars[$this->prefix.'id'] = $image->image_id;
		$vars[$this->prefix.'cover'] = $image->cover;
		$vars[$this->prefix.'upload_date'] = $image->upload_date;
		$vars[$this->prefix.'url'] = $image_url;
		$vars[$this->prefix.'secure_url'] = str_replace('http://', 'https://', $image_url);
		$vars[$this->prefix.'file_path'] = $filedir;
		$vars[$this->prefix.'file_path_secure'] = str_replace('http://', 'https://', $filedir);
		$vars[$this->prefix.'mimetype'] = $image->mime;
		$vars[$this->prefix.'field:1'] = $image->cifield_1;
		$vars[$this->prefix.'field:2'] = $image->cifield_2;
		$vars[$this->prefix.'field:3'] = $image->cifield_3;
		$vars[$this->prefix.'field:4'] = $image->cifield_4;
		$vars[$this->prefix.'field:5'] = $image->cifield_5;


		//----------------------------------------
		// Check for filesize, Since it's an expensive operation
		//----------------------------------------
		if ($this->parse_filesize == TRUE)
		{
			// If filesize is not defined, lets find it (only for local files)
			if ($image->filesize == FALSE && $settings['upload_location'] == 'local')
			{
				$filepath = $this->LOCS[$image->field_id]->get_location_prefs($settings['locations']['local']['location']);
				$filepath = $filepath['server_path']  . $image->entry_id . '/' . $image->filename;
				$image->filesize = @filesize($filepath);
			}
			elseif ($image->filesize == FALSE)
			{
				$image->filesize = 0;
			}

			$vars[$this->prefix.'filesize'] = $this->EE->image_helper->format_bytes($image->filesize);
			$vars[$this->prefix.'filesize_bytes'] = $image->filesize;
		}

		//----------------------------------------
		// Check for image_dimensions, Since it's an expensive operation
		//----------------------------------------
		if ($this->parse_dimensions == TRUE)
		{
			// If filesize is not defined, lets find it (only for local files)
			if ($image->width == FALSE && $settings['upload_location'] == 'local')
			{
				$filepath = $this->LOCS[$image->field_id]->get_location_prefs($settings['locations']['local']['location']);
				$filepath = $filepath['server_path']  . $image->entry_id . '/' . $image->filename;
				$imginfo = @getimagesize($filepath);
				$image->width = $imginfo[0];
				$image->height = $imginfo[1];
			}
			elseif ($image->width == FALSE)
			{
				$image->width = '';
				$image->height = '';
			}

			$vars[$this->prefix.'width'] = $image->width;
			$vars[$this->prefix.'height'] = $image->height;
		}

		// -----------------------------------------
		// Locked URL
		// -----------------------------------------
		if ($this->locked_url == TRUE)
		{
			$locked = array('image_id' => $image->image_id, 'size'=>'', 'time' => $this->EE->localize->now + 600, 'ip' => $this->IP);
			$vars[$this->prefix.'locked_url'] = $this->locked_act_url . '&key=' . base64_encode(serialize($locked));
		}


		$temp = $this->EE->TMPL->parse_variables_row($tagdata, $vars);
		$temp = $this->parse_size_vars($temp, $settings, $image);

		// -----------------------------------------
		// Parse Switch {switch="one|twoo"}
		// -----------------------------------------
		if ($this->parse_switch)
		{
			// Loop over all switch variables
			foreach($this->switch_vars as $switch)
			{
				$sw = '';

				// Does it exist? Just to be sure
				if ( isset( $switch[$this->prefix.'switch'] ) !== FALSE )
				{
					$sopt = explode("|", $switch[$this->prefix.'switch']);
					$sw = $sopt[(($count) + count($sopt)) % count($sopt)];
				}

				$temp = str_replace($switch['original'], $sw, $temp);
			}
		}

		return $temp;
	}

	// ********************************************************************************* //

	public function parse_size_vars($OUT, $settings, $image)
	{
		// Get Extension
		$extension = '.' . $image->extension;

		if (isset($settings['action_groups']) == FALSE OR empty($settings['action_groups']) == TRUE) return $OUT;

		//----------------------------------------
		// Size Metadata!
		//----------------------------------------
		$metadata = array();
		if ($image->sizes_metadata != FALSE)
		{
			$temp = explode('/', $image->sizes_metadata);
			foreach($temp as $row)
			{
				if ($row == FALSE) continue;
				$temp2 = explode('|', $row);

				$metadata[$temp2[0]] = array('width' => $temp2[1], 'height'=>$temp2[2], 'size'=>$temp2[3]);
			}
		}

		// -----------------------------------------
		// Loop over all sizes!
		// -----------------------------------------
		foreach ($settings['action_groups'] as $group)
		{
			$name = strtolower($group['group_name']);
			$newname = str_replace($extension, "__{$name}{$extension}", $image->filename);

			// -----------------------------------------
			// Image URL (Size)
			// -----------------------------------------
			$image_url = $this->LOCS[$image->field_id]->parse_image_url($image->entry_id, $newname);

			// Did something go wrong?
			if ($image_url == FALSE)
			{
				$this->EE->TMPL->log_item('CHANNEL IMAGES: Image URL Failed for: ' . $image->entry_id.'/'.$image->filename);
				continue;
			}

			// SSL?
			if ($this->IS_SSL == TRUE) $image_url = str_replace('http://', 'https://', $image_url);

			$OUT = str_replace(LD.$this->prefix.'filename:'.$name.RD, $newname, $OUT);
			$OUT = str_replace(LD.$this->prefix.'url:'.$name.RD, $image_url, $OUT);
			$OUT = str_replace(LD.$this->prefix.'secure_url:'.$name.RD, str_replace('http://', 'https://', $image_url), $OUT);

			// -----------------------------------------
			// Locked URLS (Size)
			// -----------------------------------------
			if ($this->locked_url == TRUE)
			{
				$locked = array('image_id' => $image->image_id, 'size'=>$name, 'time' => $this->EE->localize->now + 3600, 'ip' => $this->IP);
				$OUT = str_replace(LD.$this->prefix.'locked_url:'.$name.RD, ($this->locked_act_url . '&key=' . base64_encode(serialize($locked))), $OUT);
			}

			//----------------------------------------
			// Check for filesize, Since it's an expensive operation
			//----------------------------------------
			if ($this->parse_filesize == TRUE)
			{
				// If filesize is not defined, lets find it (only for local files)
				if (isset($metadata[$name]) == FALSE && $settings['upload_location'] == 'local')
				{
					$filepath = $this->LOCS[$image->field_id]->get_location_prefs($settings['locations']['local']['location']);
					$filepath = $filepath['server_path']  . $image->entry_id . '/' . $newname;
					$metadata[$name]['size'] = @filesize($filepath);
				}

				if (isset($metadata[$name]['size']) === FALSE) $metadata[$name]['size'] = 0;

				$OUT = str_replace(LD.$this->prefix.'filesize:'.$name.RD, $this->EE->image_helper->format_bytes($metadata[$name]['size']), $OUT);
				$OUT = str_replace(LD.$this->prefix.'filesize_bytes:'.$name.RD, $metadata[$name]['size'], $OUT);
			}

			//----------------------------------------
			// Check for image_dimensions, Since it's an expensive operation
			//----------------------------------------
			if ($this->parse_dimensions == TRUE)
			{
				// If filesize is not defined, lets find it (only for local files)
				if (isset($metadata[$name]) === FALSE && $settings['upload_location'] == 'local')
				{
					$filepath = $this->LOCS[$image->field_id]->get_location_prefs($settings['locations']['local']['location']);
					$filepath = $filepath['server_path']  . $image->entry_id . '/' . $newname;
					$imginfo = @getimagesize($filepath);
					$metadata[$name]['width'] = $imginfo[0];
					$metadata[$name]['height'] = $imginfo[1];
				}

				if (isset($metadata[$name]['width']) === FALSE) $metadata[$name]['width'] = '';
				if (isset($metadata[$name]['height']) === FALSE) $metadata[$name]['height'] = '';

				$OUT = str_replace(LD.$this->prefix.'width:'.$name.RD, $metadata[$name]['width'], $OUT);
				$OUT = str_replace(LD.$this->prefix.'height:'.$name.RD, $metadata[$name]['height'], $OUT);
			}
		}

		return $OUT;
	}

	// ********************************************************************************* //

	public function pre_fetch_data($entry_ids=array(), $params=array())
	{
		if (empty($entry_ids) == TRUE) return;

		// Lets remove all unwanted params
		unset($params['entry_id'], $params['url_title']);

		// Make our hash
		$hash = crc32(serialize($params));

		$params['entry_id'] = implode('|', $entry_ids);

		// Grab all images
		$this->session->cache['channel_images']['images'][$hash] = array();

		$result = $this->get_images($params);

		if ($result == FALSE) return;

		foreach ($result as $row)
		{
			$this->session->cache['channel_images']['images'][$hash][ $row->entry_id ][] = $row;
		}
	}

	// ********************************************************************************* //

	/**
	 * Get Settings of a field
	 *
	 * @param int $field_id
	 * @access public
	 * @return array - Field Settings
	 */
	public function get_field_settings($field_id)
	{
		if (isset($this->session->cache['channel_images']['field_settings'][$field_id]) == FALSE)
		{
			$query = $this->EE->db->select('field_settings')->from('exp_channel_fields')->where('field_id', $field_id)->get();
			if ($query->num_rows() == 0) return FALSE;
			$this->session->cache['channel_images']['field_settings'][$field_id] = unserialize(base64_decode($query->row('field_settings')));
		}

		return $this->session->cache['channel_images']['field_settings'][$field_id];
	}

	// ********************************************************************************* //

	/**
	 * Get Field ID
	 * Since we moved to Field Based Settings, our legacy versions where not storing field_id's
	 * so we need to somehow get it from the channel_id
	 *
	 * @param object $image
	 * @access public
	 * @return int - The FieldID
	 */
	public function get_field_id($image)
	{
		// Easy way..
		if ($image->field_id > 0)
		{
			return $image->field_id;
		}

		// Hard way
		if (isset($this->session->cache['Channel_Images']['Channel2Field'][$image->channel_id]) == FALSE)
		{
			// Then we need to use the Channel ID :(
			$query = $this->EE->db->query("SELECT cf.field_id FROM exp_channel_fields AS cf
											LEFT JOIN exp_channels AS c ON c.field_group = cf.group_id
											WHERE c.channel_id = {$image->channel_id} AND cf.field_type = 'channel_images'");
			if ($query->num_rows() == 0)
			{
				$query->free_result();
				return 0;
			}

			$this->session->cache['Channel_Images']['Channel2Field'][$image->channel_id] = $query->row('field_id');
			$field_id = $query->row('field_id');

			$query->free_result();
		}
		else
		{
			$field_id = $this->session->cache['Channel_Images']['Channel2Field'][$image->channel_id];
		}

		return $field_id;
	}

	// ********************************************************************************* //

	public function get_channel_id($channels)
	{
		if ($channels == FALSE) return FALSE;

		// Multiple Channels?
		if (strpos($channels, '|') !== FALSE)
		{
			$channels = explode('|', $channels);
			$lookup = array();
			$return = array();

			foreach ($channels as $key => $value)
			{
				// Did we Cache this already?
				if (isset($this->EE->session->cache['devdemon']['channel_to_id'][$value]) === TRUE)
				{
					$return[] = $this->EE->session->cache['devdemon']['channel_to_id'][$value];
					continue;
				}

				$lookup[] = "'".$value."'";
			}

			if (empty($lookup) === FALSE)
			{
				$query = $this->EE->db->query("SELECT channel_id, channel_name FROM exp_channels WHERE channel_name IN ({$lookup}) ");
				if ($query->num_rows() == 0) return FALSE;

				foreach ($query->result() as $row)
				{
					$this->EE->session->cache['devdemon']['channel_to_id'][$row->channel_name] = $row->channel_id;
					$return[] = $row->channel_id;
				}
			}

			if (empty($channels) === TRUE) return FALSE;
			return $channels;
		}
		else
		{
			// Did we Cache this already?
			if (isset($this->EE->session->cache['devdemon']['channel_to_id'][$channels]) === FALSE)
			{
				$query = $this->EE->db->query("SELECT channel_id FROM exp_channels WHERE channel_name = '{$channels}' ");
				if ($query->num_rows() == 0) return FALSE;

				$this->EE->session->cache['devdemon']['channel_to_id'][$channels] = $query->row('channel_id');
			}

			return $this->EE->session->cache['devdemon']['channel_to_id'][$channels];
		}
	}

	// ********************************************************************************* //

	public function get_fields_from_params($params)
	{
		$fields = array();

		if (isset($params['field_id']) === TRUE)
		{
			// Multiple fields?
			if (strpos($params['field_id'], '|') !== FALSE)
			{
				return explode('|', $params['field_id']);
			}
			else
			{
				return $params['field_id'];
			}
		}

		if (isset($params['field']) === TRUE)
		{
			// Multiple fields?
			if (strpos($params['field'], '|') !== FALSE)
			{
				$pfields = explode('|', $params['field']);

				foreach($pfields as $field)
				{
					if (isset($this->EE->session->cache['channel']['custom_channel_fields'][$this->site_id][ $field ]) === FALSE)
					{
						// Grab the field id
						$query = $this->EE->db->query("SELECT field_id FROM exp_channel_fields WHERE field_name = '{$field}' AND site_id = {$this->site_id} ");
						if ($query->num_rows() == 0)
						{
							if (isset($this->EE->TMPL) === TRUE) $this->EE->TMPL->log_item('CHANNEL_IMAGES: Could not find field : ' . $field);
							return FALSE;
						}
						else
						{
							$this->EE->session->cache['channel']['custom_channel_fields'][$this->site_id][ $field ] = $query->row('field_id');
						}
					}

					$fields[] = $this->EE->session->cache['channel']['custom_channel_fields'][$this->site_id][ $field ];
				}
			}
			else
			{
				if (isset($this->EE->session->cache['channel']['custom_channel_fields'][$this->site_id][ $params['field'] ]) === FALSE)
				{
					// Grab the field id
					$query = $this->EE->db->query("SELECT field_id FROM exp_channel_fields WHERE field_name = '{$params['field']}' AND site_id = {$this->site_id} ");
					if ($query->num_rows() == 0)
					{
						if (isset($this->EE->TMPL) === TRUE) $this->EE->TMPL->log_item('CHANNEL_IMAGES: Could not find field : ' . $params['field']);
						return FALSE;
					}
					else
					{
						$this->EE->session->cache['channel']['custom_channel_fields'][$this->site_id][ $params['field'] ] = $query->row('field_id');
					}
				}

				return $this->EE->session->cache['channel']['custom_channel_fields'][$this->site_id][ $params['field'] ];
			}
		}

		if (empty($fields) === TRUE) return FALSE;

		return $fields;
	}

	// ********************************************************************************* //

	// TEMP SOLUTION FOR EE 2.1.1 SIGH!!!
	public function _assign_libraries()
	{

	}

} // END CLASS

/* End of file Channel_images_model.php  */
/* Location: ./system/expressionengine/third_party/channel_images/models/Channel_images_model.php */
