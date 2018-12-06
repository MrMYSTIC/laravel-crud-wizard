<?php

namespace MrMYSTIC\CrudWizard\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Illuminate\Support\Composer;
use Illuminate\Database\Schema\Blueprint;

class WizardMigrationCommand extends WizardBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wizard:migration {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create mirgation';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Migration';

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @param  \Illuminate\Support\Composer  $composer
     * @return void
     */
    public function __construct(Filesystem $files, Composer $composer)
    {
        parent::__construct($files);

        $this->composer = $composer;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        parent::handle();

        $this->composer->dumpAutoloads();
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $path = '/stubs/migration.stub';
        if (file_exists(resource_path($path))) {
            return resource_path($path);
        }

        return __DIR__ . $path;
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = Str::lower(Str::plural($this->argument('name')));
        $prefix = $this->getDatePrefix();

        return $this->laravel->basePath() . "/database/migrations/{$prefix}_create_{$name}_table.php";
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {
        $table = Str::lower(Str::plural($this->argument('name')));
        return array_merge($replace, [
            'DummyTable' => $table,
//            'DummyClass' => $this->getClassName("create_{$table}_table"),
            'DummyFields' => $this->getTableFields(),
        ]);
    }

    /**
     * Replace the class name for the given stub.
     *
     * @param  string  $stub
     * @param  string  $name
     * @return string
     */
    protected function replaceClass($stub, $name)
    {
        $class = $this->getClassName($this->argument('name'));

        return str_replace('DummyClass', $class, $stub);
    }

    /**
     * Get the class name of a migration name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getClassName($name)
    {
        $name = Str::plural($name);
        return "Create{$name}Table";
    }

    protected function getTableFields()
    {
        $fields = [];
        foreach ($this->fields as $field) {
            $ident = count($fields) > 0
                ? str_pad('', 12, ' ', STR_PAD_LEFT)
                : '';

            $args = ["'{$field['name']}'"];
            $method = new \ReflectionMethod(Blueprint::class, $field['type']);
            if ($method->getNumberOfParameters() > 1) {
                $parameters =  $method->getParameters();
                foreach ($parameters as $parameter) {
                    if ($parameter->getPosition() == 0) {
                        continue;
                    }

                    if (!empty($field[$parameter->getName()])) {
                        $args[] = $field[$parameter->getName()];
                    }
                }
            }

            $args = implode(', ', $args);

            $fields[] = "{$ident}\$table->{$field['type']}({$args})";
        }

        if (empty($fields)) {
            return '//';
        }

        return implode(";\n", $fields);
    }

    /**
     * Get the date prefix for the migration.
     *
     * @return string
     */
    private function getDatePrefix()
    {
        return date('Y_m_d_His');
    }
}
