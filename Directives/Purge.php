<?php /*dlv-code-engine***/

$path = $state->memory()->get('hefesto-pathstorage').'../*/';
exec('find ' . $path . ' -name "hefesto-*.log" -mtime +2 -delete');
