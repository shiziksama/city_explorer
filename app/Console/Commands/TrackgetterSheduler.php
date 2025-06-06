<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Token;

class TrackgetterSheduler extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'trackget:schedule';

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
		$tokens=Token::all();
		foreach($tokens as $token){
			$string='\App\Jobs\Trackget'.ucfirst($token->service);
			$string::dispatch($token->id)->onQueue('parsers');
		}
    }
}
