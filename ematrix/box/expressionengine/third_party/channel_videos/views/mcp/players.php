<?php echo $this->view('mcp/_header'); ?>


<div id="fbody">

     <div class="btitle">
        <h2><?=lang('cv:players')?></h2>
    </div>

<?=form_open($base_url_short.AMP.'method=update_players')?>

    <div style="padding:10px 20px">
    	<div class="DDTable">
			<h2><?=lang('cv:player:vimeo')?>&nbsp;&nbsp;&nbsp;<a href="https://developer.vimeo.com/player/embedding" style="font-size:12px" target="_blank">Documentation</a></h2>
			<table class="hasrowspan">
				<thead>
					<tr>
						<th><?=lang('cv:setting')?></th>
						<th colspan="2"><?=lang('cv:value')?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="odds">
						<th><?=lang('cv:vi:width')?> <small>(<?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[vimeo][width]', ((isset($vimeo['width']) == TRUE) ? $vimeo['width'] : 500))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:vi:height')?> <small>(<?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[vimeo][height]', ((isset($vimeo['height']) == TRUE) ? $vimeo['height'] : 281))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:vi:title')?> <small>(<?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[vimeo][title]', ((isset($vimeo['title']) == TRUE) ? $vimeo['title'] : 1))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:vi:byline')?> <small>(<?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[vimeo][byline]', ((isset($vimeo['byline']) == TRUE) ? $vimeo['byline'] : 1))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:vi:portrait')?> <small>(<?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[vimeo][portrait]', ((isset($vimeo['portrait']) == TRUE) ? $vimeo['portrait'] : 1))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:vi:color')?> <small>(<?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[vimeo][color]', ((isset($vimeo['color']) == TRUE) ? $vimeo['color'] : '00adef'))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:vi:autoplay')?> <small>(<?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[vimeo][autoplay]', ((isset($vimeo['autoplay']) == TRUE) ? $vimeo['autoplay'] : 0))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:vi:loop')?> <small>(<?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[vimeo][loop]', ((isset($vimeo['loop']) == TRUE) ? $vimeo['loop'] : 0))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:vi:api')?> <small>(<?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[vimeo][api]', ((isset($vimeo['api']) == TRUE) ? $vimeo['api'] : 0))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:vi:player_id')?> <small>(<?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[vimeo][player_id]', ((isset($vimeo['player_id']) == TRUE) ? $vimeo['player_id'] : ''))?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<div class="DDTable">
			<h2><?=lang('cv:player:youtube')?>&nbsp;&nbsp;&nbsp;<a href="https://developers.google.com/youtube/player_parameters" style="font-size:12px" target="_blank">Documentation</a></h2>
			<table class="hasrowspan">
				<thead>
					<tr>
						<th><?=lang('cv:setting')?></th>
						<th colspan="2"><?=lang('cv:value')?></th>
					</tr>
				</thead>
				<tbody>
					<tr class="odds">
						<th><?=lang('cv:yt:width')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][width]', ((isset($youtube['width']) == TRUE) ? $youtube['width'] : 560))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:height')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][height]', ((isset($youtube['height']) == TRUE) ? $youtube['height'] : 315))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:autohide')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][autohide]', ((isset($youtube['autohide']) == TRUE) ? $youtube['autohide'] : 1))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:autoplay')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][autoplay]', ((isset($youtube['autoplay']) == TRUE) ? $youtube['autoplay'] : 0))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:cc_load_policy')?> <small>(<?=lang('cv:flash')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][cc_load_policy]', ((isset($youtube['cc_load_policy']) == TRUE) ? $youtube['cc_load_policy'] : 0))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:color')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][color]', ((isset($youtube['color']) == TRUE) ? $youtube['color'] : 'red'))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:controls')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][controls]', ((isset($youtube['controls']) == TRUE) ? $youtube['controls'] : 1))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:disablekb')?> <small>(<?=lang('cv:flash')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][disablekb]', ((isset($youtube['disablekb']) == TRUE) ? $youtube['disablekb'] : 0))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:enablejsapi')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][enablejsapi]', ((isset($youtube['enablejsapi']) == TRUE) ? $youtube['enablejsapi'] : 0))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:end')?> <small>(<?=lang('cv:flash')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][end]', ((isset($youtube['end']) == TRUE) ? $youtube['end'] : ''))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:fs')?> <small>(<?=lang('cv:flash')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][fs]', ((isset($youtube['fs']) == TRUE) ? $youtube['fs'] : 1))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:iv_load_policy')?> <small>(<?=lang('cv:flash')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][iv_load_policy]', ((isset($youtube['iv_load_policy']) == TRUE) ? $youtube['iv_load_policy'] : 1))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:list')?> <small>(<?=lang('cv:flash')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][list]', ((isset($youtube['list']) == TRUE) ? $youtube['list'] : ''))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:listType')?> <small>(<?=lang('cv:flash')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][listType]', ((isset($youtube['listType']) == TRUE) ? $youtube['listType'] : ''))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:loop')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][loop]', ((isset($youtube['loop']) == TRUE) ? $youtube['loop'] : 0))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:modestbranding')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][modestbranding]', ((isset($youtube['modestbranding']) == TRUE) ? $youtube['modestbranding'] : 0))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:origin')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][origin]', ((isset($youtube['origin']) == TRUE) ? $youtube['origin'] : ''))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:playerapiid')?> <small>(<?=lang('cv:flash')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][playerapiid]', ((isset($youtube['playerapiid']) == TRUE) ? $youtube['playerapiid'] : ''))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:playlist')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][playlist]', ((isset($youtube['playlist']) == TRUE) ? $youtube['playlist'] : ''))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:rel')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][rel]', ((isset($youtube['rel']) == TRUE) ? $youtube['rel'] : 1))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:showinfo')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][showinfo]', ((isset($youtube['showinfo']) == TRUE) ? $youtube['showinfo'] : 1))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:start')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][start]', ((isset($youtube['start']) == TRUE) ? $youtube['start'] : 0))?></td>
					</tr>
					<tr class="odds">
						<th><?=lang('cv:yt:theme')?> <small>(<?=lang('cv:flash')?> &amp; <?=lang('cv:html5')?>)</small></th>
						<td colspan="2"><?=form_input('players[youtube][theme]', ((isset($youtube['theme']) == TRUE) ? $youtube['theme'] : 'dark'))?></td>
					</tr>
				</tbody>
			</table>
		</div>

		<input class="submit" type="submit" value="Save">
    </div>



<?php form_close();?>

</div><!--fbody-->






<?php $this->view('mcp/_footer'); ?>