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
$headerKey = $state->message()->getQueryParam('headerKey');
$headerValue = $state->message()->getQueryParam('headerValue');
$path = $state->message()->getQueryParam('path');
$ip = $state->message()->getQueryParam('ip');
$returnIps = $state->message()->getQueryParam('returnIps');

$report = [];
$reportWithoutStatus = [];
while (($line = fgets($handle)) !== false) {
    $trace = json_decode($line,true);
    $init = $trace[0];
    $finish = $trace[count($trace) - 1];
    $medium = count($trace) === 3 ? $trace[count($trace) - 2] : null;

    if ($correlationId && 
        (!isset($finish['correlationId']) || $correlationId !== $finish['correlationId'])) {
            continue;
    }
    if ($status && 
        (!isset($finish['status']) || $status != $finish['status'])) {
            continue;
    }
    if ($headerKey && $headerValue &&
        (!isset($init['headers'][$headerKey]) || strpos($init['headers'][$headerKey], $headerValue) === false)) {
        continue;
    }
    if ($ip && 
        (!isset($medium['ip']) || $medium['ip'] !== $ip) ) {
        continue;
    }
    if ($path && 
        (!isset($init['path']) || $path !== $init['path'])) {
        continue;
    }

    if ($returnIps) {
        if (isset($medium['ip'])) {
            $report[$medium['ip']] = $medium['ip'];
        }
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

    $keyWithoutStatus = $init['type'].' '.$init['verb'].' '.$init['path'];
    if (!isset($reportWithoutStatus[$keyWithoutStatus])) {
        $reportWithoutStatus[$keyWithoutStatus] = 0;
    }
    $reportWithoutStatus[$keyWithoutStatus]++;
}

fclose($handle);

if ($returnIps) {
    $report = array_values($report);
    $state->memory()->set('report',$report);
    return;
}

foreach($report as $key => $request) {
    $keyWithoutStatus = $request['type'].' '.$request['verb'].' '.$request['path'];
    $report[$key]['percentage'] = round(100 * $request['count'] / $reportWithoutStatus[$keyWithoutStatus]);
}

$report = array_values($report);
$state->memory()->set('report',$report);