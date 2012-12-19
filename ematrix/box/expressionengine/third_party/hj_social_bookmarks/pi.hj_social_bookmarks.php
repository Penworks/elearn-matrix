<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

require_once PATH_THIRD . 'hj_social_bookmarks/config.php';

/**
 * HJ Social Bookmarks Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			Berry Timmermans
 * @copyright		Copyright (c) 2010, Berry Timmermans
 * @link			http://www.berrytimmermans.nl/add-ons/hj-social-bookmarks/
 */

$plugin_info = Array(
	'pi_name' 			=> HJ_SB_NAME,
	'pi_version' 		=> HJ_SB_VERSION,
	'pi_author' 		=> HJ_SB_AUTHOR,
	'pi_author_url' 	=> HJ_SB_AUTHOR_URL,
	'pi_description' 	=> HJ_SB_DESCRIPTION,
	'pi_usage' 			=> Hj_social_bookmarks::usage()
);

class Hj_social_bookmarks {

	public $return_data = '';

	// --------------------------------------------------------------------

  	public function __construct() {
  		    
	    $this->EE =& get_instance();
	    		    
		$sites = $this->EE->TMPL->fetch_param('sites');
		$entry_id = $this->EE->TMPL->fetch_param('entry_id');
		$field_name = $this->EE->TMPL->fetch_param('field_name');
		$show_hyperlink_text = $this->EE->TMPL->fetch_param('show_hyperlink_text');
		$pinterest_media = (!$this->EE->TMPL->fetch_param('pinterest_media')) ? '' : urlencode($this->EE->TMPL->fetch_param('pinterest_media'));
		$tweet_via = (!$this->EE->TMPL->fetch_param('tweet_via')) ? '' : '&amp;via=' . $this->EE->TMPL->fetch_param('tweet_via');
		$class_name = (!$this->EE->TMPL->fetch_param('class')) ? 'hj_social_bookmarks' : $this->EE->TMPL->fetch_param('class');
		
	    $protocol = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') ? 'https' : 'http');
	    $permalink = urlencode($protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
	    			
		if(isset($field_name) && $field_name != '') {

			$query = $this->EE->db->query("SELECT * FROM exp_channel_fields WHERE field_name = '$field_name'");
					
			if($query->num_rows() > 0) {
		
				foreach($query->result_array() as $row)	{
				
					$field_id = 'field_id_' . $row['field_id'];
											
				}
			
			}

		}
		
		if(isset($entry_id) && $entry_id != '' && ctype_digit($entry_id)) { 
					
			$query = $this->EE->db->query("SELECT *
				FROM exp_channel_titles
				LEFT JOIN exp_channel_data ON (exp_channel_titles.entry_id = exp_channel_data.entry_id)
				LEFT JOIN exp_sites ON (exp_channel_titles.site_id = exp_sites.site_id)
				WHERE exp_channel_titles.entry_id = $entry_id");
		
			if($query->num_rows() > 0) {
				
				foreach($query->result_array() as $row)	{
										
					$title = str_replace('+', '%20', urlencode($row['title']));
					$site_label = str_replace('+', '%20', urlencode($row['site_label']));
					
					if(isset($field_id) && $field_id != '') {
												
						$excerpt = strip_tags($row[$field_id]);
						$excerpt = htmlspecialchars(html_entity_decode($excerpt, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
						$excerpt = trim(preg_replace('~\x{00a0}~siu', ' ', $excerpt));
					 	$excerpt_search = Array("/\r/", "/[\n\t]+/", '/[ ]{2,}/', '/&(nbsp|#160);/i', '/&(quot|rdquo|ldquo|#8220|#8221|#147|#148);/i', '/&(apos|rsquo|lsquo|#8216|#8217);/i', '/&gt;/i', '/&lt;/i', '/&(amp|#38);/i', '/&(copy|#169);/i', '/&(trade|#8482|#153);/i', '/&(reg|#174);/i', '/&(mdash|#151|#8212);/i', '/&(ndash|minus|#8211|#8722);/i', '/&(bull|#149|#8226);/i', '/&(pound|#163);/i', '/&(euro|#8364);/i', '/&[^&;]+;/i', '/[ ]{2,}/');		    
					    $excerpt_replace = Array('', ' ', ' ', ' ', '"', "'", '>', '<', '&', '(c)', '(tm)', '(R)', '--', '-', '*', '£', 'EUR', '', ' ');
						$excerpt = preg_replace($excerpt_search, $excerpt_replace, $excerpt);
						$excerpt_append = ' [...]';
						$excerpt_length = 250-strlen($excerpt_append);
											
				    	if(strlen($excerpt) > $excerpt_length) {
				        	
							$excerpt = preg_replace('/\s+?(\S+)?$/', '', substr($excerpt, 0, $excerpt_length+1)) . $excerpt_append; 
				    	
				    	}
					 
						$excerpt = str_replace('+', '%20', urlencode($excerpt));
					
					} else {
					
						$excerpt = '';
						
					}

				}

			}
		
		}
		
		if(isset($title) && $title != '' && isset($sites) && $sites != '') {
		
			$social_sites = Array(
			
				'Bebo' => Array(
					'short_name' => 'bebo',
					'url' => 'http://www.bebo.com/c/share?Url=' . $permalink . '&amp;Title=' . $title
				),
				
				'BlinkList' => Array(
					'short_name' => 'blinklist',
					'url' => 'http://www.blinklist.com/index.php?Action=Blink/addblink.php&amp;Url=' . $permalink . '&amp;Title=' . $title
				),
		
				'Delicious' => Array(
					'short_name' => 'delicious',
					'url' => 'http://del.icio.us/post?url=' . $permalink . '&amp;title=' . $title . '&amp;notes=' . $excerpt,
					'description' => 'Del.icio.us'
				),
				
				'DesignFloat' => Array(
					'short_name' => 'designfloat',
					'url' => 'http://www.designfloat.com/submit.php?url=' . $permalink
				),
								
				'Digg' => Array(
					'short_name' => 'digg',
					'url' => 'http://digg.com/submit?phase=2&amp;url=' . $permalink . '&amp;title=' . $title . '&amp;bodytext=' . $excerpt
				),
				
				'Diigo' => Array(
					'short_name' => 'diigo',
					'url' => 'http://www.diigo.com/post?url=' . $permalink . '&amp;title=' . $title
				),
				
				'eKudos' => Array(
					'short_name' => 'ekudos',
					'url' => 'http://www.ekudos.nl/artikel/nieuw?url=' . $permalink . '&amp;title=' . $title . '&amp;desc=' . $excerpt
				),
	
				'Email' => Array(
					'short_name' => 'email',
					'url' => 'mailto:?subject=' . $title . '&amp;body=' . $permalink,
					'description' => 'E-mail'
				),
				
				'Evernote' => Array(
					'short_name' => 'evernote',
					'url' => 'http://www.evernote.com/clip.action?url=' . $permalink . '&amp;title=' . $title
				),

				'Facebook' => Array(
					'short_name' => 'facebook',					
					'url' => 'http://www.facebook.com/sharer.php?u=' . $permalink . '&amp;t=' . $title
				),
				
				'FriendFeed' => Array(
					'short_name' => 'friendfeed',
					'url' => 'http://www.friendfeed.com/share?title=' . $title . '&amp;link=' . $permalink
				),
								
				'Google' => Array (
					'short_name' => 'google',
					'url' => 'http://www.google.com/bookmarks/mark?op=edit&amp;bkmk=' . $permalink . '&amp;title=' . $title . '&amp;annotation=' . $excerpt,
					'description' => 'Google Bookmarks'
				),
				
				'Google+' => Array (
					'short_name' => 'googleplus',
					'url' => 'https://plusone.google.com/_/+1/confirm?hl=en&amp;url=' . $permalink . '&amp;title=' . $title,
					'height' => '650',
					'width' => '890'
				),
				
				'Hyves' => Array (
					'short_name' => 'hyves',
					'url' => 'http://www.hyves-share.nl/button/tip/?tipcategoryid=12&amp;rating=5&amp;title=' . $title . '&amp;body=' . $excerpt . ' [url=' . $permalink. ']' . $permalink . '[/url]',
					'height' => '520',
					'width' => '570'
				),
				
				'LinkedIn' => Array(
					'short_name' => 'linkedin',
					'url' => 'http://www.linkedin.com/shareArticle?mini=true&amp;url=' . $permalink. '&amp;title=' . $title . '&amp;source=' . $site_label . '&amp;summary=' . $excerpt,
					'height' => '520',
					'width' => '570'
				),
				
				'Messenger' => Array(
					'short_name' => 'messenger',
					'url' => 'http://profile.live.com/badge?url=' . $permalink. '&amp;title=' . $title . '&amp;description=' . $excerpt
				),
				
				'MisterWong' => Array(
					'short_name' => 'misterwong',
					'url' => 'http://www.mister-wong.com/addurl/?bm_url=' . $permalink . '&amp;bm_description=' . $title,
					'description' => 'Mister Wong'
				),
				
				'MySpace' => Array(
					'short_name' => 'myspace',
					'url' => 'http://www.myspace.com/Modules/PostTo/Pages/?u=' . $permalink . '&amp;t=' . $title
				),
					
				'Newsvine' => Array(
					'short_name' => 'newsvine',
					'url' => 'http://www.newsvine.com/_tools/seed&amp;save?u=' . $permalink . '&amp;h=' . $title
				),
				
				'NuJij' => Array(
					'short_name' => 'nujij',
					'url' => 'http://nujij.nl/jij.lynkx?t=' . $title . '&amp;u=' . $permalink . '&amp;b=' . $excerpt,
					'description' => 'NUjij'
				),
				
				'Orkut' => Array(
					'short_name' => 'orkut',
					'url' => 'http://promote.orkut.com/preview?nt=orkut.com&amp;tt=' . $title . '&amp;du=' . $permalink . '&amp;cn=' . $excerpt
				),
				
				'Pinterest' => Array(
					'short_name' => 'pinterest',
					'url' => 'http://pinterest.com/pin/create/button/?url=' . $permalink . '&amp;media=' . $pinterest_media . '&amp;description=' . $title
				),
				
				'Posterous' => Array(
					'short_name' => 'posterous',
					'url' => 'http://posterous.com/share?linkto=' . $permalink . '&amp;title=' . $title . '&amp;selection=' . $excerpt
				),
				
				'PrintFriendly' => Array(
					'short_name' => 'printfriendly',
					'url' => 'http://www.printfriendly.com/print/new?url=' . $permalink
				),
				
				'Reddit' => Array(
					'short_name' => 'reddit',
					'url' => 'http://reddit.com/submit?url=' . $permalink . '&amp;title=' . $title
				),
				
				'StumbleUpon' => Array(
					'short_name' => 'stumbleupon',
					'url' => 'http://www.stumbleupon.com/submit?url=' . $permalink . '&amp;title=' . $title
				),
								
				'Technorati' => Array(
					'short_name' => 'technorati',
					'url' => 'http://technorati.com/faves?add=' . $permalink
				),
				
				'Tumblr' => Array(
					'short_name' => 'tumblr',
					'url' => 'http://www.tumblr.com/share?v=3&amp;u=' . $permalink . '&amp;t=' . $title . '&amp;s=' . $excerpt
				),
			
				'Twitter' => Array(
					'short_name' => 'twitter',
					'url' => 'http://twitter.com/share?url=' . $permalink . '&amp;text=' . $title . ':' . $tweet_via,
					'height' => '450',
					'width' => '550'
				),
				
				'VKontakte' => Array( 
					'short_name' => 'vkontakte',
					'url' => 'http://vkontakte.ru/share.php?url=' . $permalink . '&amp;title=' . $title,
					'height' => '260',
					'width' => '570'
				),
				
				'Yahoo' => Array(
					'short_name' => 'yahoo',
					'url' => 'http://bookmarks.yahoo.com/toolbar/savebm?opener=tb&amp;u=' . $permalink . '&amp;t=' . $title . '&amp;d=' . $excerpt,
					'description' => 'Yahoo! Bookmarks'
				)
			
			);
			
			if($pinterest_media == '') { 
				
				unset($social_sites['Pinterest']); 
				
			}
		
	    	$sites_to_activate = array_unique(array_map('trim', explode('|', $sites)));		    
		    $active_sites = array_intersect($sites_to_activate, array_keys($social_sites)); 
		    $total_active_sites = count($active_sites);		    
		    $count_active_sites = 0;
		    			
			foreach($active_sites as $active_site) {
				
				$count_active_sites ++;
				
				if(isset($social_sites[$active_site]['favicon']) && $social_sites[$active_site]['favicon'] != '') {
				
					$favicon = $social_sites[$active_site]['favicon'];
			
				} else {
					
					$favicon = $social_sites[$active_site]['short_name'] . '.png';
					
				}
									
				if(isset($social_sites[$active_site]['description']) && $social_sites[$active_site]['description'] != '') {
				
					$description = $social_sites[$active_site]['description'];
			
				} else {
					
					$description = $active_site;
					
				}
				
				if($count_active_sites == 1) {
					
					$this->return_data = '
<!-- 
** ' . HJ_SB_NAME . ' v' . HJ_SB_VERSION . ' **
' . HJ_SB_DESCRIPTION . '
See: ' . HJ_SB_DOCS . ' for more information. 
-->
';
					$this->return_data .= '<ul ';	
					$this->return_data .= 'class="' . $class_name . '"';
					$this->return_data .= '>' . "\n";
					$this->return_data .= '<li class="' . $social_sites[$active_site]['short_name'] . ' first">';
			
				} elseif($count_active_sites == $total_active_sites) {
				
					$this->return_data .= '<li class="' . $social_sites[$active_site]['short_name'] . ' last">';
						
				} else {
					
					$this->return_data .= '<li class="' . $social_sites[$active_site]['short_name'] . '">';
					
				}
									
		    	$this->return_data .= '<a rel="nofollow" href="' . $social_sites[$active_site]['url'] . '" title="' . $description . '"';
		    	
		    	if($active_site != 'Email') { 
		    		
		    		$this->return_data .= ' target="_blank"';
		    		
		    	}
		    	
		    	if((isset($social_sites[$active_site]['height']) && $social_sites[$active_site]['height'] != '' && ctype_digit($social_sites[$active_site]['height'])) && (isset($social_sites[$active_site]['width']) && $social_sites[$active_site]['width'] != '' && ctype_digit($social_sites[$active_site]['width']))) {
		    	
		    		$this->return_data .= ' onclick="window.open(this.href,\'_blank\',\'height=' . $social_sites[$active_site]['height'] . ',width=' . $social_sites[$active_site]['width'] . '\');return false;"';
		    		
		    	}
		    	
		    	$this->return_data .= '>';
		    				    	
		    	if(isset($show_hyperlink_text) && $show_hyperlink_text == 'left') {
		    		
		    		$this->return_data .= $description . ' ';
		    		
		    	}
		    	
		    	if(isset($show_hyperlink_text) && $show_hyperlink_text == 'only') {
		    		
		    		$this->return_data .= $description;
		    		
		    	} else {

			    	$this->return_data .= '<img src="' . HJ_SB_IMGPATH . $favicon . '" alt="' . $description . '" />';
			    	
		    	}
			    	
		    	if(isset($show_hyperlink_text) && $show_hyperlink_text == 'right') {
		    		
		    		$this->return_data .= ' ' . $description;
		    		
		    	}
		    	
		    	$this->return_data .= '</a>';
		    	$this->return_data .= '</li>' . "\n";						
				
			}
			
			if($count_active_sites >= 1) {
			
				$this->return_data .= '</ul>';
			
			}				
		
		} 
				      
	}

	// --------------------------------------------------------------------

	public static function usage() {
	  
	  	ob_start(); ?>
	  	
	  	=============================
		The Tag
		=============================

        Add the following code between the opening {exp:channel:entries} and closing {/exp:channel:entries} tags.
        
        {exp:hj_social_bookmarks sites="Facebook|Google+|Twitter" entry_id="{entry_id}"}

		=============================
		Tag Parameters
		=============================

        sites=
        	
        	[REQUIRED]
        	
        	You can use the pipe character ('|') to separate multiple values.
        	
            - Bebo
            - BlinkList
            - Delicious
            - DesignFloat
            - Digg
            - Diigo
            - eKudos
            - Email
            - Evernote
            - Facebook
            - FriendFeed
            - Google
            - Google+
            - Hyves
            - LinkedIn
            - Messenger
            - MisterWong
            - MySpace
            - Newsvine
            - NuJij
            - Orkut
            - Pinterest
            - Posterous
            - PrintFriendly
            - Reddit
            - StumbleUpon
            - Technorati
            - Tumblr
            - Twitter
            - VKontakte
            - Yahoo

        entry_id=
        	
        	[REQUIRED]
        	
        	The ID number of the channel entry.
        	
        field_name=
			
			[OPTIONAL]
			
			When defining a field_name, HJ Social Bookmarks creates a short text summary of the selected field's content.
			
		show_hyperlink_text=
			
			[OPTIONAL]
			
			This parameter allows you to show hyperlink text left or right from the icon. You can also set it to 'only' and show only hyperlink text.
			
			Valid options are:
			
			- left
			- right
			- only
			
			By default, no hyperlink text will be showed.
			
		pinterest_media=
		
			[REQUIRED FOR PINTEREST BUTTON]
			
			The image URL to be pinned
			
		tweet_via=
		
			[OPTIONAL]
			
			This parameter allows you to attribute a screen name to a tweet. By default, no screen name will be attributed.
			
		class=
			
			[OPTIONAL]
			
			This lets you specify the value of the "class" attribute in the opening <ul> tag. The default value is "hj_social_bookmarks".
	  	
	  	<?php
  		$buffer = ob_get_contents();
  		ob_end_clean(); 

  		return $buffer;
  	
  	}
	
}

/* End of file pi.hj_social_bookmarks.php */ 
/* Location: ./system/expressionengine/third_party/hj_social_bookmarks/pi.hj_social_bookmarks.php */