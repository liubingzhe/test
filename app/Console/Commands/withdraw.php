<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class withdraw extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clearlog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'this is a clearlog crontab';

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
     * @return mixed
     */
    public function handle()
    {
        //
        DB::table('sms_code')->insert(['mobile'=>'1325666','sms_code'=>'1111']);
//        echo 111;die;
    }
}
