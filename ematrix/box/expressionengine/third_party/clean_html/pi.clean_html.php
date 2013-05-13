<?php	if(	!defined('BASEPATH')) exit('No direct script access allowed');

/***
 * codeTrio - Clean HTML
 * This plugin cleans dirty html code. Also you can truncate string to a specific length and returned string will have valid markup in it.
 *
 * @package			Clean HTML
 * @author			codeTrio DevTeam
 * @copyright		Copyright (c) 2012, codeTrio
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @link			http://www.codetrio.com/
 * @version			1.0
 * @filesource 	./system/expressionengine/third_party/clean_html/pi.clean_html.php 
 */

$plugin_info = array(
						'pi_name'			=> 'Clean HTML',
						'pi_version'		=> '1.0',
						'pi_author'			=> 'codeTrio DevTeam',
						'pi_author_url'		=> 'http://www.codetrio.com/',
						'pi_description'	=> 'Cleans dirty html code. Also you can truncate string to a specific length and returned string will have valid markup in it.',
						'pi_usage'			=> Clean_html::usage()
					);

 
 class Clean_html {
 
 	var $return_data;	
	var $singleTags = array("br", "hr", "meta", "link", "area", "base", "basefont", "input", "param", "img");
	protected $_str;
	
 	/**
 	 * Constructor Method
 	 *
 	 */
 	public function Clean_html($str = '')
 	{
 		$this->EE =& get_instance();

 		$strip_tags = "font|center|b|form|textarea|button|font|input|checkbox|select";
 		$strip_attributes = "align|font";

 		$engine = $this->EE->TMPL->fetch_param('engine', 'purifier');
 		$excerpt_size = $this->EE->TMPL->fetch_param('excerpt_size');
 		$strip_all_tags = $this->EE->TMPL->fetch_param('strip_tags', 'no');
 		$strip_tags_except = $this->EE->TMPL->fetch_param('strip_tags_except');
 		$debug = ($this->EE->TMPL->fetch_param('debug')=="yes")?true:false;
 		 		 		
 		$this->_str = ($str == '') ? $this->EE->TMPL->tagdata : $str;

		$this->EE->benchmark->mark('code_start');
		
		if($strip_all_tags!='no'){
			$this->_str = strip_tags($this->_str, $strip_tags_except);
		}
		
		# Truncate string
		if($excerpt_size!='' && $excerpt_size>0){
			 $this->_str = $this->EE->functions->word_limiter($this->_str, $excerpt_size);
		}

		if($strip_tags){
			$this->_stripTags($strip_tags);
		}
		
		if($strip_attributes){
			$this->_stripAttributes($strip_attributes);		
		}
		
		if($engine=="tidy"){
			
			// HTML Tidy
			// Details: http://www.w3.org/People/Raggett/tidy/

			//Tidy configuration
			$options = array (
								'clean' => true,
								'output-xhtml' => true,
								'output-xml' => false,
								'output-html' => false,
								'show-body-only' => true,
								'hide-comments' => true,
								'join-classes' => true,
								'join-styles' => true,
								'logical-emphasis' => true,
								'word-2000' => true,
								'wrap' => 0,
								'quote-marks'=>true,
								'quote-ampersand'=>true,
								'drop-font-tags'=>true,
								'drop-empty-paras'=>true,
								'drop-proprietary-attributes' => true,
								'enclose-text' => true,
								'fix-backslash' => false,
								'force-output' => true,
								'indent' => false,
								'indent-spaces' => 4,
						);		
			if(class_exists('tidy')){

				$tidy = tidy_parse_string($this->_str, $options, "UTF8");
				$tidy->cleanRepair();
				$this->_str = $tidy;
			}elseif (function_exists ('tidy_parse_string')) {

				# Do the cleaning of $str
				$this->_str = tidy_parse_string($this->_str, $options, "UTF8");
				tidy_clean_repair($this->_str);
				$this->_str = tidy_get_output($this->_str);
			
			}else{
				//Tidy is not supported
				return $this->EE->output->show_user_error('general', array("Tidy is not supported on this platform. Try purify or none"));
			}
		}elseif($engine=="purify"){
			
			# HTML Purifier Library
			# Details - http://htmlpurifier.org/

			require_once PATH_THIRD . 'clean_html/libraries/htmlpurifier/HTMLPurifier.auto.php';
	
			$this->htmlpurify = new HTMLPurifier();
			$config = HTMLPurifier_Config::createDefault();
			$this->_str = $this->htmlpurify->purify( $this->_str, $config );		
		
		}else{
		
			$this->_custom_tidy_string();
			// Drop empty paragraphs
			$empt_p_regex = '/<p[^>]*>[&nbsp;\s]*<\/p>/i';
			$this->_str = preg_replace($empt_p_regex,"\r\n", $this -> _str);

		}
        
        /*        
		if($compress){
			//Remove spaces after tag
			$this->_str = preg_replace("/>[\s]+/", ">", $this->_str);

			//Remove spaces before end tag
			$this->_str = preg_replace("/[\s]+<\//", "/>", $this->_str);

			//Remove space before tags
			$this->_str = preg_replace("/[\s]+</", "<", $this->_str);
			
			//Remove extra space before words
			$this->_str = preg_replace("/([^<>])[\s]+([^<>])/", "$1 $2", $this->_str);
			
		}
		*/
		
 		$this->EE->benchmark->mark('code_end');

		if($debug){
		 	$stats = "<br /><pre><span style=\"background-color:black; color:white; padding:2px\">".$this->EE->benchmark->elapsed_time('code_start', 'code_end')."<br />". $this->EE->benchmark->memory_usage()." </span></pre>";
		 	$this->_str = $this->_str.$stats;
		 }	

 		$this->return_data = $this->_str;
 	}
 	
 	/*
 	*	Custom method to clean html
 	*/
 	protected function _custom_tidy_string(){
 		
 		$inline_tags = array('a','span','b','i','u','strong','em','big','small',
 	        'tt','var','code','xmp','cite','pre','abbr','acronym','address','q','samp',
 	        'sub','sup'); 
 	        
	 	$this->_str = stripslashes($this->_str);

		# 
		#	Clean empty inline tags
		#
		$repl = 0;
		foreach($inline_tags as $tag){      
              
              $this->_str = preg_replace("/<($tag)[^>]*>[\s]*([(&nbsp;)]*)[\s]*<\/($tag)>/i","\\2", $this->_str, -1, $count);
              $repl += $count;

              $this->_str = preg_replace("/<\/($tag)[^>]*>[\s]*([(&nbsp;)]*)[\s]*<($tag)>/i","\\2", $this -> _str,-1,$count);
              $repl += $count;
        } 

	 	//close dirty tags, ie the opened tag that isnt closed or the closed tag that isnt open
	 	$this->_closeRemoveDirtyTags();
 	}

	/*
	* Close tags which are opened but not closed
	*/
	protected function _closeRemoveDirtyTags(){
	 	
		$opened = array(); // loop through opened and closed tags in order 
		if(preg_match_all("/<(\/?[a-z0-9]+)[^>]*\/?>/i", $this->_str, $matches, PREG_OFFSET_CAPTURE)) {

			#Worked well so far
			//print_r($matches);			
			
			foreach($matches[1] as $key=>$tag) {
				
				//If single tag, no need to go below
				$singletag = quotemeta($tag[0]);
				$signle_tag_regex = "/<$singletag(.*?)\/>/i";
				
				if( substr($singletag, 0, 1)!="/" && preg_match($signle_tag_regex, $matches[0][$key][0])){
					//print_r($matches[0][$key][0]);
					continue;
				}
					
			 
				if(preg_match("/^[a-z0-9]+$/i", $tag[0], $regs)) { 

					// a tag has been opened
					if(in_array(strtolower($regs[0]), $this->singleTags)==FALSE){	
						$opened[] = $regs[0];
					}	 
						
				} elseif(preg_match("/^\/([a-z0-9]+)$/i", $tag[0], $regs)) {
										
					#If closed marker doesnt have an opening marker, remove it - not perfect but ok
					if($regs[1]!=$opened[array_pop(array_keys($opened))]){

						//Lets replace by demo text as we need keep the string same length to trace the tag
						$demo_txt = str_pad("", strlen("<$tag[0]>"), "x");
						$this->_str = substr_replace($this->_str, $demo_txt, $tag[1]-1, strlen("<$tag[0]>"));

					}else{
						// a tag has been closed 
						unset($opened[array_pop(array_keys($opened))]); 
					}	
				}
				
			}
		} 

		// close tags that are still open 
		if($opened) { 
			$tagstoclose = array_reverse($opened); 
				foreach($tagstoclose as $tag) 
					$this->_str .= "</$tag>"; 
		}
		
		//Lets replace the dirty demo txt
		$this->_str = preg_replace("/([x]+){3}/i", "", $this->_str);		
	}

	/**
	*	Remove blacklisted tags
	*/
	protected function _stripTags($tags_str){
		
	  $tags_str = strtolower($tags_str);
	  $tags = explode("|", $tags_str);
	  $tag_regex = array();
	  $replace_regex = array();
	  //print_r($this->_str);
	  foreach($tags as $tag){
	    
	    if(in_array(strtolower($tag), $this->singleTags)==FALSE){

			//$tag_regex[] = "/<$tag(.*)>(.*)<\/$tag>/";
			$tag_regex[] = "/<$tag\b[^>]*>(.*?)<\/$tag>/is";
			$replace_regex[] = "$1";
	    }else{
			$tag_regex[] = "/<$tag"."[^>]*>/i";
			$replace_regex[] = "";
	    }
	    
	  }
	  
	  /*
		Some tag doesn't work, need to find out more
			print_r($tag_regex);
			preg_match_all($tag_regex, $this->_str, $matches);
			print_r($matches);
		*/	

	  
	  $this->_str = preg_replace($tag_regex, $replace_regex, $this->_str);
	}
	
	/**
	*	Remove few attributes
	*/
	protected function _stripAttributes($attributes_str){

		$attributes_str = strtolower($attributes_str);
		$attributes = explode("|", $attributes_str);
		foreach($attributes as $attribute){
			  $this->_str = preg_replace('/[\s]+('.$attribute.')=[\s]*("[^"]*"|\'[^\']*\')/i',"", $this->_str);
		      $this->_str = preg_replace('/[\s]+('.$attribute.')=[\s]*[^ |^>]*/i',"", $this->_str);
		}     
	}
 	
 
 	// --------------------------------------------------------------------
 	
 	/**
 	 * Usage
 	 *
 	 * Plugin Usage
 	 *
 	 * @access	public
 	 * @return	string
 	 */
 	public function usage()
 	{
 		ob_start(); 
 		?>
 			This plugin cleans dirty html code. Also you can truncate string to a specific length and returned string will have valid markup in it.
 			
 			This come with three parser. HTML Purifier, Tidy and custom
 			HTML Purifier - http://htmlpurifier.org/
 			HTML Tidy - http://www.w3.org/People/Raggett/tidy/, you server must have this support
 			custom - native dirty html cleaning method
 			
 			From all of this, HTML Purifier is little slower than other but it works great, also need the library files with the plugin. HTML Tidy is little faster than HTML Purifier but your server must have the php tidy package. Custom is faster than all other but i need to improve this more.

			Parameters:
				engine	- tidy/purifier/custom. Which library want to use. 
						  tidy - http://www.w3.org/People/Raggett/tidy/
						  purifier - http://htmlpurifier.org/
						  custom - native
						  Default value is purifier. Its a optional field.
				excerpt_size - Optional. To truncate suppplied string to specific number of words.
				strip_tags - yes/no. Default is no. Strip HTML tags from a string
				strip_tags_except - Tags that should not be removed from supplied string. For example:
										strip_tags_except="<p><a>"
							 							 
			To use this plugin, wrap anything you want to be processed by it between these tag pairs:

			{exp:clean_html excerpt_size="120" engine="tidy"}
				text and html code you want to clean
			{/exp:clean_html}
 			 
 		<?php
 		$buffer = ob_get_contents();
 	
 		ob_end_clean(); 
 
 		return $buffer;
 	}
 
 	// --------------------------------------------------------------------
 	
 }
 /* End of file pi.clean_html.php */ 
 /* Location: ./system/expressionengine/third_party/clean_html/pi.clean_html.php */ 