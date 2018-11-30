<?php

namespace MrMYSTIC\CrudWizard\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class WizardModelCommand extends WizardBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wizard:model {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create model';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Model';

    /**
     * Create a new command instance.
     *
     * @param  \Illuminate\Filesystem\Filesystem  $files
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct($files);
    }

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $path = '/stubs/model.stub';
        if (file_exists(resource_path($path))) {
            return resource_path($path);
        }

        return __DIR__ . $path;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        if (config('wizard.models.path') !== 'app') {
            return $rootNamespace . '\\' . str_replace('app/', '', config('wizard.models.path'));
        }
        return $rootNamespace;
    }

    /**
     * Get the destination class path.
     *
     * @param  string  $name
     * @return string
     */
    protected function getPath($name)
    {
        $name = $this->argument('name');
        if (config('wizard.models.path') !== 'app') {
            $path = str_replace('app/', '', config('wizard.models.path'));
            return app_path("{$path}/{$name}.php");
        }
        return app_path("/{$name}.php");
    }
}
