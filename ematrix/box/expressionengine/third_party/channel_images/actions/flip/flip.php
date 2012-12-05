<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images FLIP action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class CI_Action_flip extends Image_Action
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Flip Image',
		'name'		=>	'flip',
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

		if (isset($vData['axis']) == FALSE) $vData['axis'] = 'horizontal';

		return $this->EE->load->view('settings', $vData, TRUE);
	}

	// ********************************************************************************* //

	public function run($file)
	{
		$progressive = (isset($this->settings['field_settings']['progressive_jpeg']) === TRUE && $this->settings['field_settings']['progressive_jpeg'] == 'yes') ? TRUE : FALSE;

		$this->size = getimagesize($file);

		$width = $this->size[0];
		$height = $this->size[1];

		switch($this->size[2])
		{
			case 1:
				if (imagetypes() & IMG_GIF)
				{
					$this->im = imagecreatefromgif($file);
				}
				else return 'No GIF Support!';
				break;
			case 2:
				if (imagetypes() & IMG_JPG)
				{
					$this->im = imagecreatefromjpeg($file);
				}
				else return 'No JPG Support!';
				break;
			case 3:
				if (imagetypes() & IMG_PNG)
				{
					$this->im=imagecreatefrompng($file);
				}
				else return 'No PNG Support!';
				break;
			default:
				return 'File Type??';
		}


		$imgdest = imagecreatetruecolor($width, $height);

		if (imagetypes() & IMG_PNG)
		{
			imagesavealpha($imgdest, true);
			imagealphablending($imgdest, false);
		}

		for ($x=0 ; $x<$width ; $x++)
		{
			for ($y=0 ; $y<$height ; $y++)
			{
				if ($this->settings['axis'] == 'both') imagecopy($imgdest, $this->im, $width-$x-1, $height-$y-1, $x, $y, 1, 1);
				else if ($this->settings['axis'] == 'horizontal') imagecopy($imgdest, $this->im, $width-$x-1, $y, $x, $y, 1, 1);
				else if ($this->settings['axis'] == 'vertical') imagecopy($imgdest, $this->im, $x, $height-$y-1, $x, $y, 1, 1);
			}
		}

		$this->im = $imgdest;

		switch($this->size[2]) {
			case 1:
				imagegif($this->im, $file);
				break;
			case 2:
				if ($progressive === TRUE) @imageinterlace($this->im, 1);
				imagejpeg($this->im, $file, 100);
				break;
			case 3:
				imagepng($this->im, $file);
				break;
		}

		imagedestroy($this->im);

		return TRUE;
	}

	// ********************************************************************************* //

}

/* End of file flip.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/flip/flip.php */