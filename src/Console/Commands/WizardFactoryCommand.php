<?php

namespace MrMYSTIC\CrudWizard\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Filesystem\Filesystem;
use Faker\Generator as Faker;

class WizardFactoryCommand extends WizardBaseCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wizard:factory {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create factory';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Factory';

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
        $path = "/stubs/factory.stub";
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
        return $this->laravel->databasePath() . "/factories/{$name}.php";
    }

    /**
     * Parse the class name and format according to the root namespace.
     *
     * @param  string  $name
     * @return string
     */
    protected function qualifyClass($name)
    {
        return $name . 'Factory';
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
            'DummyFieldList' => $this->getFieldList(),
        ]);
    }

    protected function getFieldList()
    {
        $fieldList = [];
        foreach ($this->fields as $field) {
            $fakerMapped = $this->mapFakerMethod($field['name'], $field['type']);
            $comment = $fakerMapped === null ? '//' : '';
            $ident = count($fieldList) > 0
                ? str_pad('', 8, ' ', STR_PAD_LEFT)
                : '';
            $fieldList[] = "{$comment}{$ident}'{$field['name']}' => \$faker->{$fakerMapped}";
        }

        if (empty($fieldList)) {
            return '//';
        }

        return implode(",\n", $fieldList);
    }

    //TODO Add more logic here...
    private function mapFakerMethod($name, $type)
    {
        //check if name or type of field is property or method of $faker (name, addres, email etc)
        $class = new \ReflectionClass(Faker::class);

        try {
            if ($class->getProperty($name)) {
                return $name;
            }
        } catch (\ReflectionException $e) {}

        try {
            if($class->getProperty($type)) {
                return $type;
            }
        } catch (\ReflectionException $e) {}


        if (str_contains($name, '_at') && in_array($type, ['dateTime'])) {
            return 'dateTime';
        }

        return null;
    }
}
