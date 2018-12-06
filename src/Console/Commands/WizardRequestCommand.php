<?php

namespace MrMYSTIC\CrudWizard\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class WizardRequestCommand extends WizardBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wizard:request {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create request';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Request';

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
        $filename = 'request.stub';
        if (file_exists(resource_path('/stubs/' . $filename))) {
            return resource_path('/stubs/' . $filename);
        }

        return __DIR__ . '/stubs/' . $filename;
    }

    /**
     * Get the full namespace for a given class, without the class name.
     *
     * @param  string  $name
     * @return string
     */
    protected function getNamespace($name)
    {
        return 'App\\Http\\Requests';
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
        $class = str_replace($this->getNamespace($name).'\\', '', $name);

        return str_replace('DummyClass', $class, $stub);
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        return $name . 'Request';
    }

    /**
     * Build the model replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildModelReplacements(array $replace)
    {
        return array_merge($replace, [
            'DummyRules' => $this->getRules(),
        ]);
    }

    /**
     * @return string
     */
    private function getRules()
    {
        $rules = [];
        foreach ($this->fields as $field) {

            $ident = count($rules) > 0
                ? str_pad('', 12, ' ', STR_PAD_LEFT)
                : '';
            $rules[] = "{$ident}'{$field['name']}' => '" . implode('|', $field['validation']) . "'";
        }

        if (empty($rules)) {
            return '//';
        }

        return implode(",\n", $rules);
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

        return app_path("/Http/Requests/{$name}Request.php");
    }
}
