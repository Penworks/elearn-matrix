<?php if (!defined('BASEPATH')) die('No direct script access allowed');

$lang = array(


//----------------------------------------
'ci:resize:width'	=>	'Width',
'ci:resize:height'	=>	'Height',
'ci:resize:percent'	=>	'Percent',
'ci:resize:percent_adaptive_exp'	=>	'
This function attempts to get the image to as close to the provided dimensions as possible, and then crops the
remaining overflow using a provided percentage to get the image to be the size specified.
<br /><br />
The percentage mean different things depending on the orientation of the original image.
<br /><br />
Note: that you can use any percentage between 1 and 100.
<br /><br />
For Landscape images:<br />
---------------------<br />
A percentage of 1 would crop the image all the way to the left.<br />
A percentage of 50 would crop the image to the center.<br />
A percentage of 100 would crop the image to the image all the way to the right, etc, etc.
<br /><br />
For Portrait images:<br />
--------------------<br />
This works the same as for Landscape images except that a percentage of 1 means top and 100 means bottom<br />

',


// END
''=>''
);

/* End of file resize_percent_adaptive_lang.php */
/* Location: ./system/expressionengine/third_party/channel_images/actions/resize_percent_adaptive/language/english/resize_percent_adaptive_lang.php */