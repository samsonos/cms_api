<ul>
<?php foreach ( CMS_structure_children() as $structure ) { ?>
	<li><a href="<?php echo CMS_structure()->Url ?>/<?php echo $structure->Url ?>"><?php echo $structure->Name; ?></a></li>
<?php }?>
</ul>