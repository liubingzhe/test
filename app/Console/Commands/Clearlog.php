<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Clearlog extends Command
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
        $user_ids = DB::table('user')
            ->where('is_valid',1)
            ->get('id');
        DB::transaction(function () use ($user_ids) {
            DB::table('sms_code')->delete();
            DB::table('user_account_log')->delete();
            DB::table('user_gift_bag_log')->delete();
            DB::table('user_idiom_log')->delete();
            DB::table('stage_pass_log')->delete();
            DB::table('user_tools_log')->delete();
            DB::table('signin')->delete();

            foreach ($user_ids as $k => $v) {
                $user_id = $v->id;
                DB::table('user')
                    ->where('id',$user_id)
                    ->update(['stars'=>0]);
            }
        });
    }
}
