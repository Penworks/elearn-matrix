<?=form_open('C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=shorteen'.AMP.'method=save_settings');?>

<?=$providers?>

<p><?=form_submit('submit', lang('save'), 'class="submit"')?></p>

<?php
form_close();