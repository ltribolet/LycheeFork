<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Http\Controllers\ImportController;
use App\ModelFunctions\AlbumFunctions;
use App\ModelFunctions\PhotoFunctions;
use App\ModelFunctions\SessionFunctions;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;

class Sync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'lychee:sync {dir : directory to sync} {--album_id=0 : Album ID to import to} {--owner_id=0 : Owner ID of imported photos} {--resync_metadata : Re-sync metadata of existing files}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync a directory to lychee';

    /**
     * @var AlbumFunctions
     */
    private $albumFunctions;

    /**
     * @var PhotoFunctions
     */
    private $photoFunctions;

    /**
     * @var SessionFunctions
     */
    private $sessionFunctions;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        PhotoFunctions $photoFunctions,
        AlbumFunctions $albumFunctions,
        SessionFunctions $sessionFunctions
    ) {
        parent::__construct();

        $this->photoFunctions = $photoFunctions;
        $this->albumFunctions = $albumFunctions;
        $this->sessionFunctions = $sessionFunctions;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Start syncing.');
        $directory = $this->argument('dir');
        // in case no ID provided -> import as root user
        $owner_id = (int) $this->option('owner_id');
        // in case no ID provided -> import to root folder
        $album_id = (int) $this->option('album_id');
        $resync_metadata = $this->option('resync_metadata');
        // we want to sync -> do not delete imported files
        $delete_imported = false;
        $force_skip_duplicates = true;
        $import_controller = new ImportController(
            $this->photoFunctions,
            $this->albumFunctions,
            $this->sessionFunctions
        );

        // Enable CLI formatting of status
        $import_controller->enableCLIStatus();
        // Disable Memory Check
        $import_controller->disableMemCheck();

        Session::put('UserID', $owner_id);
        Session::put('login', true);
        try {
            $import_controller->server_exec(
                $directory,
                (int) $album_id,
                $delete_imported,
                $force_skip_duplicates,
                null,
                $resync_metadata
            );
        } catch (\Throwable $e) {
            $this->error($e);
        }

        $this->info('Done syncing.');
    }
}
