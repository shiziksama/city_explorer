<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Track;

class SimplificateTrackRemovebiglines extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'simplificate:remove_big {id}';

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
        $id = $this->argument('id');
        $track=Track::find($id);
        $track->remove_big_lines();
        $track->save();
        return 0;
        
    }
}
