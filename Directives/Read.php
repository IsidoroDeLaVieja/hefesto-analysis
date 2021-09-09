<?php /*dlv-code-engine***/

$date = date('Y-m-d');
$api = $state->message()->getPathParam('api');
$path =   $state->memory()->get('hefesto-pathstorage')."../$api/hefesto-$date.log";

if ( !file_exists($path) ) {
    $state->memory()->set('error.status',404);
    $state->memory()->set('error.message', "$date without log");
    throw new Exception("$date without log");
}

$tail = shell_exec("tail -n1 $path");

$state->message()->setHeader('Content-Type','application/json');
$state->message()->setBody($tail);