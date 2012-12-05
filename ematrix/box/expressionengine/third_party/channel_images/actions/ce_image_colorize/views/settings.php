<?php $thstyle='style="border-right-color:rgba(0, 0, 0, 0.1); border-right-style:solid; border-right-width:1px;"';?>
<table cellspacing="0" cellpadding="0" border="0" class="ChannelImagesTable CITable">
	<thead>
		<tr>
			<th><?=lang('ce:red')?></th>
			<th <?=$thstyle?>><?=lang('ce:green')?></th>
			<th <?=$thstyle?>><?=lang('ce:blue')?></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td><?=form_input($action_field_name.'[red]', $red, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[green]', $green, 'style="border:1px solid #ccc; width:80%;"')?></td>
			<td><?=form_input($action_field_name.'[blue]', $blue, 'style="border:1px solid #ccc; width:80%;"')?></td>
		</tr>
	</tbody>
</table>

<small><?=lang('ce:colorize_exp')?></small>
<small>
<strong><?=lang('ce:red')?>:</strong> <?=lang('ce:red:exp')?> <br />
<strong><?=lang('ce:green')?>:</strong> <?=lang('ce:green:exp')?> <br />
<strong><?=lang('ce:blue')?>:</strong> <?=lang('ce:blue:exp')?> <br />
</small>