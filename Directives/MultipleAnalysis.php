<?php /*dlv-code-engine***/

$request = $state->memory()->get('analysisRequest');

$correlationId = '';
if (isset($request['correlationId'])) {
    $correlationId = '&correlationId='.$request['correlationId'];
}

$output = [];
foreach ($request['apis'] as $api) {
    $apisOutput = [];
    foreach ($request['dates'] as $date) {
        
        Pull::run($state,[
            'host' => $state->memory()->get('hefesto-localhost'),
            'path' => "/analysis/$api/analysis?date=".$date.$correlationId,
            'body' => '',
            'verify' => false
        ]);
        if ($state->message()->getStatus() === 201) {
            $apisOutput[] = $state->message()->getBodyAsArray();
        }
    }
    MergeApisOutput::run($state,[
        'apisOutput' => $apisOutput
    ]);
    $output[$api] = $state->memory()->get('mergedApisOutput');
}

WriteToJob::run($state,[
    'value' => $output
]);
