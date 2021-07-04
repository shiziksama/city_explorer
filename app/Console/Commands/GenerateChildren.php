<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateChildren extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'map:genchildren  {zoom} {x} {y}';

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
		$zoom = $this->argument('zoom');
		$x=$this->argument('x');
		$y=$this->argument('y');
		\App\Jobs\RenderMap::dispatchNow($zoom+1,$x*2,$y*2);
		\App\Jobs\RenderMap::dispatchNow($zoom+1,$x*2,$y*2+1);
		\App\Jobs\RenderMap::dispatchNow($zoom+1,$x*2+1,$y*2);
		\App\Jobs\RenderMap::dispatchNow($zoom+1,$x*2+1,$y*2+1);
        return 0;
    }
}
