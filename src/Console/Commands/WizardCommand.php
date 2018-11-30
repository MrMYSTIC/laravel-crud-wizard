<?php

namespace MrMYSTIC\CrudWizard\Console\Commands;

use Illuminate\Console\Command;
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

    /**
     * Predefined types
     *
     * @var array
     */
    protected $types = [
        'model'         => false,
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

        if ($this->types['model']) {
            $this->createModel();
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
        $name = Str::lower(Str::plural($this->argument('name')));

        $this->call('make:migration', [
            'name' => "create_{$name}_table",
            '--create' => $name,
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
