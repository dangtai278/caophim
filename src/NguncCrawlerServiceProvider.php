<?php

namespace Nguonc\Crawler\NguoncCrawler;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as SP;
use Nguonc\Crawler\NguoncmCrawler\Console\CrawlerScheduleCommand;
use Nguonc\Crawler\NguoncCrawler\Option;

class NguoncCrawlerServiceProvider extends SP
{
    /**
     * Get the policies defined on the provider.
     *
     * @return array
     */
    public function policies()
    {
        return [];
    }

    public function register()
    {

        config(['plugins' => array_merge(config('plugins', []), [
            'dangtai278/nguonc-crawler' =>
            [
                'name' => 'Nguonc Crawler',
                'package_name' => 'dangtai278/caophim',
                'icon' => 'la la-hand-grab-o',
                'entries' => [
                    ['name' => 'Crawler', 'icon' => 'la la-hand-grab-o', 'url' => backpack_url('/plugin/kkphim-crawler')],
                    ['name' => 'Option', 'icon' => 'la la-cog', 'url' => backpack_url('/plugin/nguonc-crawler/options')],
                ],
            ]
        ])]);

        config(['logging.channels' => array_merge(config('logging.channels', []), [
            'nguonc-crawler' => [
                'driver' => 'daily',
                'path' => storage_path('logs/dangtai278/nguonc-crawler.log'),
                'level' => env('LOG_LEVEL', 'debug'),
                'days' => 7,
            ],
        ])]);

        config(['nguonc.updaters' => array_merge(config('nguonc.updaters', []), [
            [
                'name' => 'Nguonc Crawler',
                'handler' => 'Nguonc\Crawler\NguoncCrawler\Crawler'
            ]
        ])]);
    }

    public function boot()
    {
        $this->commands([
            CrawlerScheduleCommand::class,
        ]);

        $this->app->booted(function () {
            $this->loadScheduler();
        });

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'nguonc-crawler');
    }

    protected function loadScheduler()
    {
        $schedule = $this->app->make(Schedule::class);
        $schedule->command('nguonc:plugins:nguonc-crawler:schedule')->cron(Option::get('crawler_schedule_cron_config', '*/10 * * * *'))->withoutOverlapping();
    }
}
