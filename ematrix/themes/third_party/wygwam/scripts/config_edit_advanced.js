var wygwam_addSettingRow;


(function($){

/**
 * Switcht
 */
$.fn.ptSwitch = function(){
	return this.each(function(){
		var $select = $(this).hide(),
			offVal = this[0].value,
			offLabel = this[0].text,
			onVal = this[1].value,
			onLabel = this[1].text,
			$ul = $('<ul class="ptSwitch" tabindex="0" />').insertAfter($select),
			$off = $('<li class="'+offVal+'">'+offLabel+'</li>').appendTo($ul),
			$toggle = $('<li class="toggle" />').appendTo($ul),
			$on = $('<li class="'+onVal+'">'+onLabel+'</li>').appendTo($ul),
			selected = $select.val() == onVal;

		// set initial bg position
		$toggle.css({ backgroundPosition: (selected ? 0 : 100) + '% 0' });

		var select = function(){
			selected = true;
			$select.val('y');
			$toggle.stop().animate({
				backgroundPosition: "0% 0"
			}, 'fast');
		};

		var deselect = function(){
			selected = false;
			$select.val('n');
			$toggle.stop().animate({
				backgroundPosition: '100% 0'
			}, 'fast');
		};

		var toggle = function(){
			if (selected) deselect();
			else select();
		};

		$off.click(deselect);
		$on.click(select);

		$toggle.mousedown(function(event){
			var width = $toggle.width(),
				pageX = event.pageX,
				pageY = event.pageY,
				percent = selected ? 100 : 0;

			$(document).bind('mousemove.ptSwitch', function(event){
				percent = (selected ? 100 : 0) + 100 * (event.pageX - pageX) / width;
				if (percent > 100) percent = 100;
				else if (percent < 0) percent = 0;
				$toggle.css('background-position', (100-percent)+'% 0');
			});

			$(document).bind('mouseup.ptSwitch', function(event){
				$(document).unbind('.ptSwitch');

				// just toggle if it was a single click
				if (pageX == event.pageX && pageY == event.pageY) {
					toggle();
				} else {
					if (percent < 50) deselect();
					else select();
				}
			});
		});

		$ul.keydown(function(event){
			switch(event.keyCode) {
				case 32: toggle(); break;
				case 37: deselect(); break;
				case 39: select(); break;
				default: return;
			}

			event.preventDefault();
		});

		$select.focus(function(){
			$ul.focus();
		});
	});
};

// -------------------------------------------
//  Advanced Settings
// -------------------------------------------

var $table = $('#advanced > table'),
	$tbody = $('> tbody', $table);

if (! $tbody.lendgth) {
	$tbody = $('<tbody />').appendTo($table);
}

var languages = {
	af: 'Afrikaans',
	ar: 'Arabic',
	bg: 'Bulgarian',
	bn: 'Bengali/Bangla',
	bs: 'Bosnian',
	ca: 'Catalan',
	cs: 'Czech',
	cy: 'Welsh',
	da: 'Danish',
	de: 'German',
	el: 'Greek',
	en: 'English',
	'en-au': 'English (Australia)',
	'en-ca': 'English (Canadian)',
	'en-uk': 'English (United Kingdom)',
	eo: 'Esperanto',
	es: 'Spanish',
	et: 'Estonian',
	eu: 'Basque',
	fa: 'Persian',
	fi: 'Finnish',
	fo: 'Faroese',
	fr: 'French',
	'fr-ca': 'French (Canada)',
	gl: 'Galician',
	gu: 'Gujarati',
	he: 'Hebrew',
	hi: 'Hindi',
	hr: 'Croatian',
	hu: 'Hungarian',
	is: 'Icelandic',
	it: 'Italian',
	ja: 'Japanese',
	km: 'Khmer',
	ko: 'Korean',
	lt: 'Lithuanian',
	lv: 'Latvian',
	mn: 'Mongolian',
	ms: 'Malay',
	nb: 'Norwegian Bokmal',
	nl: 'Dutch',
	no: 'Norwegian',
	pl: 'Polish',
	pt: 'Portuguese (Portugal)',
	'pt-br': 'Portuguese (Brazil)',
	ro: 'Romanian',
	ru: 'Russian',
	sk: 'Slovak',
	sl: 'Slovenian',
	sr: 'Serbian (Cyrillic)',
	'sr-latn': 'Serbian (Latin)',
	sv: 'Swedish',
	th: 'Thai',
	tr: 'Turkish',
	uk: 'Ukrainian',
	vi: 'Vietnamese',
	zh: 'Chinese Traditional',
	'zh-cn': 'Chinese Simplified'
};

var settings = {
	// autoGrow_bottomSpace
	autoGrow_maxHeight: { desc: 'The maximum height to which the editor can reach using AutoGrow. Zero means unlimited.', type: 'number', val: '0' },
	autoGrow_minHeight: { desc: 'The minimum height to which the editor can reach using AutoGrow', type: 'number', val: 200 },
	autoGrow_onStartup: { desc: 'Whether to have the auto grow happen on editor creation.', type: 'bool' },
	autoParagraph: { desc: 'Whether automatically create wrapping blocks around inline contents inside document body, this helps to ensure the integrality of the block enter mode.', type: 'bool', val: 'y' },
	// autoUpdateElement, baseFloatZIndex
	baseHref: { desc: 'The base href URL used to resolve relative and absolute URLs in the editor content.' },
	// blockedKeystrokes
	bodyClass: { desc: 'Sets the ‘class’ attribute to be used on body if it doesn’t have one.' },
	bodyId: { desc: 'Sets the ‘id’ attribute to be used on body if it doesn’t have one.' },
	// browserContextMenuOnCtrl, colorButton_backStyle
	colorButton_colors: { desc: 'Defines the colors to be displayed in the color selectors. It’s a string containing the hexadecimal notation for HTML colors, without the “#” prefix.', type: 'textarea', val: '000,800000,8B4513,2F4F4F,008080,000080,4B0082,696969,B22222,A52A2A,DAA520,006400,40E0D0,0000CD,800080,808080,F00,FF8C00,FFD700,008000,0FF,00F,EE82EE,A9A9A9,FFA07A,FFA500,FFFF00,00FF00,AFEEEE,ADD8E6,DDA0DD,D3D3D3,FFF0F5,FAEBD7,FFFFE0,F0FFF0,F0FFFF,F0F8FF,E6E6FA,FFF' },
	colorButton_enableMore: { desc: 'Whether to enable the “More Colors...” button in the color selectors.', type: 'bool' },
	// colorButton_foreStyle
	//contentsCss: { desc: 'The CSS file(s) to be used to apply style to the contents. Put each file on a single line.', type: 'textarea', val: 'contents.css' },
	contentsLangDirection: { desc: 'The writting direction of the language used to write the editor contents.', type: 'select', options: { ltr: 'Left-to-right', rtl: 'Right-to-left' }},
	customConfig: { desc: 'The URL path for the custom configuration file to be loaded. If not overloaded with inline configurations, it defaults to the “config.js” file present in the root of the CKEditor installation directory.' },
	defaultLanguage: { desc: 'The language to be used if the “language” setting isn’t set and it’s not possible to localize the editor to the user language.', type: 'select', options: languages, val: 'en' },
	// devtools_styles, dialog_backgroundCoverColor, dialog_backgroundCoverOpacity, dialog_buttonsOrder
	dialog_buttonsOrder: { desc: 'The guideline to follow when generating the dialog buttons.', type: 'select', options: { OS: 'Operating System Default', ltr: 'Left-to-right', rtl: 'Right-to-left' }},
	// dialog_magnetDistance,
	disableNativeSpellChecker: { desc: 'Disables the built-in spell checker while typing natively available in the browser (currently Firefox and Safari only).', type: 'bool', val: 'y' },
	// disableNativeTableHandles
	disableObjectResizing: { desc: 'Disables the ability of resize objects (image and tables) in the editing area.', type: 'bool' },
	// disableReadonlyStyling
	disableReadonlyStyling: { desc: 'Disables inline styling on read-only elements.', type: 'bool' },
	docType: { desc: 'Sets the doctype to be used when loading the editor content as HTML.', val: '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">' },
	editingBlock: { desc: 'Whether to render or not the editing block area in the editor interface.', type: 'bool', val: 'y' },
	emailProtection: { desc: 'The e-mail address anti-spam protection option.', type: 'emailProtection' },
	enterMode: { desc: 'Sets the behavior for the ENTER key. It also dictates other behaviour rules in the editor, like whether the &lt;br&gt; element is to be used as a paragraph separator when indenting text.', type: 'select', options: { 'CKEDITOR.ENTER_P':'&lt;p&gt;', 'CKEDITOR.ENTER_BR':'&lt;br&gt;', 'CKEDITOR.ENTER_DIV':'&lt;div&gt;' }},
	entities: { desc: 'Whether to use HTML entities in the output.', type: 'bool', val: 'y' },
	entities_additional: { desc: 'An additional list of entities to be used. It’s a string containing each entry separated by a comma. Entities names or number must be used, exclusing the “&” preffix and the “;” termination.', val: '#39' },
	entities_greek: { desc: 'Whether to convert some symbols, mathematical symbols, and Greek letters to HTML entities. This may be more relevant for users typing text written in Greek. The list of entities can be found at the <a href="http://www.w3.org/TR/html4/sgml/entities.html#h-24.3.1" target="_blank">W3C HTML 4.01 Specification, section 24.3.1</a>.', type: 'bool', val: 'y' },
	entities_latin: { desc: 'Whether to convert some Latin characters (Latin alphabet No. 1, ISO 8859-1) to HTML entities. The list of entities can be found at the <a href="http://www.w3.org/TR/html4/sgml/entities.html#h-24.2.1" target="_blank">W3C HTML 4.01 Specification, section 24.2.1</a>.', type: 'bool', val: 'y' },
	entities_processNumerical: { desc: 'Whether to convert all remaining characters, not comprised in the ASCII character table, to their relative numeric representation of HTML entity. For example, the phrase “This is Chinese: 汉语.” is outputted as “This is Chinese: &amp;#27721;&amp;#35821;.”', type: 'bool', val: 'y' },
	extraPlugins: { desc: 'Comma-separated list of additional plugins to be loaded.' },
	//filebrowserWindowFeatures: { desc: 'The “features” to use in the file browser popup window.', val: 'location=no,menubar=no,toolbar=no,dependent=yes,minimizable=no,modal=no,alwaysRaised=yes,resizable=yes,scrollbars=yes' },
	// filebrowserWindowHeight, filebrowserWindowWidth
	fillEmptyBlocks: { desc: 'Whether a non-breaking space should be inserted into empty block elements in the HTML output.', type: 'bool', val: 'y' },
	// find_highlight
	font_defaultLabel: { desc: 'The text to be displayed in the Font combo if none of the available values matches the current cursor position or text selection.' },
	font_names: { desc: 'The list of fonts names to be displayed in the Font combo in the toolbar. Entries are separated by semi-colons (;), while it’s possible to have more than one font for each entry, in the HTML way (separated by comma). A display name may be optionally defined by prefixing the entries with the name and the slash character. For example, “Arial/Arial, Helvetica, sans-serif” will be displayed as “Arial” in the list, but will be outputted as “Arial, Helvetica, sans-serif”.', type: 'textarea' },
	// font_style
	fontSize_defaultLabel: { desc: 'The text to be displayed in the Font Size combo is none of the available values matches the current cursor position or text selection.' },
	fontSize_sizes: { desc: 'The list of fonts size to be displayed in the Font Size combo in the toolbar. Entries are separated by semi-colons (;). Any kind of “CSS like” size can be used, like “12px”, “2.3em”, “130%”, “larger” or “x-small”. A display name may be optionally defined by prefixing the entries with the name and the slash character. For example, “Bigger Font/14px” will be displayed as “Bigger Font” in the list, but will be outputted as “14px”.', type: 'textarea', val: '8/8px;9/9px;10/10px;11/11px;12/12px;14/14px;16/16px;18/18px;20/20px;22/22px;24/24px;26/26px;28/28px;36/36px;48/48px;72/72px' },
	// fontSize_style
	forceEnterMode: { desc: 'Force the respect of CKEDITOR.config.enterMode as line break regardless of the context, E.g. If CKEDITOR.config.enterMode is set to CKEDITOR.ENTER_P, presssing the enter key inside a div will create a new paragraph instead of a div.', type: 'bool' },
	forcePasteAsPlainText: { desc: 'Whether to force all pasting operations to insert on plain text into the editor, loosing any formatting information possibly available in the source text.', type: 'bool', val: 'y' },
	forceSimpleAmpersand: { desc: 'Whether to force using “&amp;” instead of “&amp;amp;” in elements attributes values. It‘s not recommended to change this setting for compliance with the W3C XHTML 1.0 standards (<a href="http://www.w3.org/TR/xhtml1/#C_12" target="_blank">C.12, XHTML 1.0</a>).', type: 'bool' },
	// * format_address ... format_pre
	format_tags: { desc: 'The list of tags to be displayed in the Format combo in the toolbar.', type: 'checkboxes', options: 'p;h1;h2;h3;h4;h5;h6;pre;address;div'.split(';'), val: 'p;h1;h2;h3;h4;h5;h6;pre;address;div'.split(';') },
	fullPage: { desc: 'Indicates whether the contents to be edited are being inputted as a full HTML page. A full page includes the &lt;html&gt;, &lt;head&gt; and &lt;body&gt; tags. The final output will also reflect this setting, including the &lt;body&gt; contents only if this setting is disabled.', type: 'bool' },
	//height: { desc: 'The height of editing area( content ), in relative or absolute, e.g. 30px, 5em. Note: Percentage unit is not supported yet. e.g. 30%.', type: 'number', val: 200 },
	htmlEncodeOutput: { desc: 'Whether escape HTML when editor update original input element.', type: 'bool' },
	ignoreEmptyParagraph: { desc: 'Whether the editor must output an empty value (“”) if it’s contents is made by an empty paragraph only.', type: 'bool', val: 'y' },
	image_removeLinkByEmptyURL: { desc: 'Whether to remove links when emptying the link URL field in the image dialog.', type: 'bool', val: 'y' },
	justifyClasses: { desc: 'Classes to use for aligning the contents. If it’s not set, inline styles will be used instead of classes.', type: 'justifyClasses' },
	language: { desc: 'The user interface language localization to use.', type: 'select', options: languages, val: 'en' },
	menu_groups: { desc: 'A comma separated list of items group names to be displayed in the context menu. The items order will reflect the order in this list if no priority has been definted in the groups.', val: 'clipboard,form,tablecell,tablecellproperties,tablerow,tablecolumn,table,anchor,link,image,flash,checkbox,radio,textfield,hiddenfield,imagebutton,button,select,textarea' },
	menu_subMenuDelay: { desc: 'The amount of time, in milliseconds, the editor waits before showing submenu options when moving the mouse over options that contains submenus, like the “Cell Properties” entry for tables.', type: 'number', val: 400 },
	newpage_html: { desc: 'The HTML to load in the editor when the “new page” command is executed.', type: 'textarea' },
	// pasteFromWordCleanupFile
	pasteFromWordNumberedHeadingToList: { desc: 'Whether transform MS-Word Outline Numbered Heading into html list.', type: 'bool' },
	pasteFromWordPromptCleanup: { desc: 'Whether prompt the user about the clean-up of content from MS-Word.', type: 'bool' },
	pasteFromWordRemoveFontStyles: { desc: 'Whether the ignore all font-related format styles, including: - font size; - font family; - font fore/background color;', type: 'bool', val: 'y' },
	pasteFromWordRemoveStyles: { desc: 'Whether remove element styles that can’t be managed with editor, note that this this doesn’t handle the font-specific styles, which depends on how pasteFromWordRemoveFontStyles is configured.', type: 'bool', val: 'y' },
	// * protectedSource
	readOnly: { desc: 'If “true”, makes the editor start in read-only state.', type: 'bool' },
	removeDialogTabs: { desc: 'The dialog contents to removed. It’s a string composed by dialog name and tab name with a colon between them. Separate each pair with semicolon.' },
	removeFormatAttributes: { desc: 'A comma separated list of elements attributes to be removed when executing the “remove format” command.', val: 'class,style,lang,width,height,align,hspace,valign' },
	removeFormatTags: { desc: 'A comma separated list of elements to be removed when executing the “remove format” command. Note that only inline elements are allowed.', val: 'b,big,code,del,dfn,em,font,i,ins,kbd,q,samp,small,span,strike,strong,sub,sup,tt,u,var' },
	removePlugins: { desc: 'A comma separated list of plugins that must not be loaded.' },
	//resize_enabled: { desc: 'Whether to enable the resizing feature. If disabed the resize handler will not be visible.', type: 'bool', val: 'y' },
	resize_dir: { desc: 'The directions to which the editor resizing is enabled.', type: 'select', options: { vertical:'Vertical', horizontal:'Horizontal', both:'Both' }, val: 'both' },
	resize_maxHeight: { desc: 'The maximum editor height, in pixels, when resizing it with the resize handle.', type: 'number', val: 3000 },
	resize_maxWidth: { desc: 'The maximum editor width, in pixels, when resizing it with the resize handle.', type: 'number', val: 3000 },
	resize_minHeight: { desc: 'The minimum editor height, in pixels, when resizing it with the resize handle.', type: 'number', val: 250 },
	resize_minWidth: { desc: 'The minimum editor width, in pixels, when resizing it with the resize handle.', type: 'number', val: 750 },
	// * shiftEnterMode
	skin: { desc: 'The skin to load. It may be the name of the skin folder inside the editor installation path, or the name and the path separated by a comma.', val: 'wygwam' },
	// * smiley_columns, smiley_descriptions, smiley_images, smiley_path
	startupFocus: { desc: 'Sets whether the editor should have the focus when the page loads.', type: 'bool' },
	startupMode: { desc: 'The mode to load at the editor startup. It depends on the plugins loaded. By default, the “wysiwyg” and “source” modes are available.', val: 'wysiwyg' },
	startupOutlineBlocks: { desc: 'Whether to automaticaly enable the “show block” command when the editor loads.', type: 'bool' },
	stylesheetParser_skipSelectors: { desc: 'Regular Expression to check if a css rule must be skipped by the stylesheet parser plugin (so it’s ignored and not available)', val: '/(^body\\.|^\\.)/i' },
	stylesheetParser_validSelectors: { desc: 'Regular Expression to check if a css rule must be allowed by the stylesheet parser plugin', val: '/\\w+\\.\\w+/' },
	stylesSet: { desc: 'The "styles definition set" to use in the editor. They will be used in the styles combo and the Style selector of the div container.', val: 'default' },
	tabIndex: { desc: 'The editor tabindex value.', type: 'number', val: '0' },
	tabSpaces: { desc: 'Intructs the editor to add a number of spaces (&nbsp;) to the text when hitting the TAB key. If set to zero, the TAB key will be used to move the cursor focus to the next element in the page, out of the editor focus.', type: 'number', val: '0' },
	templates: { desc: 'The templates definition set to use. It accepts a list of names separated by comma. It must match definitions loaded with the templates_files setting.', val: 'default' },
	templates_files: { desc: 'The list of templates definition files to load. Put each file on a single line.', type: 'textarea', val: 'plugins/templates/templates/default.js' },
	templates_replaceContent: { desc: 'Whether the “Replace actual contents” checkbox is checked by default in the Templates dialog.', type: 'bool', val: 'y' },
	// theme
	// * toolbar, toolbar_Basic, toolbar_Full
	// toolbarCanCollapse: { desc: 'Whether the toolbar can be collapsed by the user. If disabled, the collapser button will not be displayed.', type: 'bool' },
	toolbarGroupCycling: { desc: 'When enabled, makes the arrow keys navigation cycle within the current toolbar group. Otherwise the arrows will move trought all items available in the toolbar. The TAB key will still be used to quickly jump among the toolbar groups.', type: 'bool', val: 'y' },
	toolbarStartupExpanded: { desc: 'Whether the toolbar must start expanded when the editor is loaded.', type: 'bool', val: 'y' },
	undoStackSize: { desc: 'The number of undo steps to be saved. The higher this setting value the more memory is used for it.', type: 'number', val: 20 },
	width: { desc: 'The editor width in CSS size format or pixel integer.', type: 'number' }
};

var lastSettingRow;

wygwam_addSettingRow = function(initialType, initialVal){
	if (initialType && typeof settings[initialType] == 'undefined') return;

	if (lastSettingRow && !$('> td:first > select:first', lastSettingRow).val()) {
		lastSettingRow.remove();
	}

	var $tr = $('<tr />').appendTo($tbody),
		$th = $('<td style="width: 40%;" />').appendTo($tr),
		$td = $('<td />').appendTo($tr),
		$td2 = $('<td class="remove" width="1%"></td>').appendTo($tr),
		$remove = $('<a>Remove</a>').appendTo($td2),
		$settingSelect = $('<select />').appendTo($th)
			.append('<option value="">Add an advanced setting...</option>'),
		$desc = $('<p />').appendTo($th).hide(),
		selected = false;

	lastSettingRow = $tr;

	// row class
	if ($tr.attr('rowIndex') % 2) {
		$tr.addClass('even');
		$th.addClass('tableCellOne');
		$td.addClass('tableCellOne');
		$td2.addClass('tableCellOne');
	} else {
		$tr.addClass('odd');
		$th.addClass('tableCellTwo');
		$td.addClass('tableCellTwo');
		$td2.addClass('tableCellTwo');
	}

	for (var i in settings) {
		$('<option>'+i+'</option>').appendTo($settingSelect);
	}

	var showSetting = function(setting, val, focus){
		// remove last item
		$td.html('');

		if (setting) {
			var settingAttr = settings[setting],
				name ='settings['+setting+']',
				type = settingAttr.type || 'text';

			// description
			$desc.html(settingAttr.desc+' <a href="http://docs.cksource.com/ckeditor_api/symbols/CKEDITOR.config.html#.'+setting+'" target="_blank">More...</a>').show();

			// input
			switch (type) {
				case 'textarea':
					var $input = $('<textarea name="'+name+'" cols="90" rows="5" spellcheck="false" class="fullfield">').appendTo($td).html(val);
					break;

				case 'bool':
					var $input = $('<select name="'+name+'">' +
					                 '<option value="n">No</option>' +
					                 '<option value="y">Yes</option>' +
					               '</select>').appendTo($td);
					$input.val(val == 'y' ? 'y' : 'n');
					$input.ptSwitch();
					break;

				case 'select':
					var $input = $('<select name="'+name+'" />').appendTo($td);
					for (var i in settingAttr.options) {
						$('<option value="'+i+'">'+settingAttr.options[i]+'</option>').appendTo($input);
					}
					if (val) $input.val(val);
					break;

				case 'checkboxes':
					for (var i in settingAttr.options) {
						var option = settingAttr.options[i],
							$label = $('<label style="margin-right: 10px;"> '+option+'</label>').appendTo($td),
							$checkbox = $('<input type="checkbox" name="'+name+'[]" value="'+option+'" />').prependTo($label);
						if (val.indexOf(option) != -1) {
							$checkbox.attr('checked', 'checked');
						}
					}
					break;

				case 'number':
					$input = $('<input type="number" name="'+name+'" class="field ptNumber" />').appendTo($td).val(val);
					break;

				case 'emailProtection':
					var $input = $('<select name="'+name+'">' +
					                 '<option value="">None</option>' +
					                 '<option value="encode">Encode</option>' +
					                 '<option value="custom">Custom...</option>' +
					               '</select>').appendTo($td),
						$custom = $('<input type="text" name="'+name+'" spellcheck="false" class="field" style="margin-left: 10px;" value="myFunction(NAME,DOMAIN,SUBJECT,BODY)" />').appendTo($td);

					if (val == 'encode') {
						$input.val('encode');
					} else if (val) {
						$input.val('custom');
						$custom.val(val);
					}

					$input.change(function(){
						if ($input.val() == 'custom') {
							$custom.css('visibility', 'visible').removeAttr('disabled');
							if (focus) $custom.focus();
						} else {
							$custom.css('visibility', 'hidden').attr('disabled', 'disabled');
						}
					});

					$input.change();
					break;

				case 'justifyClasses':
					var $table = $('<table width="100%">'
					             +   '<tbody>'
					             +     '<tr>'
					             +       '<th scope="row" style="width: 0.01%">Left</th>'
					             +       '<td><input type="text" name="'+name+'[0]" value="'+(val[0] || '')+'" /></td>'
					             +     '</tr>'
					             +     '<tr>'
					             +       '<th scope="row">Center</th>'
					             +       '<td><input type="text" name="'+name+'[1]" value="'+(val[1] || '')+'" /></td>'
					             +     '</tr>'
					             +     '<tr>'
					             +       '<th scope="row">Right</th>'
					             +       '<td><input type="text" name="'+name+'[2]" value="'+(val[2] || '')+'" /></td>'
					             +     '</tr>'
					             +     '<tr>'
					             +       '<th scope="row">Justify</th>'
					             +       '<td><input type="text" name="'+name+'[3]" value="'+(val[3] || '')+'" /></td>'
					             +     '</tr>'
					             +   '</tbody>'
					             + '</table>').appendTo($td);

					break;

				default:
					var $input = $('<input type="text" name="'+name+'" spellcheck="false" class="fullfield" />').appendTo($td).val(val);
			}

			if (focus) if ($input) $input.focus();
			else focus = true;

			// add new row?
			if (!selected) {
				selected = true;

				$remove.addClass('enabled').click(function(){
					$tr.remove();
				});

				new wygwam_addSettingRow();
			}
		}
		else {
			$desc.html('').hide();
		}
	};

	$settingSelect.change(function(){
		var setting = $settingSelect.val(),
			val = (setting && settings[setting].val) ? settings[setting].val : '';
		showSetting(setting, val, true);
	});

	if (initialType) {
		$settingSelect.val(initialType);
		showSetting(initialType, initialVal);
	}

	return $tr;
};

new wygwam_addSettingRow();


})(jQuery);
