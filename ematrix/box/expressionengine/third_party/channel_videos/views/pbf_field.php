<div class="CVField" id="ChannelVideos<?=$field_id?>" rel="<?=$field_id?>" data-field_id="<?=$field_id?>">

<table cellspacing="0" cellpadding="0" border="0" class="CVTable">
<thead>
	<tr>
		<th colspan="99" class="top_actions">
			<div class="block SearchVideos"><?=lang('cv:search')?></div>
			<div class="block SubmitVideoUrl"><?=lang('cv:submit_url')?></div>
		</th>
	</tr>
	<tr class="hidden SVWrapperTR">
		<th colspan="99" class="SVWrapper">
			<div class="cvsearch">
				<?=lang('cv:keywords')?> <input rel="keywords" type="text" style="width:150px"/>&nbsp;&nbsp;
				<?=lang('cv:author')?> <input rel="author" value="" type="text" style="width:90px"/>&nbsp;&nbsp;
				<?=lang('cv:limit')?> <input rel="limit" value="10" type="text" style="width:35px"/>&nbsp;&nbsp;&nbsp;
				<input type="submit" class="Button searchbutton" value="<?=lang('cv:find_videos')?>" />
			</div>

			<div class="VideosResults">
				<div class="results results-youtube hidden">
					<h6>Youtube <a href="#" class="ClearVideoSearch"><?=lang('cv:clear_search')?></a></h6>
					<p class="LoadingVideos"><?=lang('cv:searching_videos')?></p>
					<div class="inner"></div>
				</div>
				<div class="results results-vimeo hidden">
					<h6>Vimeo <a href="#" class="ClearVideoSearch"><?=lang('cv:clear_search')?></a></h6>
					<p class="LoadingVideos"><?=lang('cv:searching_videos')?></p>
					<div class="inner"></div>
				</div>
			</div>
		</th>
	</tr>

	<?php if ($layout == 'table'):?>
	<tr>
		<?php foreach ($settings['columns'] as $type => $val):?>
		<?php if ($val == FALSE) continue;?>
		<?php $size=''; if ($type == 'image') $size = '50';?>
		<th style="width:<?=$size?>px"><?=$val?></th>
		<?php endforeach;?>

		<th style="width:60px"><?=lang('cv:actions')?></th>
	</tr>
	<?php endif;?>
</thead>
<?php if ($layout == 'table'):?>
	<tbody class="AssignedVideos">
	<?php foreach ($videos as $order => $vid):?>
	<?php $this->load->view('pbf_single_video', array('vid' => $vid, 'order' => $order)); ?>
	<?php endforeach;?>
	<?php if ($total_videos < 1):?><tr class="NoVideos"><td colspan="99"><?=lang('cv:no_videos')?></td></tr><?php endif;?>
	</tbody>
<?php else:?>
	<tbody><tr><td class="AssignedVideos TileBased">
	<?php foreach ($videos as $order => $vid):?>
	<?php $this->load->view('pbf_single_video', array('vid' => $vid, 'order' => $order, 'layout' => $layout)); ?>
	<?php endforeach;?>
	<?php if ($total_videos < 1):?><p class="NoVideos"><?=lang('cv:no_videos')?></p><?php endif;?>
	</td></tr></tbody>
<?php endif;?>
<tfoot>
	<tr>
		<td <?php if ($settings['video_limit'] == '999999') echo 'style="display:none"';?> colspan="99" class="VideoLimit"><?=lang('cv:remain')?> <span class="remain"><?=$settings['video_limit']?></span></td>
	</tr>
</tfoot>
</table>
<script type="text/javascript">
setTimeout(function(){
jQuery(document).ready(function() {
	ChannelVideos.Data.FIELD<?=$field_id?> = <?=$json?>;

	ChannelVideos.LANG = {
		clear_search: "<?=lang('cv:clear_search')?>"
	};

	<?php if (isset($vimeo_data)):?>
	ChannelVideos.vimeo_data = <?=$this->channel_videos_helper->generate_json($vimeo_data);?>;
	<?php endif;?>
});
}, 1500);
</script>
</div>
