<?php

namespace GGuney\RSeeder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeReverseSeeder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:reverseSeeder {table_name} {--from=} {--by=} {--except=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cretes seeder from a given table.';

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
        $seedsPath = 'seeders/';

        $tableName = lcfirst($this->argument('table_name'));
        $seederName = Str::studly($tableName) . 'TableSeeder';
        $seederVariableName = Str::camel($tableName);

        $columns = $this->setColumns($tableName);
        $rows = $this->getRows($tableName);
        $string = $this->rowsToString($tableName, $rows, $columns);
        $txt = $this->replaceStub($seederName, $seederVariableName, $tableName, $string);
        $this->saveFile($seedsPath, $seederName, $txt);
    }

    /**
     * Get table column names.
     *
     * @param string $tableName
     * @return array
     */
    private function getColumns($tableName)
    {
        return \DB::connection()
            ->getSchemaBuilder()
            ->getColumnListing($tableName);
    }

    /**
     * Set table columns.
     *
     * @param string $tableName
     * @return array
     */
    private function setColumns($tableName)
    {
        $excepts = $this->option('except');
        $tmpColumns = $this->getColumns($tableName);
        if (isset($excepts)) {
            $excepColumnsArray = explode(',', $excepts);
            foreach ($tmpColumns as $tmpColumn) {
                if ((!in_array($tmpColumn, $excepColumnsArray))) {
                    $columns[] = $tmpColumn;
                }
            }
        } else {
            $columns = $tmpColumns;
        }

        return $columns;
    }

    /**
     * Get rows from a table name.
     *
     * @param string $tableName
     * @return array
     */
    private function getRows($tableName)
    {
        $fromColumn = $this->option('by');
        $fromDate = $this->option('from');
        if (isset($fromDate) && isset($fromColumn)) {
            $rows = \DB::table($tableName)
                ->where($fromColumn, '>', $fromDate)
                ->get();
            return $rows;
        } else {
            $rows = \DB::table($tableName)
                ->get();
            return $rows;
        }
    }

    /**
     * DB Rows to array string.
     *
     * @param string $tableName
     * @param array $rows
     * @param array $columns
     * @return string
     */
    private function rowsToString($tableName, $rows, $columns)
    {
        $string = "";
        foreach ($rows as $row) {
            $string .= "\n\t\t\t[";
            foreach ($columns as $column) {
                //@todo improve this
                $type = \DB::getSchemaBuilder()->getColumnType($tableName, $column);
                if ($this->isNumeric($type)) {
                    $value = str_replace(",", ".", $row->$column);
                } elseif (!isset($row->$column)) {
                    $value = 'NULL';
                } else {
                    $value = "'" . str_replace("'", "\'", $row->$column) . "'";
                }
                $string .= "'$column' => " . $value . ", ";
            }
            $string = rtrim($string, ', ');
            $string .= "],";
        }

        return $string;
    }

    /**
     * Check column if is numeric
     *
     * @see https://www.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/types.html#detection-of-database-types
     * @param string $type
     * @return boolean
     */
    private function isNumeric(string $type)
    {
        $numericDataType = [
            'smallint',
            'integer',
            'bigint',
            'decimal',
            'float'
        ];
        if (in_array($type, $numericDataType)) {
            return true;
        }
        return false;
    }

    /**
     * Get stub.
     *
     * @return string
     */
    private function getStub()
    {
        $txt = file_get_contents(__DIR__ . '/SeederStub.stub') or die("Unable to open file!");
        return $txt;
    }

    /**
     * Replace stub with variables.
     *
     * @param string $stub
     * @param string $seederName
     * @param string $seederVariableName
     * @param string $tableName
     * @param $string
     */
    private function replaceStub($seederName, $seederVariableName, $tableName, $string)
    {
        $stub = $this->getStub();
        $stub = str_replace('{SEEDER_NAME}', $seederName, $stub);
        $stub = str_replace('{SEEDER_VARIABLE_NAME}', $seederVariableName, $stub);
        $stub = str_replace('{TABLE_NAME}', $tableName, $stub);
        $stub = str_replace('{ARRAY}', $string, $stub);
        return $stub;
    }

    /**
     * Save replaced stub as seeder.
     *
     * @param string $seedsPath
     * @param string $seederName
     * @param string $txt
     */
    private function saveFile($seedsPath, $seederName, $txt)
    {
        $path = database_path($seedsPath . $seederName . '.php');
        $file = fopen($path, "w") or die("Unable to open file!");
        fwrite($file, $txt);
        fclose($file);
        $this->info($seederName . ' named seeder created in seeds folder.');
    }
}
