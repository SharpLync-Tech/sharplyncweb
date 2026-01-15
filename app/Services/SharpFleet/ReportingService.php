public function streamTripReportCsv(int $organisationId, Request $request, ?array $actor = null)
{
    $settingsService = new CompanySettingsService($organisationId);
    $clientLabel = trim((string) $settingsService->clientLabel());
    $clientLabel = $clientLabel !== '' ? $clientLabel : 'Client';

    $result = $this->buildTripReport($organisationId, $request, $actor);
    /** @var \Illuminate\Support\Collection<int, object> $trips */
    $trips = $result['trips'];

    $customerLinkingEnabled = (bool) ($result['customerLinkingEnabled'] ?? false);
    $purposeOfTravelEnabled = $settingsService->purposeOfTravelEnabled();

    $headers = [
        'Vehicle',
        'Rego',
        'Driver',
        'Trip Mode',
        $customerLinkingEnabled ? $clientLabel : null,
        $purposeOfTravelEnabled ? 'Purpose of Travel' : null,
        'Start Reading',
        'End Reading',
        'Client Present',
        'Client Address',
        'Started At',
        'Ended At',
    ];
    $headers = array_values(array_filter($headers, fn ($h) => $h !== null));

    $callback = function () use ($trips, $headers, $customerLinkingEnabled, $purposeOfTravelEnabled) {
        $file = fopen('php://output', 'w');
        fputcsv($file, $headers);

        foreach ($trips as $trip) {
            $unit = isset($trip->display_unit)
                ? (string) $trip->display_unit
                : ((($trip->tracking_mode ?? 'distance') === 'hours') ? 'hours' : 'km');

            $startReading = $trip->display_start ?? $trip->start_km;
            $endReading   = $trip->display_end ?? $trip->end_km;

            $row = [
                $trip->vehicle_name,
                $trip->registration_number,
                $trip->driver_name,
                $trip->trip_mode,
            ];

            if ($customerLinkingEnabled) {
                $row[] = $trip->customer_name_display ?? '';
            }

            if ($purposeOfTravelEnabled) {
                $row[] = strtolower((string) $trip->trip_mode) !== 'private'
                    ? ($trip->purpose_of_travel ?? '')
                    : '';
            }

            $row = array_merge($row, [
                $startReading !== null ? $startReading . ' ' . $unit : '',
                $endReading !== null ? $endReading . ' ' . $unit : '',
                $trip->client_present ? 'Yes' : 'No',
                $trip->client_address ?? 'N/A',
                $trip->started_at,
                $trip->ended_at,
            ]);

            fputcsv($file, $row);
        }

        fclose($file);
    };

    return response()->stream($callback, 200, [
        'Content-Type' => 'text/csv',
        'Content-Disposition' => 'attachment; filename="trips_' . date('Y-m-d') . '.csv"',
    ]);
}
