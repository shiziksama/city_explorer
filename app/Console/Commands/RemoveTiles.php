<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class RemoveTiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tiles:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $tiles=\DB::table('tiles_to_delete')->orderBy('zoom','desc')->limit(1000)->get();
		$tiles_new=[];
		foreach($tiles as $tile){
			
			$path=base_path('map_overlay/'.$tile->user_id.'/'.$tile->zoom.'/'.$tile->x.'/'.$tile->y.'.png');
			if(file_exists($path)){
				unlink($path);
			}
			\DB::table('tiles_to_delete')->where('user_id',$tile->user_id)->where('zoom',$tile->zoom)->where('x',$tile->x)->where('y',$tile->y)->delete();
			if($tile->zoom<0)continue;
			$tiles_new[]=json_encode(['x'=>floor($tile->x/2),'y'=>floor($tile->y/2),'zoom'=>$tile->zoom-1,'user_id'=>$tile->user_id]);
		}
		$tiles=array_values(array_unique($tiles_new));
		$tiles=array_map(function($v){
			return json_decode($v,true);
		},$tiles);
		\DB::table('tiles_to_delete')->insertOrIgnore($tiles);
    }
}
