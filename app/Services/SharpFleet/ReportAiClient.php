<?php

namespace App\Services\SharpFleet;

use GuzzleHttp\Client;

class ReportAiClient
{
    protected Client $client;
    protected string $endpoint;
    protected string $deployment;
    protected string $apiKey;

    public function __construct()
    {
        $this->endpoint = rtrim((string) env('AZURE_OPENAI_ENDPOINT'), '/');
        $this->deployment = (string) env('AZURE_OPENAI_DEPLOYMENT');
        $this->apiKey = (string) env('AZURE_OPENAI_KEY');

        $this->client = new Client([
            'base_uri' => $this->endpoint,
            'timeout' => 25,
        ]);
    }

    public function generateReport(string $prompt, array $context = []): ?array
    {
        if ($this->endpoint === '' || $this->deployment === '' || $this->apiKey === '') {
            return null;
        }

        $timezone = trim((string) ($context['timezone'] ?? 'Australia/Brisbane'));
        $dateFormat = trim((string) ($context['date_format'] ?? 'd/m/Y'));
        $distanceUnit = strtolower(trim((string) ($context['distance_unit'] ?? 'km')));
        $distanceUnit = $distanceUnit === 'mi' ? 'mi' : 'km';

        $system = implode("\n", [
            'You are the SharpFleet AI report builder.',
            'Return valid JSON only. No markdown or prose outside JSON.',
            'Schema:',
            '{',
            '  "title": "string",',
            '  "summary": "string",',
            '  "sections": [',
            '    {"heading": "string", "bullets": ["string"]}',
            '  ],',
            '  "recommended_filters": ["string"],',
            '  "key_metrics": ["string"],',
            '  "caveats": ["string"]',
            '}',
            'Rules:',
            '- Keep content operational (fleet usage, trips, vehicles, branches, drivers).',
            '- Do NOT mention tax authorities, ATO, or registration abbreviations like "Rego".',
            '- Keep the report concise and practical for fleet managers.',
        ]);

        $user = implode("\n", [
            'User request:',
            trim($prompt),
            '',
            'Context:',
            "- Timezone: {$timezone}",
            "- Date format: {$dateFormat}",
            "- Distance unit: {$distanceUnit}",
            '- Available data tables: trips, vehicles, branches, users, company_settings.',
        ]);

        try {
            $response = $this->client->post(
                "/openai/deployments/{$this->deployment}/chat/completions",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'api-key' => $this->apiKey,
                    ],
                    'query' => [
                        'api-version' => '2024-10-01-preview',
                    ],
                    'json' => [
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => $system,
                            ],
                            [
                                'role' => 'user',
                                'content' => $user,
                            ],
                        ],
                        'temperature' => 0.2,
                        'max_tokens' => 700,
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);
            $content = $data['choices'][0]['message']['content'] ?? '';
            $json = json_decode($content, true);

            if (!is_array($json)) {
                return null;
            }

