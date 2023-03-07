<?php

namespace App\Flare\Github\Commands;

use App\Flare\Github\Services\Github;
use App\Flare\Models\ReleaseNote;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Exception;


class GetReleaseData extends Command {

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:release-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetches Github Release Data';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param Github $github
     * @return void
     * @throws Exception
     */
    public function handle(Github $github): void {
        $releaseData = $github->initiateClient()->fetchLatestRelease();

        $notes  = ReleaseNote::where('url', $releaseData['html_url'])->first();

        if (is_null($notes)) {
            ReleaseNote::create([
                'name'         => $releaseData['name'],
                'version'      => $releaseData['tag_name'],
                'url'          => $releaseData['html_url'],
                'release_date' => Carbon::parse($releaseData['published_at']),
                'body'         => $releaseData['body'],
            ]);
        }
    }
}
