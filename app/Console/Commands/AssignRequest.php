<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Request as RequestModel;
use Illuminate\Support\Facades\Log;

class AssignRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Assign-request';

    protected $description = 'Command description';

    public function handle()
    {

        $requestsNotAssignProvider = Requestmodel::select("requests.*")
        ->leftJoin("requests_providers","requests_providers.request_id","requests.id")
        ->whereNull("requests_providers.request_id")
        ->get();

        // return count($requestsNotAssignProvider);

        foreach($requestsNotAssignProvider as $requestNotAssignProvider){
            $startTime = microtime(true);
            $requestNotAssignProvider->startFindProvider();
            $endTime = microtime(true);
        }
        return Command::SUCCESS;
    }
}
