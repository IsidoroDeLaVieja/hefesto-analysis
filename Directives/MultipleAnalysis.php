<?php /*dlv-code-engine***/

$request = $state->memory()->get('analysisRequest');

$output = [];
foreach ($request['apis'] as $api) {
    $apisOutput = [];
    foreach ($request['dates'] as $date) {
        
        $queryParams = [
            'date' => $date
        ];

        if (isset($request['correlationId'])) {
            $queryParams['correlationId'] = $request['correlationId'];
        }

        Pull::run($state,[
            'host' => $state->memory()->get('hefesto-localhost'),
            'path' => "/analysis/$api/analysis",
            'body' => '',
            'verify' => false,
            'queryParams' => $queryParams
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
