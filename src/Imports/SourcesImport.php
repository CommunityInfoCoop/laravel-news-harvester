<?php

namespace CommunityInfoCoop\NewsHarvester\Imports;

use CommunityInfoCoop\NewsHarvester\Models\Source;
use Illuminate\Console\OutputStyle;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use Maatwebsite\Excel\Concerns\WithUpsertColumns;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class SourcesImport implements ToModel, WithHeadingRow, WithUpserts, WithBatchInserts
{
    use Importable;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row): \Illuminate\Database\Eloquent\Model|Source|null
    {
        return new Source([
            'name' => $row['name'],
            'url' => $row['url'],
            'type' => $row['type'],
        ]);
    }

    public function uniqueBy(): string
    {
        return 'name';
    }

    public function batchSize(): int
    {
        return 50;
    }
}
