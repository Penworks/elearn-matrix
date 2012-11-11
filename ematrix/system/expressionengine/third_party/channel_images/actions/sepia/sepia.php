<?php if (!defined('BASEPATH')) die('No direct script access allowed');

/**
 * Channel Images SEPIA action
 *
 * @package			DevDemon_ChannelImages
 * @author			DevDemon <http://www.devdemon.com> - Lead Developer @ Parscale Media
 * @copyright 		Copyright (c) 2007-2011 Parscale Media <http://www.parscale.com>
 * @license 		http://www.devdemon.com/license/
 * @link			http://www.devdemon.com/channel_images/
 */
class CI_Action_sepia extends Image_Action
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Sepia',
		'name'		=>	'sepia',
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
		return $this->EE->lang->line('ci:sepia:exp');
	}

	// ********************************************************************************* //

	public function run($file)
	{
		$progressive = (isset($this->settings['field_settings']['progressive_jpeg']) === TRUE && $this->settings['field_settings']['progressive_jpeg'] == 'yes') ? TRUE : FALSE;

		$this->size = getimagesize($file);

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

		$this->Ageimage = array(1, 0, 60);

		imagetruecolortopalette($this->im,1,256);
		for ($c=0;$c<256;$c++) {
			$col=imagecolorsforindex($this->im,$c);
			$new_col=floor($col['red']*0.2125+$col['green']*0.7154+$col['blue']*0.0721);
			$noise=rand(-$this->Ageimage[1],$this->Ageimage[1]);
			if ($this->Ageimage[2]>0) {
				$r=$new_col+$this->Ageimage[2]+$noise;
				$g=floor($new_col+$this->Ageimage[2]/1.86+$noise);
				$b=floor($new_col+$this->Ageimage[2]/-3.48+$noise);
			} else {
				$r=$new_col+$noise;
				$g=$new_col+$noise;
				$b=$new_col+$noise;
			}
			imagecolorset($this->im,$c,max(0,min(255,$r)),max(0,min(255,$g)),max(0,min(255,$b)));
		}

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

/* End of file sepia.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/sepia/sepia.php */