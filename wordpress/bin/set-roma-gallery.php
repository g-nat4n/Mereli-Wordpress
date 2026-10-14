<?php
$p = wc_get_product(26);
if (! $p) {
	exit(1);
}
set_post_thumbnail(26, 42);
$p = wc_get_product(26);
// Galeria sem repetir a destacada.
$p->set_gallery_image_ids([43]);
$p->save();
echo 'feat=' . $p->get_image_id() . ' gal=' . implode(',', $p->get_gallery_image_ids()) . PHP_EOL;
