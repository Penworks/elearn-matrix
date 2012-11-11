<div class="ChannelImagesField cfix">

<ul class="ChannelImagesTabs">
	<li><a href="#CIActions"><?=lang('ci:upload_actions')?></a></li>
	<li><a href="#CILocSettings"><?=lang('ci:loc_settings')?></a></li>
	<li><a href="#CIFieldUI"><?=lang('ci:fieldtype_settings')?></a></li>
</ul>

<div class="ChannelImagesTabsHolder">
	<div class="CIActions cfix" id="CIActions"><?=$this->load->view('fts/actions', array(), TRUE);?></div>
	<div class="CILocSettings" id="CILocSettings"><?=$this->load->view('fts/locations', array(), TRUE);?></div>
	<div class="CIFieldUI" id="CIFieldUI"><?=$this->load->view('fts/field_settings', array(), TRUE);?></div>
</div>

<script type="text/javascript">
var ChannelImages = ChannelImages ? ChannelImages : new Object();
ChannelImages.FTS = <?=$action_groups?>;
ChannelImages.previews = {small:'<?=$small_preview?>', big:'<?=$big_preview?>'};
</script>
</div>