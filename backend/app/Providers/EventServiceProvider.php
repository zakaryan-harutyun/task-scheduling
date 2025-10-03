<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use App\Events\TaskAssigned;
use App\Jobs\NotifyUserOfTaskJob;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        TaskAssigned::class => [
            NotifyUserOfTaskJob::class,
        ],
    ];
}


