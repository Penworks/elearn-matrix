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
class CI_Action_greyscale extends Image_Action
{

	/**
	 * Action info - Required
	 *
	 * @access public
	 * @var array
	 */
	public $info = array(
		'title' 	=>	'Greyscale',
		'name'		=>	'greyscale',
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
		return $this->EE->lang->line('ci:greyscale:exp');
	}

	// ********************************************************************************* //

	public function run($file)
	{
		$progressive = (isset($this->settings['field_settings']['progressive_jpeg']) === TRUE && $this->settings['field_settings']['progressive_jpeg'] == 'yes') ? TRUE : FALSE;

		// replace with your files
	    $originalFileName    = $file;
	    $destinationFileName = $file;

	    // create a copy of the original image
	    // works with jpg images
	    // fell free to adapt to other formats ;)
	    $fullPath = explode(".",$originalFileName);
	    $lastIndex = sizeof($fullPath) - 1;
	    $extension = $fullPath[$lastIndex];
	    if (preg_match("/jpg|jpeg|JPG|JPEG/", $extension))
	    {
	    	$filetype = 'jpg';
	        $sourceImage = imagecreatefromjpeg($originalFileName);
	    }
		else if (preg_match("/png|PNG/", $extension))
	    {
	    	$filetype = 'png';
	        $sourceImage = imagecreatefrompng($originalFileName);
	    }
	    else if (preg_match("/gif|GIF/", $extension))
	    {
	    	$filetype = 'gif';
	        $sourceImage = imagecreatefromgif($originalFileName);
	    }

	    // get image dimensions
	    $img_width  = imageSX($sourceImage);
	    $img_height = imageSY($sourceImage);

	    if (function_exists('imagefilter') == false)
	    {
		    // convert to grayscale
        	$palette = array();
   			for ($c=0;$c<256;$c++)
			{
			$palette[$c] = imagecolorallocate($sourceImage,$c,$c,$c);
			}

			for ($y=0;$y<$img_height;$y++)
			{
				for ($x=0;$x<$img_width;$x++)
				{
					$rgb = imagecolorat($sourceImage,$x,$y);
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
					$gs = (($r*0.299)+($g*0.587)+($b*0.114));
					imagesetpixel($sourceImage,$x,$y,$palette[$gs]);
				}
			}

	    }
	    else
	    {
	    	imagefilter($sourceImage, IMG_FILTER_GRAYSCALE);
	    }

	    // copy pixel values to new file buffer
	    $destinationImage = ImageCreateTrueColor($img_width, $img_height);
	    imagecopy($destinationImage, $sourceImage, 0, 0, 0, 0, $img_width, $img_height);

	    // create file on disk
	    if ($filetype == 'jpg')
	    {
	    	if ($progressive === TRUE) @imageinterlace($destinationImage, 1);
	    	imagejpeg($destinationImage, $destinationFileName, 100);
	    }
	    else if ($filetype == 'png') imagepng($destinationImage, $destinationFileName);
	    else if ($filetype == 'gif') imagegif($destinationImage, $destinationFileName);

	    // destroy temp image buffers
	    imagedestroy($destinationImage);
	    imagedestroy($sourceImage);

		return TRUE;
	}

	// ********************************************************************************* //


}

/* End of file resize.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/greyscale/greyscale.php */
