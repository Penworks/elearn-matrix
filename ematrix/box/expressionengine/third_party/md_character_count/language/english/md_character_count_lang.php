<?php
$lang = array(
'extension_title'                  => 'MD Character Count',
'css_title' 					             => 'CSS',
'css_info' 					               => 'The CSS style information for the counter. The ".charcounter_err" class is added to fields that are set to "soft" counts where the character count goes over the max.',
'fields_title' 					           => 'Character Count Fields',
'fields_info' 				             => '<strong>Count</strong><br />
If field is left blank then no count will display or be imposed. The Max is pulled from the settings for the custom field.<br /><br />
<strong>Count Type</strong><br />
A "soft" count allows the user to type beyond the max set in "Count". When the max is exceeded, the Count Format will change style (specified in CSS below) as a warning. A hard count will cap the text entry so that the Count cannot be exceeded. <strong>NOTE:</strong> Be careful assigning a field that already has data in it to a "hard" count, as you will lose the characters beyond the number in your Count field.
<br /><br />
<strong>Count Format</strong><br />
If nothing is entered, the format will default to: "<strong>%1/{count} characters remaining</strong>" where "%1" is the number of typed characters, and {count} is the number entered in the Count field. You can enter anything here (note that {count} is not a real variable. If you need the max shown in the Count Format, you have to enter it manually). Using "20" for a sample count, you might try:<br /><br />
<ol style="padding-left: 25px" >
<li><strong>20 max (%1 left) Hard count.</strong></li>
<li><strong>(%1/20)</strong></li>
<li><strong>20 characters suggested. You have %1 left. (Soft count).</strong></li>
</ol>',
'coltitle_count'                   => 'Count',
'coltitle_count_type'              => 'Count Type',
'coltitle_count_format'            => 'Count Format',
'maximum_label'                    => 'Max',
// END
''=>''
);
?>