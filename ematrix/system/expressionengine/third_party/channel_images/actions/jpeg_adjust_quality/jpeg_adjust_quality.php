<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images GREYSCALE action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class CI_Action_jpeg_adjust_quality extends Image_Action
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'JPEG Adjust Quality',
		'name'		=>	'jpeg_adjust_quality',
		'version'	=>	'1.0',
		'enabled'	=>	TRUE,
	);

	/**
	 * Constructor
	 *
	 * @access public
	 *
	 * Calls the parent constructor
	 */
	public function __construct()
	{
		parent::__construct();
	}

	// ********************************************************************************* //

	public function settings($settings)
	{
		$vData = $settings;

		if (isset($vData['quality']) == FALSE) $vData['quality'] = '85';

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

	public function run($file)
	{
		$progressive = (isset($this->settings['field_settings']['progressive_jpeg']) === TRUE && $this->settings['field_settings']['progressive_jpeg'] == 'yes') ? TRUE : FALSE;
		$extension = strtolower(substr( strrchr($file, '.'), 1));

		if ($extension == 'jpg' || $extension == 'jpeg')
		{
			// get image dimensions
			$sourceImage = imagecreatefromjpeg($file);
		    if ($progressive === TRUE) @imageinterlace($sourceImage, 1);
		    imagejpeg($sourceImage, $file, $this->settings['quality']);
		    imagedestroy($sourceImage);
		}

		return TRUE;
	}

	// ********************************************************************************* //


}

/* End of file resize.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/greyscale/greyscale.php */
