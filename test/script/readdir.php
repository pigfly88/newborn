<?php
$d = dir("faces/woman");
echo "Handle: " . $d->handle . "\n";
echo "Path: " . $d->path . "\n";
$id=1;
while (false !== ($entry = $d->read())) {
	if($entry != "." && $entry != ".."){
		copy('faces/woman/'.$entry, "faces_rename/woman/{$id}.jpg");
		$id++;
	}
}
$d->close();
?> 