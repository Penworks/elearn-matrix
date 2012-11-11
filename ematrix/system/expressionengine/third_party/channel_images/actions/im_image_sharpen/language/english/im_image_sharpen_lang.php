<?php if (!defined('BASEPATH')) die('No direct script access allowed');

$lang = array(


//----------------------------------------

'im:radius'	=>	'Radius',
'im:sigma'	=>	'Sigma',
'im:image_sharpen_exp'	=>  '
<pre style="font-family:Helvetica,Arial,sans-serif;font-size:10px;">
The most important factor is the sigma. As it is the real control of thesharpening operation.
It can be any floating point value from  .1  for practically no sharpening to 3 or more for sever sharpening.
0.5 to 1.0 is rather good.

Radius is just a limit of the effect as is the threshold.

Radius is only in integer units as that is the way the algorithm works, the larger it is the slower it is.
But it should be at a minimum 1 or better still 2 times the sigma.
</pre>
',

// END
''=>''
);

/* End of file ce_image_sharpen_lang.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/ce_image_sharpen/language/english/ce_image_sharpen_lang.php */
