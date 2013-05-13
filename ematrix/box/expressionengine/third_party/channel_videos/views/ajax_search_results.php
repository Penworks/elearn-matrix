<div class="VideoResultsTabs">

	<a href="#" class="ClearVideoSearch ClearVideoSearch_Top"><?=lang('video:clear_search')?></a>

	<ul>
	<?php foreach($videos as $vidSecName => $vidSection):?>
		<li><a href="#<?=$field_id?>_<?=$vidSecName?>"><?=lang('video:service:'.$vidSecName)?> (<?=count($vidSection)?>)</a></li>
	<?php endforeach;?>
	</ul>

	<?php foreach($videos as $vidSecName => $vidSection):?>

	<div id="<?=$field_id?>_<?=$vidSecName?>">
		<table cellspacing="0" cellpadding="0" class="CVTable"> <thead> <tr>
			<th width="30">&nbsp;</th>
			<th><span><?=lang('video:video')?></span></th>
			<th><span><?=lang('video:title')?></span></th>
			<th><span><?=lang('video:author')?></span></th>
			<th><span><?=lang('video:duration')?></span></th>
			<th><span><?=lang('video:views')?></span></th>
			<th><span><?=lang('video:date')?></span></th>
			</tr> </thead> <tbody>

			<?php if (count($vidSection) == 0): ?>
			<tr>
				<td colspan="7"><?=lang('video:no_search_results')?></td>
			</tr>
			<?php endif;?>


			<?php foreach($vidSection as $video):?>
				<tr class="ChannelVideosDrag" data='<?=base64_encode($this->channel_videos_helper->generate_json($video))?>'>
					<td><a href="#" class="AddVideo">&nbsp;</a> </td>
					<td><a href="<?=$video->vid_url?>" rel="shadowbox;width=405;height=340;player=swf"><img src="<?=$video->img_url?>"></a></td>
					<td><?=$video->title?></td>
					<td><?=$video->author?></td>
					<td><?=sprintf("%0.2f", $video->duration/60)?> min</td>
					<td><?=$video->views?></td>
					<td><?=$this->localize->decode_date('%d-%M-%y %H:%i', $video->date)?></td>
				</tr>
			<?php endforeach;?>


		</tbody></table>
	</div>

	<?php endforeach;?>

	<a href="#" class="ClearVideoSearch"><?=lang('video:clear_search')?></a>
</div>