<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RemoveTilesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
	protected $user_id;
	protected $points;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($user_id,$points)
    {
        $this->user_id=$user_id;
        $this->points=$points;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
		$zoom=18;//maximum zoom
		$tiles=[];
		foreach($this->points as $point){
			$xtile = floor((($point['lng'] + 180) / 360) * pow(2, $zoom));
			$ytile = floor((1 - log(tan(deg2rad($point['lat'])) + 1 / cos(deg2rad($point['lat']))) / pi()) /2 * pow(2, $zoom));
			$tiles[]=json_encode(['x'=>$xtile,'y'=>$ytile,'zoom'=>$zoom,'user_id'=>$this->user_id]);
		}
		$tiles=array_values(array_unique($tiles));
		$tiles=array_map(function($v){
			return json_decode($v,true);
		},$tiles);
		\DB::table('tiles_to_delete')->insertOrIgnore($tiles);
	}
}
