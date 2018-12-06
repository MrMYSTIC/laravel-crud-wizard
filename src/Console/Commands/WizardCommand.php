<?php

namespace MrMYSTIC\CrudWizard\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class WizardCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wizard:run {name} {--api}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the CRUD wizard';

    protected $fields = [];

    protected $availableFieldTypes = [
        'bigIncrements' => ['name',],
        'bigInteger' => ['name',],
        'binary' => ['name',],
        'boolean' => ['name',],
        'char' => ['name', 'length',],
        'date' => ['name',],
        'dateTime' => ['name',],
        'dateTimeTz' => ['name',],
        'decimal' => ['name', 'total', 'decimal',],
        'double' => ['name', 'total', 'decimal',],
        'enum' => ['name', 'values',],
        'float' => ['name', 'total', 'decimal',],
        'geometry' => ['name',],
        'geometryCollection' => ['name',],
        'increments' => ['name',],
        'integer' => ['name',],
        'ipAddress' => ['name',],
        'json' => ['name',],
        'jsonb' => ['name',],
        'lineString' => ['name',],
        'longText' => ['name',],
        'macAddress' => ['name',],
        'mediumIncrements' => ['name',],
        'mediumInteger' => ['name',],
        'mediumText' => ['name',],
        'morphs' => ['name',],
        'multiLineString' => ['name',],
        'multiPoint' => ['name',],
        'multiPolygon' => ['name',],
        'nullableMorphs' => ['name',],
        'nullableTimestamps' => [],
        'point' => ['name',],
        'polygon' => ['name',],
        'rememberToken' => ['name',],
        'smallIncrements' => ['name',],
        'smallInteger' => ['name',],
        'softDeletes' => [],
        'softDeletesTz' => [],
        'string' => ['name', 'length',],
        'text' => ['name',],
        'time' => ['name',],
        'timeTz' => ['name',],
        'timestamp' => ['name',],
        'timestampTz' => ['name',],
        'timestamps' => [],
        'timestampsTz' => [],
        'tinyIncrements' => ['name',],
        'tinyInteger' => ['name',],
        'unsignedBigInteger' => ['name',],
        'unsignedDecimal' => ['name', 'total', 'decimal',],
        'unsignedInteger' => ['name',],
        'unsignedMediumInteger' => ['name',],
        'unsignedSmallInteger' => ['name',],
        'unsignedTinyInteger' => ['name',],
        'uuid' => ['name',],
        'year' => ['name',],
    ];

    /**
     * Predefined types
     *
     * @var array
     */
    protected $types = [
        'model'         => false,
        'request'       => false,
        'controller'    => false,
        'migration'     => false,
        'factory'       => false,
        'seeder'        => false,
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        foreach ($this->types as $key => $type) {
            $this->types[$key] = $this->confirm("Would you like to generate {$key}?", true);
        }

        if (!$this->option('api')) {
            $this->types['views'] = $this->confirm('Would you like to generate views?', true);
            $this->types['resource'] = false;
        }

        if ($this->option('api')) {
            $this->types['views'] = false;
            $this->types['resource'] = $this->confirm('Would you like to generate resource?', true);
        }

        if ($this->confirm('Would you like to describe model fields?', true)) {
            while ($field = $this->describeFieldDialog()) {
                $more = $field['more'];
                unset($field['more']);
                $this->fields[] = $field;
                if (!$more) {
                    break;
                }
            }
        }

        $name = $this->argument('name');
        if (!empty($this->fields)) {
            file_put_contents(storage_path("{$name}_fields.json"), json_encode($this->fields));
        }

        if ($this->types['model']) {
            $this->createModel();
        }
        if ($this->types['request']) {
            $this->createRequest();
        }
        if ($this->types['controller']) {
            $this->createController();
        }
        if ($this->types['migration']) {
            $this->createMigration();
        }
        if ($this->types['factory']) {
            $this->createFactory();
        }
        if ($this->types['seeder']) {
            $this->createSeeder();
        }
        if ($this->types['views']) {
            $this->createViews();
        }
        if ($this->types['resource']) {
            $this->createResource();
        }

        unlink(storage_path("{$name}_fields.json"));
    }

    private function describeFieldDialog()
    {
        $field['validation'] = [];
        $field['type'] = $this->choice('What is the type of field?', $this->getFieldTypes());
        foreach ($this->availableFieldTypes[$field['type']] as $parameter) {
            $field[$parameter] = $this->ask("What is the {$parameter} of field?");
        }
        $field['fillable'] = $this->confirm('Is the field is fillable?', true);
        if ($this->confirm('Do you want to supply validation criteria for the field?', true)) {
            while ($choose = $this->choice('What is the type of field?', $this->getValidationRules())) {
                if ($choose == 'stop validation choosing') {
                    break;
                }
                $field['validation'][] = $choose;
            }
        }

        $field['more'] = $this->confirm('Do you want to describe one more field?', true);

        return $field;
    }

    private function getFieldTypes()
    {
        return array_keys($this->availableFieldTypes);
    }

    private function getValidationRules()
    {
        return [
            'stop validation choosing',
            'accepted',
            'active_url',
            'after:date',
            'after_or_equal:date',
            'alpha',
            'alpha_dash',
            'alpha_num',
            'array',
            'bail',
            'before:date',
            'before_or_equal:date',
            'between:min,max',
            'boolean',
            'confirmed',
            'date',
            'date_equals:date',
            'date_format:format',
            'different:field',
            'digits:value',
            'digits_between:min,max',
            'dimensions',
            'distinct',
            'email',
            'exists:table,column',
            'file',
            'filled',
            'gt:field',
            'gte:field',
            'image',
            'in:foo,bar,...',
            'in_array:anotherfield.*',
            'integer',
            'ip',
            'ipv4',
            'ipv6',
            'json',
            'lt:field',
            'lte:field',
            'max:value',
            'mimetypes:text/plain,...',
            'mimes:foo,bar,...',
            'min:value',
            'not_in:foo,bar,...',
            'not_regex:pattern',
            'nullable',
            'numeric',
            'present',
            'regex:pattern',
            'required',
            'required_if:anotherfield,value,...',
            'required_unless:anotherfield,value,...',
            'required_with:foo,bar,...',
            'required_with_all:foo,bar,...',
            'required_without:foo,bar,...',
            'required_without_all:foo,bar,...',
            'same:field',
            'size:value',
            'starts_with:foo,bar,...',
            'string',
            'timezone',
            'unique:table,column,except,idColumn',
            'url',
            'uuid',
        ];
    }

    /**
     * Run create resource command
     *
     * @return void
     */
    protected function createResource()
    {
        $this->call('wizard:resource', [
            'name' => $this->argument('name'),
        ]);
    }

    /**
     * Run create views command
     *
     * @return void
     */
    protected function createViews()
    {
        foreach(['create', 'edit', 'index', 'show', 'partials/form'] as $view) {
            $this->call('wizard:view', [
                'name' => $this->argument('name'),
                '--view' => $view,
            ]);
        }
    }

    /**
     * Run create model command
     *
     * @return void
     */
    protected function createModel()
    {
        $this->call('wizard:model', [
            'name' => $this->argument('name'),
        ]);
    }

    /**
     * Run create migration command
     *
     * @return void
     */
    protected function createMigration()
    {
        $name = $this->argument('name');

        $this->call('wizard:migration', [
            'name' => $name,
        ]);
    }

    /**
     * Run create controller command
     *
     * @return void
     */
    protected function createController()
    {
        $params = [
            'name' => $this->argument('name'),
        ];

        if ($this->option('api')) {
            $params['--api'] = true;
        }

        $this->call('wizard:controller', $params);
    }

    /**
     * Run create request command
     *
     * @return void
     */
    protected function createRequest()
    {
        $params = [
            'name' => $this->argument('name'),
        ];

        $this->call('wizard:request', $params);
    }

    /**
     * Run create factory command
     *
     * @return void
     */
    protected function createFactory()
    {

        $this->call('wizard:factory', [
            'name' => $this->argument('name'),
        ]);
    }

    /**
     * Run create seeder command
     *
     * @return void
     */
    protected function createSeeder()
    {
        $this->call('wizard:seeder', [
            'name' => $this->argument('name'),
        ]);
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['api', null, InputOption::VALUE_NONE, 'Exclude the create and edit methods from the controller.'],
        ];
    }
}
