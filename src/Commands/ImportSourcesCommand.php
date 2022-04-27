<?php

namespace CommunityInfoCoop\NewsHarvester\Commands;

use CommunityInfoCoop\NewsHarvester\Imports\SourcesImport;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Excel;

class ImportSourcesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsharvest:import-sources {import_file : the CSV import file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Sources from a CSV file';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $filename = $this->argument('import_file');

        if (! file_exists($filename) || ! is_readable($filename)) {
            $this->error('Import file is not readable.');
            return Command::FAILURE;
        }

        $this->output->title('Starting import');
        (new SourcesImport)->withOutput($this->output)->import($filename, null, Excel::CSV);
        $this->output->success('Import successful');
    }
}
