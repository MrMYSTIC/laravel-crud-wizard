<?php

namespace MrMYSTIC\CrudWizard\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Composer;
use Illuminate\Support\Str;

class WizardSeederCommand extends WizardBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wizard:seeder {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create seeder';

    /**
     * The Composer instance.
     *
     * @var \Illuminate\Support\Composer
     */
    protected $composer;

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Seeder';

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
        if (file_exists(resource_path('/stubs/seeder.stub'))) {
            return resource_path('/stubs/seeder.stub');
        }

        return __DIR__ . '/stubs/seeder.stub';
    }


    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        return $this->laravel->databasePath() . '/seeds/' . $name . '.php';
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        $name = Str::plural($name);
        return $name . 'TableSeeder';
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {
        $modelClass = $this->parseModel($this->argument('name'));

        return array_merge($replace, [
            'DummyModel' => $modelClass,
            'DummyVariable' => lcfirst(class_basename($modelClass)),
        ]);
    }
}
