<div class="__editor" action="<?php url_base('cmsapi','editor_save')?>">
	<input type="hidden" class="__id" name="__id" value="<?php v('id')?>">
	<input type="hidden" class="__field" name="__field" value="<?php v('field')?>">	
	<textarea style="display:none;" class="__html" name="__html"><?php vi('value')?></textarea>
	<input type="hidden" class="__entity" name="__entity" value="<?php vi('entity')?>">
	<div class="__value"><?php v('value')?></div>
	<div class="__clear"></div>
</div>