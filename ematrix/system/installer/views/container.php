<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title><?php echo $title; ?></title>
<script type="text/javascript" src="<?=$javascript_path?>jquery/jquery.js"></script>

<style type="text/css">

	<?php $this->load->view('css'); ?>

</style>

<?php

if (isset($extra_header))
{
	echo $extra_header;
}

if (isset($refresh) && $refresh === TRUE)
{
	if ($this->input->get('ajax_progress') == 'yes')
	{
		$refresh_url .= '&ajax_progress=yes';
	}
	echo '<meta http-equiv="refresh" content="5;url='.$refresh_url.'" />';
}
?>

</head>
<body>

	<div id="outer">
	
		<div id="header">
		
			<a href="<?php echo SELF; ?>"><img src="<?php echo $image_path; ?>logo.gif" width="241" height="88" border="0" alt="ExpressionEngine Installation Wizard" /></a>
		
		</div>
	
		<div id="inner">		
		
			<h1><?php echo $heading; ?></h1>
			
			<div id="content">
			
				<?php echo $content; ?>
			
			</div>
			
			<div id="footer">
				
				ExpressionEngine <?php echo $version; ?> - &#169; <?php echo $copyright; ?>
				
			</div>

		</div>
				
	</div>

</body>
</html>
