<?php

namespace App\Services;

use App\Models\AnalyticsPageView;
use Carbon\Carbon;
use Google\Analytics\Data\V1beta\Client\BetaAnalyticsDataClient;
use Google\Analytics\Data\V1beta\DateRange;
use Google\Analytics\Data\V1beta\Dimension;
use Google\Analytics\Data\V1beta\Metric;
use Google\Analytics\Data\V1beta\OrderBy;
use Google\Analytics\Data\V1beta\RunReportRequest;
use Google\Auth\Credentials\ServiceAccountCredentials;

class AnalyticsService
{
    protected $client;

    protected $propertyId;

    public function __construct()
    {
        $this->propertyId = config('analytics.property_id');

        $credentials = new ServiceAccountCredentials(
            'https://www.googleapis.com/auth/analytics.readonly',
            config('analytics.credentials')
        );

        $this->client = new BetaAnalyticsDataClient([
            'credentials' => $credentials,
        ]);
    }

    public function getTodayActiveUsers()
    {
        $request = new RunReportRequest([
            'property' => 'properties/'.$this->propertyId,
            'date_ranges' => [
                new DateRange([
                    'start_date' => '7daysAgo',
                    'end_date' => 'today',
                ]),
            ],
            'metrics' => [
                new Metric(['name' => 'activeUsers']),
            ],
            'dimensions' => [
                new Dimension(['name' => 'date']),
            ],
        ]);

        $response = $this->client->runReport($request);

        $rows = $response->getRows();

        // dd($response->getRows());

        return $rows[0]->getMetricValues()[0]->getValue() ?? 0;
    }

    public function getMonthlyActiveUsers(): array
    {
        Carbon::setLocale('tr');

        $start = Carbon::now()->subDays(15);
        $end = Carbon::now();

        $days = collect();

        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $days[$date->format('Y-m-d')] = 0; // tarih anahtarı tam formatta
        }

        $request = new RunReportRequest([
            'property' => 'properties/'.$this->propertyId,
            'date_ranges' => [
                new DateRange([
                    'start_date' => '15daysAgo',
                    'end_date' => 'today',
                ]),
            ],
            'dimensions' => [
                new Dimension(['name' => 'date']),
            ],
            'metrics' => [
                new Metric(['name' => 'screenPageViews']),
            ],
        ]);

        $response = $this->client->runReport($request);

        foreach ($response->getRows() as $row) {
            $date = Carbon::createFromFormat('Ymd', $row->getDimensionValues()[0]->getValue());
            $days[$date->format('Y-m-d')] = (int) $row->getMetricValues()[0]->getValue();
        }
        // dd($days);

        return $days->toArray();

    }

    public function getTopDailyUrls(string $date = 'today', int $limit = 10): array
    {
        $request = new RunReportRequest([
            'property' => 'properties/'.$this->propertyId,
            'date_ranges' => [
                new DateRange([
                    'start_date' => $date,
                    'end_date' => $date,
                ]),
            ],
            'dimensions' => [
                new Dimension(['name' => 'pagePath']),
            ],
            'metrics' => [
                new Metric(['name' => 'screenPageViews']),
            ],
            'order_bys' => [
                new OrderBy([
                    'metric' => new OrderBy\MetricOrderBy(['metric_name' => 'screenPageViews']),
                    'desc' => true,
                ]),
            ],
            'limit' => $limit,
        ]);

        $response = $this->client->runReport($request);

        $result = [];

        foreach ($response->getRows() as $row) {
            $result[] = [
                'page_path' => $row->getDimensionValues()[0]->getValue(),
                'views' => (int) $row->getMetricValues()[0]->getValue(),
            ];
        }

        return $result;
    }

    public function syncTopDailyUrls(string $date, int $limit = 50): void
    {
        // Property ID'yi config'ten al
        $propertyId = $this->propertyId;

        // RunReportRequest nesnesini oluştur
        $request = new RunReportRequest([
            'property' => "properties/$propertyId",
            'date_ranges' => [
                new DateRange([
                    'start_date' => $date,
                    'end_date' => $date,
                ]),
            ],
            'metrics' => [
                new Metric(['name' => 'screenPageViews']),
            ],
            'dimensions' => [
                new Dimension(['name' => 'pagePath']),
            ],
            'order_bys' => [
                new OrderBy([
                    'metric' => new OrderBy\MetricOrderBy(['metric_name' => 'screenPageViews']),
                    'desc' => true,
                ]),
            ],
            'limit' => $limit,
        ]);

        $response = $this->client->runReport($request);

        // dd($response->getRows());

        // Aynı tarihli eski kayıtları sil
        AnalyticsPageView::where('date', $date)->delete();

        // Gelen rapor satırlarını kaydet
        foreach ($response->getRows() as $row) {
            $pagePath = $row->getDimensionValues()[0]->getValue();
            $views = (int) $row->getMetricValues()[0]->getValue();

            AnalyticsPageView::create([
                'page_path' => $pagePath,
                'views' => $views,
                'date' => $date,
            ]);
        }
    }
}
