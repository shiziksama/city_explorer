<?php

namespace App\Jobs;

use App\Services\StravaService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TrackgetStrava implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $token_id;

    public function __construct($token_id)
    {
        $this->token_id = $token_id;
    }

    public function handle(StravaService $service)
    {
        $service->syncActivities($this->token_id);
    }
}
