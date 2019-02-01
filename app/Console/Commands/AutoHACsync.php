<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AutohacController;
use App\Model\AutoHAC\AutohacUser;
use App\Model\AutoHAC\AutohacSchool;
use Log;

class AutoHACsync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AutoHAC:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all AutoHAC users, sending messages as necessary.';
    
    protected $autohacController;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(AutohacController $autohacController)
    {
        parent::__construct();
        $this->autohacController = $autohacController;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
		//Don't run from 9PM-5AM
	    $nowHour = \Carbon\Carbon::now()->hour - 1;
	    if ($nowHour < 0) {
		    $nowHour += 24;
	    }
	    $nowDayOfYear = \Carbon\Carbon::now()->dayOfYear;
	    
	    // Crude approximate autonomous switch
	    $mp = 0;
	    $nd = $nowDayOfYear - 170;
	    if ($nd < 1) {
		    $nd += 365;
	    }
	    if ($nd > 62) {
		    $mp = 1;
	    }
	    if ($nd > 142) {
		    $mp = 2;
	    }
	    if ($nd > 221) {
		    $mp = 3;
	    }
	    if ($nd > 290) {
		    $mp = 4;
	    }
		$oldmp = file_get_contents("/var/www/laravel/storage/mp.txt");
		if ($oldmp != $mp) {
			file_put_contents("/var/www/laravel/storage/mp.txt", $mp);
			$epsd = AutohacSchool::where('id', 1)->first();
			$epsd->current_mp = $mp;
			$epsd->save();
			if ($oldmp == 0 && $mp == 1) {
				$allMsg = "School is coming up, and AutoHAC has reactivated for marking period 1! Type ? for help.";
			} elseif ($oldmp == $mp - 1) {
				$allMsg = "AutoHAC has switched to marking period " . $mp . ". You will not get messages for any further assignments in MP" . $oldmp . ".";
			} elseif ($mp == 0) {
				$allMsg = "Looks like school is out for the year! AutoHAC is shutting down until next year.";
			}
		}
		
	    if (($mp > 0) && ($nowHour > 7 || isset($this->autohacController->onlyUser))) {
		    $users = AutohacUser::where('signup_code', null)->get();
		    //Log::info($users[rand(0, count($users) - 1)]->id);
		    
		    foreach ($users as $user) {
			    $uname = $user->username;
			    /*if (substr($uname, strlen($uname) - 2) == "18") {
				    $user->sendMsg("Thank you so much for using and supporting AutoHAC. You have graduated so your account has been deleted. Good luck in your future.");
				    $user->delete();
				    echo $uname . "\n";
				    sleep(1);
			    }*/
			    
			    if (!isset($this->autohacController->onlyUser) || $user->real_name == $this->autohacController->onlyUser) {
				    try {
					    set_time_limit(30);
					    if (isset($allMsg)) {
						    $user->sendMsg($allMsg);
					    }
					    $this->autohacController->syncCourses($user);
				    } catch (\Throwable $e) {
					    Log::warning('AutoHAC:sync failed for user ' . $user->id . ' because ' . $e);
				    }
			    }
		    }
		    //Log::info('AutoHAC:sync success!');
	    }
    }
}
