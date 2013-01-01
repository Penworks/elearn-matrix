<div class="editAccordion open"> 
<h3><?=$name.' '.lang('settings')?></h3> 
    <table class="templateTable templateEditorTable" border="0" cellspacing="0" cellpadding="0" style="margin: 0;"> 
    <?php foreach($fields as $parts): ?> 
        <tr> 
            <td style="width: 50%"><?=$parts['label']?></td>
            <td><?=$parts['field']?></td> 
        </tr> 
    <?php endforeach;?> 
        <tr> 
            <td style="width: 50%"></td>
            <td><span class="cp_button"><a href="<?=BASE.AMP.'C=addons_modules'.AMP.'M=show_module_cp'.AMP.'module=social_update'.AMP.'method=request_token'?>" onclick="window.location=this.href+'&provider=<?=$provider?>&app_id='+$('#<?=$provider?>_app_id').val()+'&app_secret='+$('#<?=$provider?>_app_secret').val()+'&ts='+new Date().getTime(); return false;"><?=lang('authorize')?></a></span></td> 
        </tr> 
    </table> 
    
</div> 