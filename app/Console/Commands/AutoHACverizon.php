<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\AutohacController;
use Log;

class AutoHACverizon extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'AutoHAC:verizon {a?} {b?} {c?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Email handler for Verizon AutoHAC.';
    
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
	    $mess = file("php://stdin");
		$fromLine = $mess[0];
		$fromAddress = explode(" ", $fromLine)[1];
		if ($fromAddress == "MAILER-DAEMON")
			return;
		$fromNuma = explode("@", $fromAddress);
		$fromNum = (int)$fromNuma[0];
		if ($fromNum == 0)
			return;
		$msgline = -1;
		foreach ($mess as $mi => $ml) {
			if (strpos($ml, "X-Spam-Flag: NO") !== false) {
				$msgline = $mi + 2;
			}
			if (strpos($ml, "Content-Location:") !== false) {
				$msgline = $mi + 2;
			}
		}
		$msgIn = trim(trim($mess[$msgline]), ":");
		$this->autohacController->handleVerizon($fromNum, $msgIn);
    }
}
