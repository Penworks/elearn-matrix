<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
  'pi_name' => 'List Maker',
  'pi_version' =>'1.1',
  'pi_author' =>'Derek Jones',
  'pi_author_url' => 'http://www.expressionengine.com/',
  'pi_description' => 'Creates ordered and unordered HTML lists',
  'pi_usage' => List_maker::usage()
  );

/**
 * List_maker Class
 *
 * @package			ExpressionEngine
 * @category		Plugin
 * @author			ExpressionEngine Dev Team
 * @copyright		Copyright (c) 2006 - 2009, EllisLab, Inc.
 * @link			http://expressionengine.com/downloads/details/list_maker/
 */


class List_maker {

var $return_data = '';

	/**
	 * Constructor
	 *
	 * @access	public
	 * @return	void
	 */

	function List_maker($data = '', $type = '', $sep = '', $id = '', $class = '')
	{
		$this->EE =& get_instance();
		
		$data = ($data == '') ? trim($this->EE->TMPL->tagdata) : $data;
				
		if ($type == '')
		{
			$type = (strtolower($this->EE->TMPL->fetch_param('type')) == 'ol')? 'ol' : 'ul';			
		}
				
		if ($sep == '')
		{
			// if separator is "newline" or not specified, make sure it's a newline character
			$sep = ( ! $this->EE->TMPL->fetch_param('separator')) ? "\n" : $this->EE->TMPL->fetch_param('separator');
			if ($sep == 'newline')
				$sep = "\n";
		}
		
		$id = ($id == '') ? $this->EE->TMPL->fetch_param('id') : $id;
				
		if ($class == '')
		{
			$class = ( ! $this->EE->TMPL->fetch_param('class')) ? 'list' : $this->EE->TMPL->fetch_param('class');
		}
				
		// break up the data into an array
		$data = explode($sep, $data);
		
		// open the list with the proper tag
		if ($id)
		{
			$list = "<$type id=\"$id\" class=\"$class\">\n";
		}
		else
		{
			$list = "<$type class=\"$class\">\n";
		}
		
		// insert the elements into <li> tags
		foreach ($data as $item)
		{
			$list .= "<li>" . $item . "</li>\n";
		}
		
		// close the list tag up
		$list .= "</$type>\n";
		
		$this->return_data = $list;
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

	function usage()
	{

		ob_start(); 
		?>
		This plugin creates HTML lists from plain text data, and
		optionally accepts user-defined delimiters.
		
		PARAMETERS:
					
		type=""
		"ul" (unordered list) or "ol" (ordered list)
		* Default for type is an unordered list
		
		separator=""
		"newline" (hard returns) or any charaacter
		such as "|", "^", "+", etc.
		* Default for separator is a newline (hard return)
		
		id=""
		allows you to specify an id attribute
		* No default value
		
		class=""
		allows you to specify a class attribute
		* Default is "list"
		
		{exp:list_maker}
		This is one item
		This is another item
		And this is another
		{/exp:list_maker}
		
		would output the following HTML:
		
		<ul class="list">
		<li>This is one item</li>
		<li>This is another item</li>
		<li>And this is another</li>
		</ul>
		
		Version 1.1
		******************
		- Updated plugin to be 2.0 compatible		

		
		<?php
		$buffer = ob_get_contents();
		
		ob_end_clean();
		
		return $buffer;
	}
	
	// --------------------------------------------------------------------
	
}
// END CLASS

/* End of file pi.list_maker.php */
/* Location: ./system/expressionengine/third_party/list_maker/pi.list_maker.php */