<?php
use App\Services\AzureOpenAIClient;

Route::get('/ai-test', function (AzureOpenAIClient $client) {

    $result = $client->analyze("This message says I won a prize and should click a link to claim. Is it a scam?");

    return response()->json($result);
});

Route::get('/dns-test', function () {
    return gethostbyname('sharplync-openai-aue.openai.azure.com');
});

