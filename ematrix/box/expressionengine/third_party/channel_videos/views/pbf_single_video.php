<?php if ($layout == 'table'):?>

	<tr class="CVItem">
		<td> <a href="<?=$vid->video_url?>" class="PlayVideo"><img src="<?=$vid->video_img_url?>" width="100px" height="75px"></a></td>
		<td><?=$vid->video_title?></td>
		<td><?=$vid->video_author?></td>
		<td><?=sprintf("%0.2f", $vid->video_duration/60)?> min</td>
		<td><?=$vid->video_views?></td>
		<td><?=$this->localize->decode_date('%d-%M-%y %H:%i', $vid->video_date)?></td>
		<td>
			<a href="javascript:void(0)" class="MoveVideo">&nbsp;</a>
			<a href="javascript:void(0)" class="DelVideo" data-id="<?=$vid->video_id?>">&nbsp;</a>
			<?php if ($vid->video_id != FALSE):?>
			<input name="<?=$field_name?>[videos][<?=$order?>][video_id]" type="hidden" value="<?=$vid->video_id?>" >
			<?php else:?>
			<textarea name="<?=$field_name?>[videos][<?=$order?>][data]" style="display:none"><?=$this->channel_videos_helper->generate_json($vid)?></textarea>
			<?php endif;?>
		</td>
	</tr>

<?php else:?>

	<div class="CVItem VideoTile">
		<a href="<?=$vid->video_url?>" class="PlayVideo"><img src="<?=$vid->video_img_url?>" width="100px" height="75px"></a>
		<small><?=$vid->video_title?></small>
		<span>
			<a href="javascript:void(0)" class="MoveVideo">&nbsp;</a>
			<a href="javascript:void(0)" class="DelVideo" data-id="<?=$vid->video_id?>">&nbsp;</a>
			<?php if ($vid->video_id != FALSE):?>
			<input name="<?=$field_name?>[videos][<?=$order?>][video_id]" type="hidden" value="<?=$vid->video_id?>" >
			<?php else:?>
			<textarea name="<?=$field_name?>[videos][<?=$order?>][data]" style="display:none"><?=$this->channel_videos_helper->generate_json($vid)?></textarea>
			<?php endif;?>
		</span>
	</div>

<?php endif;?>
