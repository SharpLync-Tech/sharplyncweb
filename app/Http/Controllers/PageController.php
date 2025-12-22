<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\CMS\Testimonial;

class PageController extends Controller
{
    public function about()
    {
        return $this->loadTestimonials('about');
    }

    public function testimonials()
    {
        return $this->loadTestimonials('testimonials');
    }

    /**
     * Shared loader with heavy diagnostics
     */
    protected function loadTestimonials(string $page)
    {
        // ---------- MODEL INTROSPECTION ----------
        $model = new Testimonial();

        $modelDebug = [
            'model_class'      => get_class($model),
            'connection_name'  => $model->getConnectionName(),
            'table'            => $model->getTable(),
            'primary_key'      => $model->getKeyName(),
            'timestamps'       => $model->usesTimestamps(),
        ];

        // ---------- CONNECTION INTROSPECTION ----------
        try {
            $connection = DB::connection($model->getConnectionName());
            $pdo = $connection->getPdo();

            $dbDebug = [
                'driver'   => $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME),
                'database' => $connection->getDatabaseName(),
                'status'   => 'connected',
            ];
        } catch (\Throwable $e) {
            $dbDebug = [
                'status'  => 'FAILED',
                'error'   => $e->getMessage(),
            ];
        }

        // ---------- RAW COUNT (NO ELOQUENT) ----------
        try {
            $rawCount = DB::connection($model->getConnectionName())
                ->table($model->getTable())
                ->count();
        } catch (\Throwable $e) {
            $rawCount = 'FAILED: ' . $e->getMessage();
        }

        // ---------- ELOQUENT QUERY ----------
        try {
            $testimonials = Testimonial::where('is_active', 1)
                ->orderBy('display_order', 'asc')
                ->orderByDesc('created_at')
                ->get();

            $eloquentCount = $testimonials->count();
        } catch (\Throwable $e) {
            $testimonials   = collect();
            $eloquentCount = 'FAILED: ' . $e->getMessage();
        }

        // ---------- LOG EVERYTHING ----------
        Log::warning('TESTIMONIALS DEBUG [' . $page . ']', [
            'model'           => $modelDebug,
            'db'              => $dbDebug,
            'raw_table_count' => $rawCount,
            'eloquent_count'  => $eloquentCount,
        ]);

        // ---------- PASS DEBUG TO VIEW ----------
        return view($page, [
            'testimonials' => $testimonials,
            'debug' => [
                'model'           => $modelDebug,
                'db'              => $dbDebug,
                'raw_table_count' => $rawCount,
                'eloquent_count'  => $eloquentCount,
            ],
        ]);
    }
}