            return $this->normaliseReport($json);
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function parseIntent(string $prompt): ?array
    {
        if ($this->endpoint === '' || $this->deployment === '' || $this->apiKey === '') {
            return null;
        }

        $system = implode("\n", [
            'You are the SharpFleet AI Report Intent Parser.',
            '',
            'You translate a user\'s request into a structured reporting intent.',
            'You do NOT execute queries.',
            'You do NOT generate SQL.',
            'You do NOT invent data.',
            '',
            'Return VALID JSON ONLY that matches the provided schema.',
            'If a request is ambiguous or unsupported, set unsupported to true.',
            '',
            'Rules:',
            '- Use only supported entities and filters.',
            '- Never guess dates or names.',
            '- Never output prose or explanations.',
        ]);

        try {
            $response = $this->client->post(
                "/openai/deployments/{$this->deployment}/chat/completions",
                [
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'api-key' => $this->apiKey,
                    ],
                    'query' => [
                        'api-version' => '2024-10-01-preview',
                    ],
                    'json' => [
                        'messages' => [
                            [
                                'role' => 'system',
                                'content' => $system,
                            ],
                            [
                                'role' => 'user',
                                'content' => implode("\n", [
                                    'Schema:',
                                    '{',
                                    '  "report_type": "trips",',
                                    '  "entities": {',
                                    '    "driver": string|null,',
                                    '    "customer": string|null,',
                                    '    "vehicle": string|null',
                                    '  },',
                                    '  "filters": {',
                                    '    "client_present": boolean|null,',
                                    '    "date_range": {',
                                    '      "from": string|null,',
                                    '      "to": string|null',
                                    '    }',
                                    '  },',
                                    '  "unsupported": boolean',
                                    '}',
                                    '',
                                    'User request:',
                                    trim($prompt),
                                ]),
                            ],
                        ],
                        'temperature' => 0.1,
                        'max_tokens' => 220,
                    ],
                ]
            );

            $data = json_decode($response->getBody()->getContents(), true);
            $content = $data['choices'][0]['message']['content'] ?? '';
            $json = json_decode($content, true);

            if (!is_array($json)) {
                return null;
            }

            $reportType = strtolower(trim((string) ($json['report_type'] ?? '')));
            if ($reportType !== 'trips') {
                return null;
            }

            $entities = is_array($json['entities'] ?? null) ? $json['entities'] : [];
            $filters = is_array($json['filters'] ?? null) ? $json['filters'] : [];
            $dateRange = is_array($filters['date_range'] ?? null) ? $filters['date_range'] : [];

            $driver = array_key_exists('driver', $entities) ? $entities['driver'] : null;
            $customer = array_key_exists('customer', $entities) ? $entities['customer'] : null;
            $vehicle = array_key_exists('vehicle', $entities) ? $entities['vehicle'] : null;

            $driver = is_string($driver) && trim($driver) !== '' ? trim($driver) : null;
            $customer = is_string($customer) && trim($customer) !== '' ? trim($customer) : null;
            $vehicle = is_string($vehicle) && trim($vehicle) !== '' ? trim($vehicle) : null;

            $clientPresent = array_key_exists('client_present', $filters) ? $filters['client_present'] : null;
            if (!is_bool($clientPresent)) {
                $clientPresent = null;
            }

            $dateFrom = array_key_exists('from', $dateRange) ? $dateRange['from'] : null;
            $dateTo = array_key_exists('to', $dateRange) ? $dateRange['to'] : null;
            $dateFrom = is_string($dateFrom) && trim($dateFrom) !== '' ? trim($dateFrom) : null;
            $dateTo = is_string($dateTo) && trim($dateTo) !== '' ? trim($dateTo) : null;

            $unsupported = $json['unsupported'] ?? null;
            $unsupported = is_bool($unsupported) ? $unsupported : null;

            if ($driver === null && $customer === null && $vehicle === null) {
                $fallback = $this->fallbackIntentFromPrompt($prompt);
                if ($fallback !== null) {
                    $driver = $fallback['driver'] ?? $driver;
                    $customer = $fallback['customer'] ?? $customer;
                    $vehicle = $fallback['vehicle'] ?? $vehicle;
                }
            }

            $hasEntity = ($driver !== null || $customer !== null || $vehicle !== null);
            $hasFilter = ($clientPresent !== null || $dateFrom !== null || $dateTo !== null);

            if ($unsupported === null) {
                $unsupported = !($hasEntity || $hasFilter);
            }

            return [
                'report_type' => 'trips',
                'entities' => [
                    'driver' => $driver,
                    'customer' => $customer,
                    'vehicle' => $vehicle,
                ],
                'filters' => [
                    'client_present' => $clientPresent,
                    'date_range' => [
                        'from' => $dateFrom,
                        'to' => $dateTo,
                    ],
                ],
                'unsupported' => $unsupported,
            ];
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function fallbackIntentFromPrompt(string $prompt): ?array
    {
        $text = trim($prompt);
        if ($text === '') {
            return null;
        }

        $result = [
            'driver' => null,
            'customer' => null,
            'vehicle' => null,
        ];

        // Examples:
        // "show all trips jannie made for Apple and Grape"
        // "show all trips by Jannie for Apple and Grape"
        if (preg_match('/trips?\s+(.*?)\s+made\s+for\s+(.+)$/i', $text, $m)) {
            $result['driver'] = $this->cleanEntityName($m[1] ?? '');
            $result['customer'] = $this->cleanEntityName($m[2] ?? '');
        } elseif (preg_match('/trips?\s+by\s+(.*?)\s+for\s+(.+)$/i', $text, $m)) {
            $result['driver'] = $this->cleanEntityName($m[1] ?? '');
            $result['customer'] = $this->cleanEntityName($m[2] ?? '');
        } elseif (preg_match('/trips?\s+for\s+(.+)$/i', $text, $m)) {
            $result['customer'] = $this->cleanEntityName($m[1] ?? '');
        }

        if ($result['driver'] === '' || $result['driver'] === null) {
            $result['driver'] = null;
        }
        if ($result['customer'] === '' || $result['customer'] === null) {
            $result['customer'] = null;
        }

        if ($result['driver'] === null && $result['customer'] === null && $result['vehicle'] === null) {
            return null;
        }

        return $result;
    }

    private function cleanEntityName(string $value): string
    {
        $name = trim($value);
        $name = preg_replace('/[\\?\\.!]+$/', '', $name);
        return trim((string) $name);
    }

    private function normaliseReport(array $json): ?array
    {
        $title = isset($json['title']) ? trim((string) $json['title']) : '';
        $summary = isset($json['summary']) ? trim((string) $json['summary']) : '';
        $sections = is_array($json['sections'] ?? null) ? $json['sections'] : [];
        $filters = is_array($json['recommended_filters'] ?? null) ? $json['recommended_filters'] : [];
        $metrics = is_array($json['key_metrics'] ?? null) ? $json['key_metrics'] : [];
        $caveats = is_array($json['caveats'] ?? null) ? $json['caveats'] : [];

        if ($title === '' && $summary === '' && $sections === []) {
            return null;
        }

        $sections = array_values(array_filter(array_map(function ($section) {
            if (!is_array($section)) {
                return null;
            }

            $heading = trim((string) ($section['heading'] ?? ''));
            $bulletsRaw = $section['bullets'] ?? [];
            $bullets = is_array($bulletsRaw)
                ? array_values(array_filter(array_map('strval', $bulletsRaw)))
                : [];

            if ($heading === '' && $bullets === []) {
                return null;
            }

            return [
                'heading' => $heading === '' ? 'Overview' : $heading,
                'bullets' => $bullets,
            ];
        }, $sections)));

        $filters = array_values(array_filter(array_map('strval', $filters)));
        $metrics = array_values(array_filter(array_map('strval', $metrics)));
        $caveats = array_values(array_filter(array_map('strval', $caveats)));

        return [
            'title' => $title !== '' ? $title : 'AI Report Builder (Beta)',
            'summary' => $summary,
            'sections' => $sections,
            'recommended_filters' => $filters,
            'key_metrics' => $metrics,
            'caveats' => $caveats,
        ];
    }
}
