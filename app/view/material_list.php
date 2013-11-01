<?php
foreach (CMS_materials( $GLOBALS[ '_CMS_Rendered_Structure' ] ) as $material)
{
	CMS_render_material( $material );
}
?>