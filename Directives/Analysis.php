<?php /*dlv-code-engine***/

$date = $state->message()->getQueryParam('date');
if (!$date) {
    $date = date('Y-m-d');
}

$api = $state->message()->getPathParam('api');
$path =   $state->memory()->get('hefesto-pathstorage')."../$api/hefesto-$date.log";

$handle = false;
try {
    $handle = fopen($path, 'r');
} catch (\Throwable $e) { }

if (!$handle) {
    $state->memory()->set('error.status',404);
    $state->memory()->set('error.message', "$date without log");
    throw new \Exception("$date without log");
}

$correlationId = $state->message()->getQueryParam('correlationId');
$status = $state->message()->getQueryParam('status');

$report = [];
while (($line = fgets($handle)) !== false) {
    $trace = json_decode($line,true);
    $init = $trace[0];
    $finish = $trace[count($trace) - 1];

    if ($correlationId && 
        (!isset($finish['correlationId']) || $correlationId !== $finish['correlationId'])) {
            continue;
    }
    if ($status && 
        (!isset($finish['status']) || $status != $finish['status'])) {
            continue;
    }
    
    $key = $init['type'].' '.$init['verb'].' '.$init['path'].' ---> '.$finish['status'];
    if (!isset($report[$key])) {
        $report[$key] = [
            'type' => $init['type'],
            'verb' => $init['verb'],
            'path' => $init['path'],
            'status' => $finish['status'],
            'count' => 0
        ];
    }
    $report[$key]['count']++;
}

fclose($handle);

$report = array_values($report);
$state->memory()->set('report',$report);