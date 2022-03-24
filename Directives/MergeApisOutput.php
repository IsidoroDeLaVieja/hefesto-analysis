<?php /*dlv-code-engine***/

$mergedApisOutput = [];
foreach($config['apisOutput'] as $apiOutput) {
    foreach ($apiOutput as $requestAnalysis) {
        
        $key = sha1(
            $requestAnalysis['type'].
            $requestAnalysis['verb'].
            $requestAnalysis['path'].
            $requestAnalysis['status']
        );
        
        if (isset($mergedApisOutput[$key])) {
            $mergedApisOutput[$key]['count'] += $requestAnalysis['count'];
        } else {
            $mergedApisOutput[$key] = $requestAnalysis;
        }
    }
}

$reportWithoutStatus = [];
foreach($mergedApisOutput as $key => $request) {
    $keyWithoutStatus = $request['type'].' '.$request['verb'].' '.$request['path'];
    if (!isset($reportWithoutStatus[$keyWithoutStatus])) {
        $reportWithoutStatus[$keyWithoutStatus] = 0;
    }
    $reportWithoutStatus[$keyWithoutStatus] += $request['count'];
}

foreach($mergedApisOutput as $key => $request) {
    $keyWithoutStatus = $request['type'].' '.$request['verb'].' '.$request['path'];
    $mergedApisOutput[$key]['percentage'] = round(100 * $request['count'] / $reportWithoutStatus[$keyWithoutStatus]);
}

$state->memory()->set('mergedApisOutput',array_values($mergedApisOutput));