/*

	jQuery Tags Input Plugin 1.2.4
	
	Copyright (c) 2010 XOXCO, Inc
	
	Documentation for this plugin lives here:
	http://xoxco.com/clickable/jquery-tags-input
	
	Licensed under the MIT license:
	http://www.opensource.org/licenses/mit-license.php

	ben@xoxco.com

*/

(function($) {

	var delimiter = new Array();
	var tags_callbacks = new Array();
	var gotAutoComplete = false;
	
	$.fn.addTag = function(value,options) {
			var options = jQuery.extend({focus:false,callback:true},options);
			this.each(function() { 
				id = $(this).attr('id');

				var tagslist = $(this).val().split(delimiter[id]);
				if (tagslist[0] == '') { 
					tagslist = new Array();
				}

				value = jQuery.trim(value);
		
				if (options.unique) {
					skipTag = $(tagslist).tagExist(value);
				} else {
					skipTag = false; 
				}

				if (value !='' && skipTag != true) { 
					
					$('<span class="tag">'+value + '&nbsp;&nbsp;<a href="#" title="Remove tag" onclick="return $(\'#'+id + '\').removeTag(\'' + escape(value) + '\');">x</a></span>').insertBefore('#'+id+'_addTag');
					tagslist.push(value);
				
					$('#'+id+'_tag').val('');
					if (options.focus) {
						$('#'+id+'_tag').focus();
					} else {		
						$('#'+id+'_tag').blur();
					}
					
					if (options.callback && tags_callbacks[id] && tags_callbacks[id]['onAddTag']) {
						var f = tags_callbacks[id]['onAddTag'];
						f(value);
					}
					if(tags_callbacks[id] && tags_callbacks[id]['onChange'])
					{
						var i = tagslist.length;
						var f = tags_callbacks[id]['onChange'];
						f($(this), tagslist[i]);
					}					
				}
				$.fn.tagsInput.updateTagsField(this,tagslist);
		
			});		
			
			return false;
		};
		
	$.fn.removeTag = function(value) { 
			value = unescape(value);
			this.each(function() { 
				id = $(this).attr('id');
	
				var old = $(this).val().split(delimiter[id]);
	
				
				$('#'+id+'_tagsinput .tag').remove();
				str = '';
				for (i=0; i< old.length; i++) { 
					if (old[i]!=value) { 
						str = str + delimiter[id] +old[i];
					}
				}
				
				$.fn.tagsInput.importTags(this,str);

				if (tags_callbacks[id] && tags_callbacks[id]['onRemoveTag']) {
					var f = tags_callbacks[id]['onRemoveTag'];
					f(value);
				}
			});
					
			return false;
		};
	
	$.fn.tagExist = function(val) {
		if (jQuery.inArray(val, $(this)) == -1) {
		  return false; /* Cannot find value in array */
		} else {
		  return true; /* Value found */
		}
	};
	
	// clear all existing tags and import new ones from a string
	$.fn.importTags = function(str) {
		$('#'+id+'_tagsinput .tag').remove();
		$.fn.tagsInput.importTags(this,str);
	}
		
	$.fn.tagsInput = function(options) { 
    var settings = jQuery.extend({
      interactive:true,
      defaultText:'add a tag',
      minChars:0,
      width:'300px',
      height:'100px',
      'hide':true,
      'delimiter':',',
      autocomplete:{},
      'unique':true,
      removeWithBackspace:true,
      placeholderColor:'#666666'
    },options);

		this.each(function() { 
			if (settings.hide) { 
				$(this).hide();				
			}
				
			id = $(this).attr('id')
			
			data = jQuery.extend({
				pid:id,
				real_input: '#'+id,
				holder: '#'+id+'_tagsinput',
				input_wrapper: '#'+id+'_addTag',
				fake_input: '#'+id+'_tag'
			},settings);
	
	
			delimiter[id] = data.delimiter;
			
			if (settings.onAddTag || settings.onRemoveTag || settings.onChange) {
				tags_callbacks[id] = new Array();
				tags_callbacks[id]['onAddTag'] = settings.onAddTag;
				tags_callbacks[id]['onRemoveTag'] = settings.onRemoveTag;
				tags_callbacks[id]['onChange'] = settings.onChange;
			}
	
			var markup = '<div id="'+id+'_tagsinput" class="tagsinput"><div id="'+id+'_addTag">';
			
			if (settings.interactive) {
				markup = markup + '<input id="'+id+'_tag" value="" data-default="'+settings.defaultText+'" />';
			}
			
			markup = markup + '</div><div class="tags_clear"></div></div>';
			
			$(markup).insertAfter(this);

			$(data.holder).css('width',settings.width);
			$(data.holder).css('height',settings.height);
	
			if ($(data.real_input).val()!='') { 
				$.fn.tagsInput.importTags($(data.real_input),$(data.real_input).val());
			}		
			if (settings.interactive) { 
				$(data.fake_input).val($(data.fake_input).attr('data-default'));
				$(data.fake_input).css('color',settings.placeholderColor);		
		
				$(data.holder).bind('click',data,function(event) {
					$(event.data.fake_input).focus();
				});
			
				$(data.fake_input).bind('focus',data,function(event) {
					if ($(event.data.fake_input).val()==$(event.data.fake_input).attr('data-default')) { 
						$(event.data.fake_input).val('');
					}
					$(event.data.fake_input).css('color','#000000');		
				});
						
				if (settings.autocomplete_url != undefined) {
				  autocomplete_options = {source: settings.autocomplete_url};
				  for (attrname in settings.autocomplete) { 
				    autocomplete_options[attrname] = settings.autocomplete[attrname]; 
				  }
          
					$(data.fake_input).autocomplete(autocomplete_options).bind('autocompleteselect',data,function(event,ui) {
            gotAutoComplete = true;
            		$(event.data.real_input).addTag(ui.item.value);
					  //$(event.data.real_input).addTag(ui.item.value,{focus:true,unique:(settings.unique)});
					  return false;
          });
					
					
          $(data.fake_input).bind('blur',data,function(event) {
            setTimeout(function() {
              if(gotAutoComplete == true) {
                gotAutoComplete = false;
                return false;
              }
              if ( $(event.data.fake_input).val() != $(event.data.fake_input).attr('data-default')) {
                if((event.data.minChars <= $(event.data.fake_input).val().length) && (!event.data.maxChars || (event.data.maxChars >= $(event.data.fake_input).val().length))) {
                  $(event.data.real_input).addTag($(event.data.fake_input).val(),{focus:false,unique:(settings.unique)});						
                }
              }

              $(event.data.fake_input).val($(event.data.fake_input).attr('data-default'));
              $(event.data.fake_input).css('color',settings.placeholderColor);              
            }, 100);
            return false;
					});
			
				} else {
						// if a user tabs out of the field, create a new tag
						// this is only available if autocomplete is not used.
						$(data.fake_input).bind('blur',data,function(event) { 
							var d = $(this).attr('data-default');
							if ($(event.data.fake_input).val()!='' && $(event.data.fake_input).val()!=d) { 
								if( (event.data.minChars <= $(event.data.fake_input).val().length) && (!event.data.maxChars || (event.data.maxChars >= $(event.data.fake_input).val().length)) )
									$(event.data.real_input).addTag($(event.data.fake_input).val(),{focus:true,unique:(settings.unique)});
							} else {
								$(event.data.fake_input).val($(event.data.fake_input).attr('data-default'));
								$(event.data.fake_input).css('color',settings.placeholderColor);
							}
							return false;
						});
				
				}
				// if user types a comma, create a new tag
				$(data.fake_input).bind('keypress',data,function(event) { 
					if (event.which==event.data.delimiter.charCodeAt(0) || event.which==13 ) {
						if( (event.data.minChars <= $(event.data.fake_input).val().length) && (!event.data.maxChars || (event.data.maxChars >= $(event.data.fake_input).val().length)) )
							$(event.data.real_input).addTag($(event.data.fake_input).val(),{focus:true,unique:(settings.unique)});
						
						return false;
					}
				});
				//Delete last tag on backspace
				data.removeWithBackspace && $(data.fake_input).bind('keyup', function(event)
				{
					if(event.keyCode == 8 && $(this).val() == '')
					{
						 var last_tag = $(this).closest('.tagsinput').find('.tag:last').text();
						 var id = $(this).attr('id').replace(/_tag$/, '');
						 last_tag = last_tag.replace(/[\s]+x$/, '');
						 $('#' + id).removeTag(escape(last_tag));
						 $(this).trigger('focus');
					};
				});
				$(data.fake_input).blur();
			} // if settings.interactive
			return false;
		});
			
		return this;
	
	};
	
	$.fn.tagsInput.updateTagsField = function(obj,tagslist) { 
		id = $(obj).attr('id');
		$(obj).val(tagslist.join(delimiter[id]));
	};
	
	$.fn.tagsInput.importTags = function(obj,val) {			
		$(obj).val('');
		id = $(obj).attr('id');
		var tags = val.split(delimiter[id]);
		for (i=0; i<tags.length; i++) { 
			$(obj).addTag(tags[i],{focus:false,callback:false});
		}
		if(tags_callbacks[id] && tags_callbacks[id]['onChange'])
		{
			var f = tags_callbacks[id]['onChange'];
			f(obj, tags[i]);
		}
	};

})(jQuery);