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

$state->memory()->set('mergedApisOutput',array_values($mergedApisOutput));