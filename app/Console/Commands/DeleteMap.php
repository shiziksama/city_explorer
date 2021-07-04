<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DeleteMap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'map:delete {zoom} {x} {y}';

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
	public function remove_file($file){
		if(file_exists($file)){
			var_dump('remove|'.$file);
			unlink($file);
		}
	}
	public function remove($zoom,$x,$y){
		$this->remove_file(base_path('lb_map/'.$zoom.'/'.$x.'/'.$y.'.png'));
		$this->remove_file(base_path('lb_overlay/'.$zoom.'/'.$x.'/'.$y.'.png'));
		$this->remove_file(base_path('lb_json/l_'.$zoom.'.'.$x.'.'.$y.'.json'));
	}
	public function removeBigger($zoom,$x,$y){
		if($zoom<8){
			var_dump('remove_bigger|'.$zoom.'.'.$x.'.'.$y);
		}
		
		$this->remove($zoom,$x,$y);
		if($zoom==12)return;
		$this->removeBigger($zoom+1,$x*2,$y*2);
		$this->removeBigger($zoom+1,$x*2+1,$y*2);
		$this->removeBigger($zoom+1,$x*2,$y*2+1);
		$this->removeBigger($zoom+1,$x*2+1,$y*2+1);
	}
	public function run_with_params($zoom,$x,$y){
		if($zoom>6){
			$zoom-=1;
			$x=floor($x/2);
			$y=floor($y/2);
		}
		$this->removeBigger($zoom,$x,$y);
		for($i=$zoom;$i>0;$i--){
			$this->remove($i,$x,$y);
			$x=floor($x/2);
			$y=floor($y/2);
		}
	}
    public function handle()
    {
		
		if(file_exists(base_path('delete.txt'))){
			$s=file_get_contents(base_path('delete.txt'));
			$s=explode("\n",$s);
			foreach($s as $path){
				if(empty($path))continue;
				$l=(pathinfo($path,PATHINFO_BASENAME));
				$l=substr($l,2);
				$l=explode('.',$l);
				$this->run_with_params($l[0],$l[1],$l[2]);
			}
		}else{
			$zoom = $this->argument('zoom');
			$x=$this->argument('x');
			$y=$this->argument('y');
			$this->run_with_params($zoom,$x,$y);
		}
		var_dump('completed');
		return 0;
    }
}
