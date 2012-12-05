<?php if (!defined('BASEPATH')) die('No direct script access allowed');

$lang = array(


//----------------------------------------

'im:radius'	=>	'Radius',
'im:sigma'	=>	'Sigma',
'im:amount'	=>	'Amount',
'im:threshold'	=>	'Threshold',

'im:radius:exp'	=>  'An integer value defining the radius of the convolution kernel (i.e. the circle of pixels which are analysed by the unsharpmask filter), not including the center pixel. Setting this value to 0 will cause imagick to automatically choose an optimal radius based on chosen sigma value. Processing time will increase to approximately the square of the radius.',
'im:sigma:exp'	=>  ' Describes the relative weight of pixels as a function of their distance from the center of the convolution kernel. For small sigma, the outer pixels have little weight. Sigma is a decimal and should be smaller than or equal to the radius.',
'im:amount:exp'	=>  ' The fraction (as a decimal) of the difference between the original and processed image that is added back into the original. Values from 0 to 1 would be normal, values above 1 provide a more extreme sharpening effect.',
'im:threshold:exp'	=>  'A decimal from 0 to 1 defining the amount of contrast required between the central and surrounding pixels in the convolution kernel in order for it to get sharpened. A value of 0 means all pixels will be sharpened equally. Higher values will leave smoother areas of an image unsharpened, whilst sharpening only edges.',

// END
''=>''
);

/* End of file ce_image_sharpen_lang.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/ce_image_sharpen/language/english/ce_image_sharpen_lang.php */
