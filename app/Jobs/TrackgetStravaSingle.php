<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\StravaService;

class TrackgetStravaSingle implements ShouldQueue 
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	protected $token_id;
	protected $track_id;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($token_id,$track_id)
    {
        $this->token_id=$token_id;
		$this->track_id=$track_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(StravaService $service)
    {
        $service->fetchSingleActivity($this->token_id, $this->track_id);
    }
}
