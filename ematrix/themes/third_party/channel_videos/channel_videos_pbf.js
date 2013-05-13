;(function(global, $){
	//es5 strict mode
	"use strict";

	var ChannelVideos = global.ChannelVideos = global.ChannelVideos || {};
	ChannelVideos.Data = {};
	ChannelVideos.youtube = {};
	ChannelVideos.vimeo = {};

	// ----------------------------------------------------------------------

	ChannelVideos.SearchForVideos = function(e){
		e.preventDefault();
		var TargetBox = $(e.target).closest('.CVField');
		var Services = ChannelVideos.Data['FIELD'+TargetBox.data('field_id')].services;
		var i;
		var results = {};

		// Grab all input fields
		var Params = {};
		TargetBox.find('.SVWrapper .cvsearch').find('input[type=text], input[type=hidden]').each(function(){
			Params[jQuery(this).attr('rel')] = jQuery(this).val();
		});

		for (var i = 0; i < Services.length; i++) {
			TargetBox.find('.VideosResults .results-'+Services[i]).show().find('.LoadingVideos').show().siblings('.inner').empty();
			ChannelVideos[Services[i]].search_videos(Params, TargetBox);
		};
	};

	// ----------------------------------------------------------------------

	ChannelVideos.AddVideoResults = function(service, items, TargetBox){
		var Label = (service == 'youtube') ? 'Youtube' : 'Vimeo';
		var i, html = '';

		if (items.length == 0) {
			html += '<p>No Results Found...</p>';
		}

		for (var i = 0; i < items.length; i++) {
			html += '<div class="video" rel="'+service+'|'+items[i].id+'" id="'+service+'__'+items[i].id+'">';
			html += 	'<img src="' + items[i].img_url + '" width="100px" height="75px">';
			html += 	'<small>'+ items[i].title +'</small>';
			html += 	'<span>';
			html += 		'<a href="' + items[i].vid_url + '" class="play">&nbsp;</a>';
			html += 		'<a href="#" class="add">&nbsp;</a>';
			html += 	'</span>';
			html += '</div>';
		};

		html += '<br clear="all"></div>';

		TargetBox.find('.VideosResults .results-'+service).find('.LoadingVideos').hide().siblings('.inner').show().html(html);
		TargetBox.find('.VideosResults .video .play').colorbox({iframe:true, width: 450, height:375});
	};

	// ----------------------------------------------------------------------

	ChannelVideos.SubmitVideoUrl = function(e){
		var VideoURL = prompt("Video URL?", "");
		if (VideoURL == null) return false;

		var TargetBox = $(e.target).closest('div.CVField');
		var Services = ChannelVideos.Data['FIELD'+TargetBox.data('field_id')].services;
		var FieldID = TargetBox.attr('rel');

		TargetBox.find('.SVWrapperTR').show();

		for (var i = 0; i < Services.length; i++) {
			TargetBox.find('.VideosResults .results-'+Services[i]).show().find('.LoadingVideos').show().siblings('.inner').empty();
			ChannelVideos[Services[i]].submit_url(VideoURL, TargetBox);
		};
/*


*/
		return false;
	};

	// ----------------------------------------------------------------------

	ChannelVideos.DisableEnter = function(e){
		if (e.which == 13)	{
			$(e.target).closest('.CVField').find('.searchbutton').click();
			return false;
		}
	};

	// ----------------------------------------------------------------------

	ChannelVideos.AddVideoToTable = function(video, field_id) {
		var field_data = ChannelVideos.Data['FIELD'+field_id];
		var TargetBox = $('#ChannelVideos'+field_id);
		var html = '';

		TargetBox.find('#'+video.service+'__'+video.service_video_id).slideUp();

		var video_date = new Date();
		video_date.setTime( (video.video_date*1000) );

		if (field_data.layout == 'table') {
			html += '<tr class="CVItem">';
			html += 	'<td><a href="'+video.video_url+'" class="PlayVideo"><img src="'+video.video_img_url+'" width="100px" height="75px"></a></td>';
			html += 	'<td>'+video.video_title+'</td>';
			html += 	'<td>'+video.video_author+'</td>';
			html += 	'<td>'+(video.video_duration/60).toFixed(2)+' min</td>';
			html += 	'<td>'+video.video_views+'</td>';
			html += 	'<td>'+video_date.toDateString()+'</td>';
			html += 	'<td>';
			html += 		'<a href="javascript:void(0)" class="MoveVideo">&nbsp;</a>';
			html += 		'<a href="javascript:void(0)" class="DelVideo" data-id="'+video.video_id+'">&nbsp;</a>';

			if (video.video_id > 0) {
				html += '<input name="'+field_data.field_name+'[videos][0][video_id]" type="hidden" value="'+video.video_id+'">';
			} else {
				html += '<textarea name="'+field_data.field_name+'[videos][0][data]" style="display:none">'+JSON.stringify(video)+'</textarea>';
			}

			html += 	'</td>';
			html += '</tr>';
		} else {
			html += '<div class="CVItem VideoTile">';
			html += 	'<a href="'+video.video_url+'" class="PlayVideo"><img src="'+video.video_img_url+'" width="100px" height="75px"></a>';
			html += 	'<small>'+video.video_title+'</small>';
			html += 	'<span>';
			html += 		'<a href="javascript:void(0)" class="MoveVideo">&nbsp;</a>';
			html += 		'<a href="javascript:void(0)" class="DelVideo" data-id="'+video.video_id+'">&nbsp;</a>';

			if (video.video_id > 0) {
				html += '<input name="'+field_data.field_name+'[videos][0][video_id]" type="hidden" value="'+video.video_id+'">';
			} else {
				html += '<textarea name="'+field_data.field_name+'[videos][0][data]" style="display:none">'+JSON.stringify(video)+'</textarea>';
			}
			html += 	'</span>';
			html += '</div>';
		}

		TargetBox.find('.AssignedVideos .NoVideos').hide();
		TargetBox.find('.AssignedVideos').append(html);
		ChannelVideos.SyncOrderNumbers();
		TargetBox.find('.AssignedVideos .CVItem .PlayVideo').colorbox({iframe:true, width: 450, height:375});
	};

	// ----------------------------------------------------------------------

	ChannelVideos.AddVideo = function(e){
		var Parent = jQuery(e.target).closest('div.video');
		var TargetBox = jQuery(e.target).closest('div.CVField');
		var field_id = TargetBox.data('field_id');

		jQuery(e.target).addClass('loading');

		var Params = {};
		Params.ajax_method = 'get_video';
		Params.service = Parent.attr('rel').split('|')[0];
		Params.video_id = Parent.attr('rel').split('|')[1];
		Params.XID = ChannelVideos.XID;
		Params.field_id = field_id;
		Params.field_name = ChannelVideos.Data['FIELD'+field_id].field_name;
		Params.field_layout = ChannelVideos.Data['FIELD'+field_id].layout;

		if (Params.service == 'youtube') {
			ChannelVideos.youtube.get_video(Params.video_id, field_id, ChannelVideos.AddVideoToTable);

			return false;
		} else {
			ChannelVideos.vimeo.get_video(Params.video_id, field_id, ChannelVideos.AddVideoToTable);
		}

		return false;
	};

	// ----------------------------------------------------------------------

	ChannelVideos.youtube.search_videos = function(Params, TargetBox){
		var i, entry, video_id;

		if (Params.author) Params.keywords += ' ' +Params.author;

		$.ajax({
			crossDomain: true,
			dataType: "json",
			url: 'https://gdata.youtube.com/feeds/api/videos/?v=2&alt=json&callback=?',
			data: {q:Params.keywords, 'max-results':Params.limit},
			success: function(rdata){
				var Videos = [];

				if (typeof rdata.feed === 'undefined') {
					ChannelVideos.AddVideoResults('youtube', Videos, TargetBox);
				}

				if (typeof rdata.feed.entry === 'undefined') {
					ChannelVideos.AddVideoResults('youtube', Videos, TargetBox);
				}

				for (var i = 0; i < rdata.feed.entry.length; i++) {
					entry = rdata.feed.entry[i];
					video_id = entry.media$group.yt$videoid.$t;

					Videos.push({
						id: video_id,
						title: entry.title.$t,
						img_url: 'https://i.ytimg.com/vi/' + video_id + '/default.jpg',
						vid_url: 'https://www.youtube.com/embed/'+video_id,
					});

				};

				ChannelVideos.AddVideoResults('youtube', Videos, TargetBox);
			}
		});
	};

	// ----------------------------------------------------------------------

	ChannelVideos.youtube.submit_url = function(url, TargetBox){
		var servicebox = TargetBox.find('.VideosResults .results-youtube').show();
		var inner = servicebox.find('.inner');
		var loading = servicebox.find('.LoadingVideos');
		var id = null;
		var parts;

		if (url.indexOf('youtube') === -1 && url.indexOf('youtu.be') === -1) {
			loading.hide();
			inner.html('<p>Not a valid Youtube URL</p>');
			return;
		}

		// http://www.youtube.com/watch?v=9bZkp7q19f0
		if (url.indexOf('youtube.com/watch') > 0) {
			parts = ChannelVideos.parse_url(url);
			id = ChannelVideos.getQueryVariable(parts.query, 'v');
		}

		// http://youtu.be/9bZkp7q19f0
		else if (url.indexOf('youtu.be') > 0) {
			parts = url.split('/');
			id = parts[(parts.length-1)];
		}

		// http://www.youtube.com/embed/9bZkp7q19f0
		else if (url.indexOf('youtube.com/embed') > 0) {
			parts = url.split('/');
			id = parts[(parts.length-1)];
		}

		if (id == null) {
			loading.hide();
			inner.html('<p>Could not parse Youtube ID from that URL</p>');
			return;
		}

		$.ajax({
			crossDomain: true,
			dataType: "json",
			url: 'https://gdata.youtube.com/feeds/api/videos/'+id+'?v=2&alt=json&callback=?',
			success: function(rdata){
				var video_id;
				loading.hide();

				if (typeof rdata.entry === 'undefined') {
					inner.html('<p>Youtube could not find the video. (ID: '+id+')</p>');
				}

				video_id = rdata.entry.media$group.yt$videoid.$t;

				ChannelVideos.AddVideoResults('youtube', [{
					id: video_id,
					title: rdata.entry.title.$t,
					img_url: 'https://i.ytimg.com/vi/' + video_id + '/default.jpg',
					vid_url: 'https://www.youtube.com/embed/'+video_id
				}], TargetBox);
			}
		});
	};

	// ----------------------------------------------------------------------

	ChannelVideos.youtube.get_video = function(id, field_id, callback){
		$.ajax({
			crossDomain: true,
			dataType: "json",
			url: 'https://gdata.youtube.com/feeds/api/videos/'+id+'?v=2&alt=json&callback=?',
			success: function(rdata){

				var video_id = rdata.entry.media$group.yt$videoid.$t;
				var Video = {};
				Video.service = 'youtube';
				Video.service_video_id = video_id;
				Video.video_id = 0;
				Video.video_url = 'http://www.youtube.com/embed/' + video_id;
				Video.video_img_url = 'http://i.ytimg.com/vi/' + video_id + '/default.jpg';
				Video.video_title = rdata.entry.title.$t;
				Video.video_desc = rdata.entry.media$group.media$description.$t;
				Video.video_username = rdata.entry.author[0].name.$t;
				Video.video_author = rdata.entry.author[0].name.$t;
				Video.video_author_id = 0;
				Video.video_duration = rdata.entry.media$group.yt$duration.seconds;
				Video.video_views = rdata.entry.yt$statistics.viewCount
				Video.video_date = (new Date(rdata.entry.published.$t).getTime()/1000);

				callback(Video, field_id);
			}
		});
	}

	// ----------------------------------------------------------------------

	ChannelVideos.vimeo.search_videos = function(Params, TargetBox){
		var i, entry, img_url, thumb;

		var servicebox = TargetBox.find('.VideosResults .results-vimeo').show();
		var inner = servicebox.find('.inner');
		var loading = servicebox.find('.LoadingVideos');

		var urlparams = {};
		urlparams.format = 'jsonp';
		urlparams.method = 'vimeo.videos.search';
		urlparams.query = Params.keywords;
		if (Params.author) urlparams.user_id = Params.author;
		urlparams.per_page = Params.limit;
		urlparams.full_response = '1';
		var url = ChannelVideos.vimeo.GetTheSignedUrl($.param(urlparams));
		//url = decodeURIComponent(url.replace(/\+/g,  " "));

		$.ajax({
			crossDomain: true,
			dataType: "jsonp",
			url: url,
			jsonp: false, jsonpCallback: "vimeoCallback",
			cache: true, // Adding the extra cache params breaks it!!
			success: function(rdata){
				if (rdata.stat != 'ok') {
					loading.hide();
					inner.html('<p>The vimeo request failed!</p>');
					return;
				}

				if (rdata.videos.on_this_page == 0) {
					loading.hide();
					inner.html('<p>No results found..</p>');
					return;
				}

				var Videos = [];

				for (var i = 0; i < rdata.videos.video.length; i++) {
					entry = rdata.videos.video[i];

					for (var ii = entry.thumbnails.thumbnail.length - 1; ii >= 0; ii--) {
						thumb = entry.thumbnails.thumbnail[ii];
						if (thumb.width == '100' || thumb.height == '100') {
							img_url = thumb._content;
						}
					};

					Videos.push({
						id: entry.id,
						title: entry.title,
						img_url: img_url,
						vid_url: 'http://player.vimeo.com/video/' + entry.id + '?title=0&byline=0&portrait=0'
					});

				};

				ChannelVideos.AddVideoResults('vimeo', Videos, TargetBox);
			}
		});

	};

	// ----------------------------------------------------------------------

	ChannelVideos.vimeo.submit_url = function(url, TargetBox){
		var servicebox = TargetBox.find('.VideosResults .results-vimeo').show();
		var inner = servicebox.find('.inner');
		var loading = servicebox.find('.LoadingVideos');
		var id = null;
		var parts, entry, thumb, img_url;

		if (url.indexOf('vimeo') === -1) {
			loading.hide();
			inner.html('<p>Not a valid Vimeo URL</p>');
			return;
		}

		// https://vimeo.com/58161697
		if (url.indexOf('vimeo.com/') > 0) {
			parts = url.split('/');
			id = parts[(parts.length-1)];
		}

		if (id == null) {
			loading.hide();
			inner.html('<p>Could not parse Vimeo ID from that URL</p>');
			return;
		}

		var urlparams = {};
		urlparams.format = 'jsonp';
		urlparams.method = 'vimeo.videos.getInfo';
		urlparams.video_id = id;
		urlparams.full_response = '1';
		var url = ChannelVideos.vimeo.GetTheSignedUrl($.param(urlparams));
		//url = decodeURIComponent(url.replace(/\+/g,  " "));

		$.ajax({
			crossDomain: true,
			dataType: "jsonp",
			url: url,
			jsonp: false, jsonpCallback: "vimeoCallback",
			cache: true, // Adding the extra cache params breaks it!!
			success: function(rdata){
				if (rdata.stat != 'ok') {
					loading.hide();
					inner.html('<p>The vimeo request failed!</p>');
					return;
				}

				if (rdata.video.length == 0) {
					loading.hide();
					inner.html('<p>No results found..</p>');
					return;
				}

				var Videos = [];

				for (var i = 0; i < rdata.video.length; i++) {
					entry = rdata.video[i];

					for (var ii = entry.thumbnails.thumbnail.length - 1; ii >= 0; ii--) {
						thumb = entry.thumbnails.thumbnail[ii];
						if (thumb.width == '100' || thumb.height == '100') {
							img_url = thumb._content;
						}
					};

					Videos.push({
						id: entry.id,
						title: entry.title,
						img_url: img_url,
						vid_url: 'http://player.vimeo.com/video/' + entry.id + '?title=0&byline=0&portrait=0'
					});

				};

				ChannelVideos.AddVideoResults('vimeo', Videos, TargetBox);
			}
		});

		return;
	};

	// ----------------------------------------------------------------------

	ChannelVideos.vimeo.get_video = function(id, field_id, callback){
		var entry, thumb, img_url;

		var urlparams = {};
		urlparams.format = 'jsonp';
		urlparams.method = 'vimeo.videos.getInfo';
		urlparams.video_id = id;
		urlparams.full_response = '1';
		var url = ChannelVideos.vimeo.GetTheSignedUrl($.param(urlparams));
		//url = decodeURIComponent(url.replace(/\+/g,  " "));

		$.ajax({
			crossDomain: true,
			dataType: "jsonp",
			url: url,
			jsonp: false, jsonpCallback: "vimeoCallback",
			cache: true, // Adding the extra cache params breaks it!!
			success: function(rdata){
				if (rdata.stat != 'ok') {
					loading.hide();
					inner.html('<p>The vimeo request failed!</p>');
					return;
				}

				entry = rdata.video[0];

				for (var ii = entry.thumbnails.thumbnail.length - 1; ii >= 0; ii--) {
					thumb = entry.thumbnails.thumbnail[ii];
					if (thumb.width == '100' || thumb.height == '100') {
						img_url = thumb._content;
					}
				};

				var Video = {};
				Video.service = 'vimeo';
				Video.service_video_id = entry.id;
				Video.video_id = 0;
				Video.video_url = 'http://player.vimeo.com/video/' + entry.id + '?title=0&byline=0&portrait=0';
				Video.video_img_url = img_url;
				Video.video_title = entry.title;
				Video.video_desc = entry.description;
				Video.video_username = entry.owner.username;
				Video.video_author = entry.owner.display_name;
				Video.video_author_id = entry.owner.id;
				Video.video_duration = entry.duration;
				Video.video_views = entry.number_of_plays;
				Video.video_date = (new Date(entry.upload_date.replace(' ', 'T')+'-00:00').getTime()/1000);

				callback(Video, field_id);
			}
		});


	}

	// ----------------------------------------------------------------------

	ChannelVideos.vimeo.GetTheSignedUrl = function(params) {
        var recvd_resp = "nothing";
        var oauth = ChannelVideos.OAuthSimple();

        try {
            var oauthObject = ChannelVideos.OAuthSimple().sign({
                path: 'https://vimeo.com/api/rest/v2',
                parameters: params,
                signatures: {
                    api_key: ChannelVideos.vimeo.consumer_key,
                    shared_secret: ChannelVideos.vimeo.shared_secret
                }
            });
            recvd_resp = oauthObject.signed_url;
        }
        catch (e) {
            alert(e);
        }

        return recvd_resp;
    }

    // ----------------------------------------------------------------------

	ChannelVideos.parse_url = function(str, component) {
		var key = ['source', 'scheme', 'authority', 'userInfo', 'user', 'pass', 'host', 'port',
		        'relative', 'path', 'directory', 'file', 'query', 'fragment'],
		ini = (this.php_js && this.php_js.ini) || {},
		mode = (ini['phpjs.parse_url.mode'] &&
		  ini['phpjs.parse_url.mode'].local_value) || 'php',
		parser = {
		  php: /^(?:([^:\/?#]+):)?(?:\/\/()(?:(?:()(?:([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?()(?:(()(?:(?:[^?#\/]*\/)*)()(?:[^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
		  strict: /^(?:([^:\/?#]+):)?(?:\/\/((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?))?((((?:[^?#\/]*\/)*)([^?#]*))(?:\?([^#]*))?(?:#(.*))?)/,
		  loose: /^(?:(?![^:@]+:[^:@\/]*@)([^:\/?#.]+):)?(?:\/\/\/?)?((?:(([^:@]*):?([^:@]*))?@)?([^:\/?#]*)(?::(\d*))?)(((\/(?:[^?#](?![^?#\/]*\.[^?#\/.]+(?:[?#]|$)))*\/?)?([^?#\/]*))(?:\?([^#]*))?(?:#(.*))?)/ // Added one optional slash to post-scheme to catch file:/// (should restrict this)
		};

		var m = parser[mode].exec(str),
		uri = {},
		i = 14;
		while (i--) {
		if (m[i]) {
		  uri[key[i]] = m[i];
		}
		}

		if (component) {
		return uri[component.replace('PHP_URL_', '').toLowerCase()];
		}
		if (mode !== 'php') {
		var name = (ini['phpjs.parse_url.queryKey'] &&
		    ini['phpjs.parse_url.queryKey'].local_value) || 'queryKey';
		parser = /(?:^|&)([^&=]*)=?([^&]*)/g;
		uri[name] = {};
		uri[key[12]].replace(parser, function ($0, $1, $2) {
		  if ($1) {uri[name][$1] = $2;}
		});
		}
		delete uri.source;
		return uri;
	}

	// ----------------------------------------------------------------------

	ChannelVideos.getQueryVariable = function(url, variable) {
	    var query = url;
	    var vars = query.split('&');
	    for (var i = 0; i < vars.length; i++) {
	        var pair = vars[i].split('=');
	        if (decodeURIComponent(pair[0]) == variable) {
	            return decodeURIComponent(pair[1]);
	        }
	    }
	    return null;
	}

	// ----------------------------------------------------------------------

	//OAuthSimple Class :)
    //ChannelVideos.vimeo.consumer_key = 'be6f5726995c952d80c62fea3bfdacbd92f477ae';
    //ChannelVideos.vimeo.shared_secret = '5e082c746fefa4bbad7217d7ca8aef12dae4dab5';
    ChannelVideos.vimeo.consumer_key = '1a8a81eaf6658d0dbb955f0386f484c1f9b55ece';
    ChannelVideos.vimeo.shared_secret = '2ffac38d1ee9eac6a2389269aa19429a927ff07b';

    ChannelVideos.OAuthSimple = function (consumer_key, shared_secret) {
        this._secrets = {};


        // General configuration options.
        if (consumer_key != null)
            this._secrets['consumer_key'] = consumer_key;
        if (shared_secret != null)
            this._secrets['shared_secret'] = shared_secret;
        this._default_signature_method = "HMAC-SHA1";
        this._action = "GET";
        this._nonce_chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz";

        /* set the parameters either from a hash or a string
        *
        * @param {string,object} List of parameters for the call, this can either be a URI string (e.g. "foo=bar&gorp=banana" or an object/hash)
        */

        this.setParameters = function (parameters) {
            if (parameters == null)
                parameters = {};
            if (typeof (parameters) == 'string')
                parameters = this._parseParameterString(parameters);
            this._parameters = parameters;
            if (this._parameters['oauth_nonce'] == null)
                this._getNonce();
            if (this._parameters['oauth_timestamp'] == null)
                this._getTimestamp();
            if (this._parameters['oauth_method'] == null)
                this.setSignatureMethod();
            if (this._parameters['oauth_consumer_key'] == null)
                this._getApiKey();
            if (this._parameters['oauth_token'] == null)
                this._getAccessToken();

            return this;
        };

        /* convienence method for setParameters
        *
        * @param parameters {string,object} See .setParameters
        */
        this.setQueryString = function (parameters) {
            return this.setParameters(parameters);
        };

        /* Set the target URL (does not include the parameters)
        *
        * @param path {string} the fully qualified URI (excluding query arguments) (e.g "http://example.org/foo")
        */
        this.setURL = function (path) {
            if (path == '')
                throw ('No path specified for OAuthSimple.setURL');
            if (path.indexOf(' ') != -1) {
                $("#path").select();
                throw ('Space detected in request path/URL');
            };
            this._path = path;
            return this;
        };

        /* convienence method for setURL
        *
        * @param path {string} see .setURL
        */
        this.setPath = function (path) {
            return this.setURL(path);
        };

        /* set the "action" for the url, (e.g. GET,POST, DELETE, etc.)
        *
        * @param action {string} HTTP Action word.
        */
        this.setAction = function (action) {
            if (action == null)
                action = "GET";
            action = action.toUpperCase();
            if (action.match('[^A-Z]'))
                throw ('Invalid action specified for OAuthSimple.setAction');
            this._action = action;
            return this;
        };

        /* set the signatures (as well as validate the ones you have)
        *
        * @param signatures {object} object/hash of the token/signature pairs {api_key:, shared_secret:, oauth_token: oauth_secret:}
        */
        this.setTokensAndSecrets = function (signatures) {
            if (signatures)
                for (var i in signatures)
                    this._secrets[i] = signatures[i];
            // Aliases
            if (this._secrets['api_key'])
                this._secrets.consumer_key = this._secrets.api_key;
            if (this._secrets['access_token'])
                this._secrets.oauth_token = this._secrets.access_token;
            if (this._secrets['access_secret'])
                this._secrets.oauth_secret = this._secrets.access_secret;
            // Gauntlet
            if (this._secrets.consumer_key == null)
                throw ('Missing required consumer_key in OAuthSimple.setTokensAndSecrets');
            if (this._secrets.shared_secret == null)
                throw ('Missing required shared_secret in OAuthSimple.setTokensAndSecrets');
            if ((this._secrets.oauth_token != null) && (this._secrets.oauth_secret == null))
                throw ('Missing oauth_secret for supplied oauth_token in OAuthSimple.setTokensAndSecrets');
            return this;
        };

        /* set the signature method (currently only Plaintext or SHA-MAC1)
        *
        * @param method {string} Method of signing the transaction (only PLAINTEXT and SHA-MAC1 allowed for now)
        */
        this.setSignatureMethod = function (method) {
            if (method == null)
                method = this._default_signature_method;
            //TODO: accept things other than PlainText or SHA-MAC1
            if (method.toUpperCase().match(/(PLAINTEXT|HMAC-SHA1)/) == null)
                throw ('Unknown signing method specified for OAuthSimple.setSignatureMethod');
            this._parameters['oauth_signature_method'] = method.toUpperCase();
            return this;
        };

        /* sign the request
        *
        * note: all arguments are optional, provided you've set them using the
        * other helper functions.
        *
        * @param args {object} hash of arguments for the call
        * {action:, path:, parameters:, method:, signatures:}
        * all arguments are optional.
        */
        this.sign = function (args) {
            if (args == null)
                args = {};
            // Set any given parameters
            if (args['action'] != null)
                this.setAction(args['action']);
            if (args['path'] != null)
                this.setPath(args['path']);
            if (args['method'] != null)
                this.setSignatureMethod(args['method']);
            this.setTokensAndSecrets(args['signatures']);
            if (args['parameters'] != null)
                this.setParameters(args['parameters']);
            // check the parameters
            var normParams = this._normalizedParameters();
            var sig = this._generateSignature(normParams);
            this._parameters['oauth_signature'] = sig.signature;
            return {
                parameters: this._parameters,
                sig_string: sig.sig_string,
                signature: this._oauthEscape(this._parameters['oauth_signature']),
                signed_url: this._path + '?' + this._normalizedParameters(),
                header: this.getHeaderString()
            };
        };

        /* Return a formatted "header" string
        *
        * NOTE: This doesn't set the "Authorization: " prefix, which is required.
        * I don't set it because various set header functions prefer different
        * ways to do that.
        *
        * @param args {object} see .sign
        */
        this.getHeaderString = function (args) {
            if (this._parameters['oauth_signature'] == null)
                this.sign(args);

            var result = 'OAuth ';
            for (var pName in this._parameters) {
                if (pName.match(/^oauth/) == null)
                    continue;
                if ((this._parameters[pName]) instanceof Array) {
                    var pLength = this._parameters[pName].length;
                    for (var j = 0; j < pLength; j++) {
                        result += pName + '="' + this._oauthEscape(this._parameters[pName][j]) + '" ';
                    }
                }
                else {
                    result += pName + '="' + this._oauthEscape(this._parameters[pName]) + '" ';
                }
            }
            return result;
        };

        // Start Private Methods.

        /* convert the parameter string into a hash of objects.
        *
        */
        this._parseParameterString = function (paramString) {
            var elements = paramString.split('&');
            var result = {};
            for (var element = elements.shift(); element; element = elements.shift()) {
                var keyToken = element.split('=');
                var value;
                if (keyToken[1])
                    value = decodeURIComponent(keyToken[1]);
                if (result[keyToken[0]]) {
                    if (!(result[keyToken[0]] instanceof Array)) {
                        result[keyToken[0]] = Array(result[keyToken[0]], value);
                    }
                    else {
                        result[keyToken[0]].push(value);
                    }
                }
                else {
                    result[keyToken[0]] = value;
                }
            }
            return result;
        };

        this._oauthEscape = function (string) {
            if (string == null)
                return "";
            if (string instanceof Array) {
                throw ('Array passed to _oauthEscape');
            }
            return encodeURIComponent(string).replace(/\!/g, "%21").
            replace(/\*/g, "%2A").
            replace(/'/g, "%27").
            replace(/\(/g, "%28").
            replace(/\)/g, "%29");
        };

        this._getNonce = function (length) {
            if (length == null)
                length = 5;
            var result = "";
            var cLength = this._nonce_chars.length;
            for (var i = 0; i < length; i++) {
                var rnum = Math.floor(Math.random() * cLength);
                result += this._nonce_chars.substring(rnum, rnum + 1);
            }
            this._parameters['oauth_nonce'] = result;
            return result;
        };

        this._getApiKey = function () {
            if (this._secrets.consumer_key == null)
                throw ('No consumer_key set for OAuthSimple.');
            this._parameters['oauth_consumer_key'] = this._secrets.consumer_key;
            return this._parameters.oauth_consumer_key;
        };

        this._getAccessToken = function () {
            if (this._secrets['oauth_secret'] == null)
                return '';
            if (this._secrets['oauth_token'] == null)
                throw ('No oauth_token (access_token) set for OAuthSimple.');
            this._parameters['oauth_token'] = this._secrets.oauth_token;
            return this._parameters.oauth_token;
        };

        this._getTimestamp = function () {
            var d = new Date();
            var ts = Math.floor(d.getTime() / 1000);
            this._parameters['oauth_timestamp'] = ts;
            return ts;
        };

        this._normalizedParameters = function () {
            var elements = new Array();
            var paramNames = [];
            var ra = 0;

            for (var paramName in this._parameters) {
                if (ra++ > 1000)
                    throw ('runaway 1');
                paramNames.unshift(paramName);
            }
            paramNames = paramNames.sort();
            var pLen = paramNames.length;
            for (var i = 0; i < pLen; i++) {
                paramName = paramNames[i];
                //skip secrets.
                if (paramName.match(/\w+_secret/))
                    continue;
                if (this._parameters[paramName] instanceof Array) {
                    var sorted = this._parameters[paramName].sort();
                    var spLen = sorted.length;
                    for (var j = 0; j < spLen; j++) {
                        if (ra++ > 1000)
                            throw ('runaway 1');
                        elements.push(this._oauthEscape(paramName) + '=' +
                                  this._oauthEscape(sorted[j]));
                    }
                    continue;
                }
                elements.push(this._oauthEscape(paramName) + '=' +
                              this._oauthEscape(this._parameters[paramName]));
            }
            return elements.join('&');
        };

        this.b64_hmac_sha1 = function (k, d, _p, _z) {
            // heavily optimized and compressed version of http://pajhome.org.uk/crypt/md5/sha1.js
            // _p = b64pad, _z = character size; not used here but I left them available just in case
            if (!_p) { _p = '='; } if (!_z) { _z = 8; } function _f(t, b, c, d) { if (t < 20) { return (b & c) | ((~b) & d); } if (t < 40) { return b ^ c ^ d; } if (t < 60) { return (b & c) | (b & d) | (c & d); } return b ^ c ^ d; } function _k(t) { return (t < 20) ? 1518500249 : (t < 40) ? 1859775393 : (t < 60) ? -1894007588 : -899497514; } function _s(x, y) { var l = (x & 0xFFFF) + (y & 0xFFFF), m = (x >> 16) + (y >> 16) + (l >> 16); return (m << 16) | (l & 0xFFFF); } function _r(n, c) { return (n << c) | (n >>> (32 - c)); } function _c(x, l) { x[l >> 5] |= 0x80 << (24 - l % 32); x[((l + 64 >> 9) << 4) + 15] = l; var w = [80], a = 1732584193, b = -271733879, c = -1732584194, d = 271733878, e = -1009589776; for (var i = 0; i < x.length; i += 16) { var o = a, p = b, q = c, r = d, s = e; for (var j = 0; j < 80; j++) { if (j < 16) { w[j] = x[i + j]; } else { w[j] = _r(w[j - 3] ^ w[j - 8] ^ w[j - 14] ^ w[j - 16], 1); } var t = _s(_s(_r(a, 5), _f(j, b, c, d)), _s(_s(e, w[j]), _k(j))); e = d; d = c; c = _r(b, 30); b = a; a = t; } a = _s(a, o); b = _s(b, p); c = _s(c, q); d = _s(d, r); e = _s(e, s); } return [a, b, c, d, e]; } function _b(s) { var b = [], m = (1 << _z) - 1; for (var i = 0; i < s.length * _z; i += _z) { b[i >> 5] |= (s.charCodeAt(i / 8) & m) << (32 - _z - i % 32); } return b; } function _h(k, d) { var b = _b(k); if (b.length > 16) { b = _c(b, k.length * _z); } var p = [16], o = [16]; for (var i = 0; i < 16; i++) { p[i] = b[i] ^ 0x36363636; o[i] = b[i] ^ 0x5C5C5C5C; } var h = _c(p.concat(_b(d)), 512 + d.length * _z); return _c(o.concat(h), 512 + 160); } function _n(b) { var t = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/", s = ''; for (var i = 0; i < b.length * 4; i += 3) { var r = (((b[i >> 2] >> 8 * (3 - i % 4)) & 0xFF) << 16) | (((b[i + 1 >> 2] >> 8 * (3 - (i + 1) % 4)) & 0xFF) << 8) | ((b[i + 2 >> 2] >> 8 * (3 - (i + 2) % 4)) & 0xFF); for (var j = 0; j < 4; j++) { if (i * 8 + j * 6 > b.length * 32) { s += _p; } else { s += t.charAt((r >> 6 * (3 - j)) & 0x3F); } } } return s; } function _x(k, d) { return _n(_h(k, d)); } return _x(k, d);
        };

        this._generateSignature = function () {

            var secretKey = this._oauthEscape(this._secrets.shared_secret) + '&' +
                this._oauthEscape(this._secrets.oauth_secret);
            if (this._parameters['oauth_signature_method'] == 'PLAINTEXT') {
                return { sig_string: null, signature: secretKey };
            }
            if (this._parameters['oauth_signature_method'] == 'HMAC-SHA1') {
                var sigString = this._oauthEscape(this._action) + '&' + this._oauthEscape(this._path) + '&' + this._oauthEscape(this._normalizedParameters());
                return { 'sig_string': sigString, 'signature': this.b64_hmac_sha1(secretKey, sigString) };
            }
            return null;
        };

        return this;
    }


    function GetTheSignedUrl() {
        var recvd_resp = "nothing";
        var oauth = OAuthSimple();
        try {
            var oauthObject = OAuthSimple().sign({
                path: 'http://vimeo.com/api/rest/v2',
                parameters: 'format=jsonp&channel_id=6513&method=vimeo.channels.getVideos&sort=newest&page=1&per_page=50',
                signatures: {
                    api_key: consumer_key,
                    shared_secret: shared_secret
                }
            });
            recvd_resp = oauthObject.signed_url;
        }
        catch (e) {
            alert(e);
        }

        return recvd_resp;
    }
    /* --------------------------------End of Simple OAuth Class------------------------------------- */


}(window, jQuery));

// ----------------------------------------------------------------------

$(document).ready(function() {

	var CVField = $('.CVField');

	CVField.find('.SearchVideos').click(ChannelVideos.ToggleSearchVideos);
	CVField.find('.SearchVideos input[type=text]').keypress(ChannelVideos.DisableEnter);
	CVField.find('.SVWrapper .Button').click(ChannelVideos.SearchForVideos);
	//CVField.find('.SVWrapper .Button').click(ChannelVideos.SearchForVideos);
	CVField.find('.SubmitVideoUrl').click(ChannelVideos.SubmitVideoUrl);
	CVField.find('.AssignedVideos').sortable({
		cursor: 'move', opacity: 0.6, handle: '.MoveVideo', update:ChannelVideos.SyncOrderNumbers,
		helper: function(event, ui){
			ui.children().each(function() {
				$(this).width($(this).width());
			});
			return ui;
		},
		forcePlaceholderSize: true,
		start: function(event, ui){
			ui.placeholder.html('<td colspan="20"></td>');
		},
		placeholder: 'cvideo-reorder-state-highlight'
	});
	CVField.find('.DelVideo').live('click', ChannelVideos.DelVideo);
	CVField.find('.ClearVideoSearch').live('click', ChannelVideos.ClearVideoSearch);

	CVField.find('.AssignedVideos .CVItem .PlayVideo').colorbox({iframe:true, width: 450, height:375});

	CVField.delegate('.VideosResults .video .add', 'click', ChannelVideos.AddVideo);

	/*
	//CVField.find('.RefreshVideo').live('click', ChannelVideos.RefreshVideo);
	*/
});

//********************************************************************************* //

ChannelVideos.ToggleSearchVideos = function(event){
	var Target = $(event.target).closest('.CVTable').find('.SVWrapperTR').toggle();

	return false;
};

//********************************************************************************* //

ChannelVideos.DelVideo = function(e){

	VideoID = $(e.target).data('id');

	// Send Ajax
	if (VideoID != false) {
		$.get(ChannelVideos.AJAX_URL, {video_id: VideoID, ajax_method: 'delete_video'}, function(){

		});
	}

	$(e.target).closest('.CVItem').fadeOut('slow', function(){ $(this).remove(); ChannelVideos.SyncOrderNumbers(); });

};

//********************************************************************************* //

ChannelVideos.ClearVideoSearch = function(Event){

	var TargetBox = jQuery(Event.target).closest('div.CVField');

	TargetBox.find('.VideosResults .inner').empty();

	return false;
}

//********************************************************************************* //

ChannelVideos.SyncOrderNumbers = function(){

	// Loop over all Channel Videos Fields
	$('.CVField').each(function(FieldIndex, VideoField){


		// Loop over all individual Videos
		jQuery(VideoField).find('.CVItem').each(function(VideoIndex, VideoItem){

			// Loop Over all Input Elements of the Relation Item
			jQuery(VideoItem).find('input, textarea, select').each(function(){
				attr = jQuery(this).attr('name').replace(/\[videos\]\[.*?\]/, '[videos][' + (VideoIndex+1) + ']');
				jQuery(this).attr('name', attr);
			});
		});

		// Add Zebra
		jQuery(VideoField).find('.CVItem').removeClass('odd');
		jQuery(VideoField).find('.CVItem:odd').addClass('odd');
	});

};

//********************************************************************************* //
