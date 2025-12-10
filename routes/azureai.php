<?php
use App\Services\AzureOpenAIClient;

Route::get('/ai-test', function (AzureOpenAIClient $client) {

    $result = $client->analyze("You have won $5000! Click the link to claim: http://fake-site.scam");

    return response()->json($result);
});

Route::get('/dns-test', function () {
    return gethostbyname('sharplync-openai-aue.openai.azure.com');
});

