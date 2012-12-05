<?php if (!defined('BASEPATH')) die('No direct script access allowed');

$lang = array(

// Required for MODULES page
'channel_images'					=>	'Channel Images',
'channel_images_module_name'		=>	'Channel Images',
'channel_images_module_description'	=>	'Enables images to be associated with an entry.',

//----------------------------------------
'ci:home'			=>	'Home',
'ci:legacy_settings'=>	'Legacy Settings',
'ci:docs' 			=>	'Documentation',
'ci:yes'			=>	'Yes',
'ci:no'				=>	'No',
'ci:pref'		=>	'Preference',
'ci:value'		=>	'Value',
'ci:sizes'		=>	'Sizes',
'ci:images'		=>	'Images',

'ci:quality'	=>	'Quality',

// MCP
'ci:location_path'	=>	'Server Location Path',
'ci:location_url'	=>	'Location URL',
'ci:no_legacy'		=>	'No Legacy Settings Found',
'ci:regenerate_sizes'=>	'Regenerate Sizes',
'ci:ci_fields'		=>	'Channel Images Fields',
'ci:grab_images'	=>	'Grab Images',
'ci:start_resize'	=>	'Start the regeneration process.',
'ci:import'			=>	'Import Images',
'ci:transfer_field'	=>	'Transfer To',
'ci:column_mapping'	=>	'Column Mapping',
'ci:dont_transfer'	=>	'Do Not Transfer',
'ci:import_entries'	=>	'Entries to Process',
'ci:status'	=>	'Status',
'ci:select_regen_field'=>	'Select a field so we can start regenerating...',

//----------------------------------------
// FIELDTYPE
//----------------------------------------

// Actions
'ci:upload_actions'	=>	'Upload Actions',
'ci:click2edit'	=>	'<span>Click to edit..</span>',
'ci:hover2edit'	=>	'<span>Hover to edit..</span>',
'ci:wysiwyg'	=>	'WYSIWYG',
'ci:editable'   =>  'Editable',
'ci:small_prev'	=>	'Small Preview',
'ci:big_prev'	=>	'Big Preview',
'ci:step'		=>	'Step',
'ci:action'		=>	'Action',
'ci:actions'	=>	'Actions',
'ci:add_action'	=>	'Add an Action',
'ci:settings'	=>	'Settings',
'ci:add_action_group'=>	'Add New Size',
'ci:no_actions'	=>	'No actions have yet been defined',
'ci:show_settings'=>	'Show Settings',
'ci:hide_settings'=>	'Hide Settings',


'ci:loc_settings'	=>	'Upload Location Settings',
'ci:upload_location'=>	'Upload Location',
'ci:test_location'	=>	'Test Location',
'ci:specify_pref_cred' =>	'Specify Credential & Settings',
'ci:local'		=>	'Local Server',

// S3
'ci:s3'			=>	'Amazon S3',
'ci:s3:key'		=>	'AWS KEY',
'ci:s3:key_exp'	=>	'Amazon Web Services Key. Found in the AWS Security Credentials.',
'ci:s3:secret_key'	=>	'AWS SECRET KEY',
'ci:s3:secret_key_exp'	=>	'Amazon Web Services Secret Key. Found in the AWS Security Credentials.',
'ci:s3:bucket'		=>	'Bucket',
'ci:s3:bucket_exp'	=>	'Every object stored in Amazon S3 is contained in a bucket. Must be unique.',
'ci:s3:region'		=>	'Bucket Region',
'ci:s3:region:us-east-1' => 'USA-East (Northern Virginia)',
'ci:s3:region:us-west-1' => 'USA-West (Northern California)',
'ci:s3:region:eu'	 => 'Europe (Ireland)',
'ci:s3:region:ap-southeast-1' => 'Asia Pacific (Singapore)',
'ci:s3:region:ap-northeast-1' => 'Asia Pacific (Japan)',
'ci:s3:acl'		=>	'ACL',
'ci:s3:acl_exp'	=>	'ACL is a mechanism which decides who can access an object.',
'ci:s3:acl:public-read'	=>	'Public READ',
'ci:s3:acl:authenticated-read'		=>	'Public Authenticated Read',
'ci:s3:acl:private'		=>	'Owner-only read',
'ci:s3:storage'	=>	'Storage Redundancy',
'ci:s3:storage:standard'=>	'Standard storage redundancy',
'ci:s3:storage:reduced'	=>	'Reduced storage redundancy (cheaper)',
'ci:s3:directory'	=>	'Subdirectory (optional)',
'ci:s3:cloudfrontd'	=>	'Cloudfront Domain (optional)',

// CloudFiles
'ci:cloudfiles'=>'Rackspace Cloud Files',
'ci:cloudfiles:username'	=>	'Username',
'ci:cloudfiles:api'			=>	'API Key',
'ci:cloudfiles:container'	=>	'Container',
'ci:cloudfiles:region'		=>	'Region',
'ci:cloudfiles:region:us'	=>	'United States',
'ci:cloudfiles:region:uk'	=>	'United Kingdom (London)',
'ci:cloudfiles:cdn_uri'		=>	'CDN URI Override',

'ci:fieldtype_settings'	=>	'Fieldtype Settings',
'ci:categories'	=>	'Categories',
'ci:categories_explain'=>	'Seperate each category with a comma.',
'ci:keep_original'	=>	'Keep Original Image',
'ci:keep_original_exp'	=>	'WARNING: If you do not upload the original image you will not be able to change the size of your existing images again.',
'ci:show_stored_images'	=>	'Show Stored Images',
'ci:limt_stored_images_author'	=>	'Limit Stored Images by Author?',
'ci:limt_stored_images_author_exp'	=>	'When using the Stored Images feature, all images uploaded by everyone will be searched. <br />Select YES to limit the searching to images uploaded by the current member.',
'ci:stored_images_search_type'	=>	'Stored Images Search Type',
'ci:entry_based' =>	'Entry Based',
'ci:image_based' =>	'Image Based',
'ci:show_import_files'     =>	'Show Import Files',
'ci:show_import_files_exp' =>	'The Import Files feature allows you to add files from the local filesystem',
'ci:import_path'           =>	'Import Path',
'ci:import_path_exp'       =>	'Path where the files will be located',
'ci:show_image_edit'        =>  'Show Image Edit Button',
'ci:allow_per_image_action'	=>	'Allow Per Image Action',
'ci:jeditable_event'=>	'Edit Field Event',
'ci:click'		=>	'Click',
'ci:hover'		=>	'Hover',
'ci:image_limit'	=>	'Image Limit',
'ci:image_limit_exp'=>	'Limit the amount of images a user can upload to this field. Leave empty to allow unlimited images.',
'ci:locked_url_fieldtype'	=>	'Obfuscate image URL\'s in the fieldtype',
'ci:locked_url_fieldtype_exp'=>	'Normally the Image URL\'s are direct links to the files. But in some cases you want the file location to be secret. Enable this option to encrypt the Image URL.<br>NOTE: This will prevent any WYSIWYG Channel Images plugin to work.',
'ci:act_url'		=>	'ACT URL',
'ci:act_url:exp'	=>	'This URL is going to be used for all AJAX calls and image uploads',
'ci:hybrid_upload'       => 'Hybrid Upload',
'ci:hybrid_upload_exp'   =>	'Enabling this option will turn on HTML 5 uploading, otherwise Flash uploading will occur.',
'ci:progressive_jpeg'	=>	'Create Progressive JPEG',
'ci:progressive_jpeg_exp'=>	'Enabling this will create progressive JPEG. Limitations: Does not work with CE Image Actions, Internet Explorer does not support progressive JPEGs',
'ci:wysiwyg_original'	=>	'Original Image Option in WYSIWYG',
'ci:save_data_in_field'	=>	'Save image metadata in Custom Field',
'ci:save_data_in_field_exp'	=>	'When you enable this Channel Images will store image titles/desc/etc in the Custom Field so it can be searched. <br>Note: It takes more space in your database.',
'ci:disable_cover'		=>	'Disable Cover button',

// Field Columns
'ci:field_columns'		=>	'Field Columns',
'ci:field_columns_exp'	=>	'Specify a label for each column, leave the field blank to disable the column.',
'ci:row_num'		=>	'#',
'ci:id'				=>	'ID',
'ci:image'			=>	'Image',
'ci:title'			=>	'Title',
'ci:url_title'		=>	'URL Title',
'ci:desc'			=>	'Description',
'ci:category'		=>	'Category',
'ci:filename'		=>	'Filename',
'ci:actions:edit'	=>	'Edit',
'ci:actions:cover'	=>	'Cover',
'ci:actions:move'	=>	'Move',
'ci:actions:del'	=>	'Delete',
'ci:actions:process_action'=>	'Process Action',
'ci:actions:unlink'	=>	'Unlink',
'ci:cifield_1'		=>	'Field 1',
'ci:cifield_2'		=>	'Field 2',
'ci:cifield_3'		=>	'Field 3',
'ci:cifield_4'		=>	'Field 4',
'ci:cifield_5'		=>	'Field 5',
'c:filesize'		=>	'Filesize',

// PBF
'ci:upload_images'	=>	'Upload Images',
'ci:stored_images'	=>	'Stored Images',
'ci:time_remaining'	=>	'Time Remaining',
'ci:stop_upload'	=>	'Stop Upload',
'ci:dupe_field'		=>	'Only one Channel Images field can be used at once.',
'ci:missing_settings'=>	'Missing Channel Images settings for this field.',
'ci:no_images'		=>	'No images have yet been uploaded.',
'ci:site_is_offline'=>	'Site is OFFLINE! Uploading images will/might not work.',
'ci:image_remain'	=>	'Images Remaining:',
'ci:crossdomain_detect' =>	'CROSSDOMAIN: The current domain does not mach the ACT URL domain. Upload may fail due crossdomain restrictions.',
'ci:drophere'           =>	'Drop Your Files Here....',

'ci:json:click2edit'		=> '<span>Click to edit..</span>',
'ci:json:mouseenter2edit'	=> '<span>Hover to edit..</span>',
'ci:json:file_limit_reached'=> 'ERROR: File Limit Reached',
'ci:json:xhr_reponse_error'	=> "Server response was not as expected, probably a PHP error. <a href='#' class='OpenError'>OPEN ERROR</a>",
'ci:json:xhr_status_error'	=> "Upload request failed, no HTTP 200 Return Code! <a href='#' class='OpenError'>OPEN ERROR</a>",
'ci:json:del_file'			=> 'Are you sure you want to delete this file?',
'ci:json:unlink_file'		=> 'Are you sure you want to unlink this file?',
'ci:json:linked_force_del'	=> "This file is linked with other entries, are you sure you want to delete it? \n The other references will also be deleted!",
'ci:json:submitwait'		=> 'You have uploaded file(s), those files are now being send to their final destination. This can take a while depending on the amount of files..',

'ci:add_image'		=>	'Add Image',
'ci:caption_text'	=>	'Image Caption:',

// Stored Images
'ci:last'			=>	'Last',
'ci:entries'		=>	'Entries',
'ci:filter_keywords'	=>	'Keywords',
'ci:entry_images'	=>	'Entry Images',
'ci:loading_images'	=>	'Loading Images...',
'ci:loading_entries'=>	'Loading Entries..',
'ci:no_entry_sel'	=>	'No entry has been selected.',
'ci:no_images'		=>	'No Images found..',

// Import Files
'ci:import_files'		=> 'Import Images',
'ci:import:bad_path'	=> 'The supplied import path does not exist (or is inaccessible).',
'ci:import:no_files'	=> 'No files..',

// Action Per Image
'ci:apply_action'	=>	'Apply Action',
'ci:apply_action_exp'=>	'Select an action to execute on the selected image size.',
'ci:select_action'	=>	'Select an Action',
'ci:applying_action'=>	'Applying your selected action, please wait...',
'ci:preview'		=>	'Preview',
'ci:save'			=>	'Save',
'ci:save_img'           =>  'Save Image',
'ci:cancel' =>  'Cancel',
'ci:original'		=>	'ORIGINAL',

// Edit Image
'ci:crop'   =>  'Crop',
'ci:rotate_left'=>  'Rotate Left',
'ci:rotate_right'=>  'Rotate Right',
'ci:flip_hor'=>  'Flip Horizontally',
'ci:flip_ver'=>  'Flip Vertically',
'ci:image_scaled_note'  =>  '<storng>Note:</storng> This is a scaled representation of the actual image size.',
'ci:regen_sizes'    =>  'Regenerate All Sizes',
'ci:apply_crop'     =>  'Apply Crop',
'ci:cancel_crop'    =>  'Cancel Crop',
'ci:set_crop_sel'   =>  'Set Selection',

'ci:zenbu_show_cover'   =>  'Show Cover image only (or first image)',

// Pagination
'ci:pag_first_link' => '&lsaquo; First',
'ci:pag_last_link' => 'Last &rsaquo;',

'ci:required_field'	=>	'REQUIRED FIELD: Please add at least one image.',

// Errors
'ci:file_arr_empty'	=> 'No file was uploaded or file is not allowed by EE.(See EE Mime-type settings).',
'ci:tempkey_missing'	=> 'The temp key was not found',
'ci:file_upload_error'	=> 'No file was uploaded. (Maybe filesize was too big)',
'ci:no_settings'		=> 'No settings exist for this fieldtype',
'ci:location_settings_failure'	=>	'Upload Location Settings Missing',
'ci:location_load_failure'	=>	'Failure to load Upload Location Class',
'ci:tempdir_error'		=>	'The Local Temp dir is either not writable or does not exist',

'ci:temp_dir_failure'		=>	'Failed to create the temp dir, through Upload Location Class',
'ci:file_upload_error'		=>	'Failed to upload the image, through Upload Location Class',



'ci:no_upload_location_found' => 'Upload Location has not been found!.',
'ci:file_to_big'		=> 'The file is too big. (See module settings for max file size).',
'ci:extension_not_allow'=> 'The file extension is not allowed. (See module settings for file extensions)',
'ci:targetdir_error'	=> 'The target directory is either not writable or does not exist',
'ci:file_move_error'	=> 'Failed to move uploaded file to the temp directory, please check upload path permissions etc.',


// END
''=>''
);

/* End of file channel_images_lang.php */
/* Location: ./system/expressionengine/third_party/channel_images/language/english/channel_images_lang.php */
