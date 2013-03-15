<?php

$lang = array(

	'advanced_config' => 'Advanced Preferences',
	'basic_config' => 'Basic Preferences',
	'optional' => 'optional',
	'config_location_warning' => '<strong class="notice">Minimee appears to be configured elsewhere.</strong> There is likely no need to have this extension installed. Consult the <a href="http://johndwells.com/software/minimee" title="Minimee Docs">docs</a> for more.',

	'save' => 'Save Settings',
	'auto' => 'Auto',
	'curl' => 'cURL',
	'fgc' => 'file_get_contents()',

	'sha1' => 'SHA-1',
	'md5' => 'MD5',
	'sanitize' => 'Sanitize',

	/* Settings */
	'base_path' => 'Base Path',
	'base_path_note' => 'The location on your webserver where your <i>source</i> CSS and JS files sit.<br />Optional, defaults to FCPATH constant (the root path to your site).',
	'base_path_hint' => 'e.g. ' . rtrim(FCPATH, '/'),

	'base_url' => 'Base URL',
	'base_url_note' => 'The base URL from which your <i>source</i> CSS and JS files are served.<br />Optional, defaults to Site URL.',
	'base_url_hint' => 'e.g. ' . rtrim(get_instance()->config->item('base_url'), '/'),

	'cache_path' => 'Cache Path',
	'cache_path_note' => 'Assumed to be absolute, but will also test as relative to the Base Path.<br />If left blank, will guess `cache`.',
	'cache_path_hint' => 'e.g. ' . rtrim(FCPATH, '/') . '/cache',

	'cache_url' => 'Cache URL',
	'cache_url_note' => 'Assumed to be a fully qualified URL, but will also test as relative to the Base URL.<br />If left blank, will guess `cache`.',
	'cache_url_hint' => 'e.g. ' . rtrim(get_instance()->config->item('base_url'), '/') . '/cache',

	'cachebust' => 'Cache-Busting',
	'cachebust_note' => 'Update this to a unique string to force Minimee to create a new cache file.<br />Optional, and for most scenarios unneccessary. Consult the <a href="http://johndwells.com/software/minimee" title="Minimee Docs">docs</a> for more.',
	'cachebust_hint' => 'e.g. `1.0.0`.',
	
	'cleanup' => 'Cleanup Expired Caches',
	'cleanup_note' => '<strong>Use with caution.</strong> When enabled, Minimee will automatically delete any cache file it determines has expired. Consult the <a href="http://johndwells.com/software/minimee" title="Minimee Docs">docs</a> for more.',

	'combine' => 'Combine Assets',
	'combine_note' => 'Specify which types of assets to combine.',
	
	'css_prepend_mode' => 'CSS Prepend Mode',
	'css_prepend_mode_note' => 'By default when minifying CSS, Minimee will rewrite <i>relative</i> image & @import URLs into absolute URLs. Turn OFF to skip this step.',

	'css_prepend_url' => 'CSS Prepend URL',
	'css_prepend_url_note' => 'The URL to use when `CSS Prepend Mode` is ON.<br />Optional, by default uses the Base URL.',
	'css_prepend_url_hint' => 'e.g. ' . rtrim(get_instance()->config->item('base_url'), '/'),

	'disable' => 'Disable Minimee entirely?',

	'minify' => 'Minify Assets',
	'minify_note' => 'Specify which types of assets to run through minification engine.<br />Note: HTML minification only available for EE2.4+',

	'hash_method' => 'Filename Hash Algorithm',
	'hash_method_note' => 'Choose which algorithm to create the cache filename.<br />`Sanitize` is only recommended during development; filenames will not exceed 200 characters in length.',

	'remote_mode' => 'Remote file mode?',
	'remote_mode_note' => 'Specify how to fetch remote and {stylesheet=} URLs. \'Auto\' mode will try cURL first.',
	
	'css_library' => 'CSS Library',
	'css_library_note' => 'Specify which library to use for CSS minification. Defaults to Minify.',
	'minify' => 'Minify',
	'cssmin' => 'CSSMin',
	
	'js_library' => 'JS Library',
	'js_library_note' => 'Specify which library to use for JS minification. Defaults to JSMin.',
	'jsmin' => 'JSMin',
	'jsminplus' => 'JSMinPlus',
	
	'' => ''
);