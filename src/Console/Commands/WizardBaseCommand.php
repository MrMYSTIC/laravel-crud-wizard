<?php

namespace MrMYSTIC\CrudWizard\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

abstract class WizardBaseCommand extends GeneratorCommand
{
    protected $fields = [];

    /**
     * Execute the console command.
     *
     * @return bool|null
     */
    public function handle()
    {
        $this->fields = $this->getFields();
        return parent::handle();
    }

    protected function getFields()
    {
        $name = $this->argument('name');
        $fileName = storage_path("{$name}_fields.json");
        if (!file_exists($fileName)) {
            return [];
        }
        return json_decode(file_get_contents(storage_path("{$name}_fields.json")), true);
    }

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
     * Get the fully-qualified model class name.
     *
     * @param  string  $model
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    protected function parseModel($model)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $model)) {
            throw new InvalidArgumentException('Model name contains invalid characters.');
        }

        $model = trim(str_replace('/', '\\', $model), '\\');

        if (config('wizard.models.path') !== 'app') {
            $model = str_replace('app/', '', config('wizard.models.path')) . '\\' . $model;
        }

        if (!Str::startsWith($model, $rootNamespace = $this->laravel->getNamespace())) {
            $model = $rootNamespace.$model;
        }

        return $model;
    }

    /**
     * Build the class with the given name.
     *
     * Remove the base controller import if we are already in base namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $replace = [];

        $replace = $this->buildModelReplacements($replace);

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
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
            'DummyClass' => class_basename($modelClass),
        ]);
    }
}
