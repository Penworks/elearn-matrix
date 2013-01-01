<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=social_update'.AMP.'method=save_settings');?>


<p><?=lang('save_reminder')?></p>


<?php 
$this->table->set_template($cp_pad_table_template);

foreach ($settings as $key => $val)
{
	$this->table->add_row(lang($key, $key), $val);
}

echo $this->table->generate();

$this->table->clear();
?>

<div id="shortening_test_table" style="display: none;">
<?php 
$this->table->set_template($cp_pad_table_template);

$tbl = $this->table->make_columns($shortening_test_table, 3);

echo $this->table->generate($tbl);

$this->table->clear();
?>
</div>

<?=$providers?>

<p><?=form_submit('submit', lang('save'), 'class="submit"')?></p>

<?php
form_close();