<div class="editAccordion open"> 
<h3><?=$name.' '.lang('settings')?></h3> 
    <table class="templateTable templateEditorTable" border="0" cellspacing="0" cellpadding="0" style="margin: 0;"> 
    <?php foreach($fields as $parts): ?> 
        <tr> 
            <td style="width: 50%"><?=$parts['label']?></td>
            <td><?=$parts['field']?></td> 
        </tr> 
    <?php endforeach;?> 
    <?php if (isset($new_app)) : ?>
        <tr> 
            <td style="width: 50%"></td>
            <td><span class="cp_button"><a href="<?php echo BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=social_update'; if($new_app=='authorize') echo AMP.'method=request_token'; ?>" onclick="window.location=this.href+'&provider='+$('#new_app_provider').val()+'&app_id='+$('#new_app_app_id').val()+'&app_secret='+$('#new_app_app_secret').val()+'&ts='+new Date().getTime(); return false;"><?=lang($new_app)?></a></span></td> 
        </tr> 
    <?php endif; ?>
    </table> 
    
</div> 