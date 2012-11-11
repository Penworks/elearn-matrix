<?php if (!defined('BASEPATH')) die('No direct script access allowed');

$lang = array(


//----------------------------------------

'ce:amount'			=>  'Amount',
'ce:amount:exp'		=>  "How much of the effect you want, 100 is 'normal' (typically 50 to 200).",
'ce:radius'			=>  'Radius',
'ce:radius:exp'		=>  'Radius of the blurring circle of the mask. (typically 0.5 to 1).',
'ce:threshold'		=>  'Threshold',
'ce:threshold:exp'	=>  'The least difference in color values that is allowed between the original and the mask. In practice this means that low-contrast areas of the picture are left un-rendered whereas edges are treated normally. This is good for pictures of e.g. skin or blue skies. (typically 0 to 5).',


'ce:sharpen_exp'	=>	'When images are resized by PHP, they often appear a bit blurry. You can use this filter to sharpen the images to your taste.',

// END
''=>''
);

/* End of file ce_image_sharpen_lang.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/ce_image_sharpen/language/english/ce_image_sharpen_lang.php */