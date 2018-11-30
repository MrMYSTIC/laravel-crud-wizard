<?php

namespace MrMYSTIC\CrudWizard;

use MrMYSTIC\CrudWizard\Console\Commands\WizardControllerCommand;
use MrMYSTIC\CrudWizard\Console\Commands\WizardFactoryCommand;
use MrMYSTIC\CrudWizard\Console\Commands\WizardModelCommand;
use MrMYSTIC\CrudWizard\Console\Commands\WizardResourceCommand;
use MrMYSTIC\CrudWizard\Console\Commands\WizardSeederCommand;
use MrMYSTIC\CrudWizard\Console\Commands\WizardViewCommand;
use MrMYSTIC\CrudWizard\Console\Commands\WizardCommand;
use Illuminate\Support\ServiceProvider;

class CrudWizardServiceProvider extends ServiceProvider {

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot() {
        $this->commands(
            [
                WizardCommand::class,
                WizardResourceCommand::class,
                WizardFactoryCommand::class,
                WizardViewCommand::class,
                WizardSeederCommand::class,
                WizardControllerCommand::class,
                WizardModelCommand::class,
            ]
        );

        $this->mergeConfigFrom(
            __DIR__.'/config/wizard.php', 'wizard'
        );

        $this->publishes([
            __DIR__.'/config/wizard.php' => config_path('wizard.php')
        ], 'config');

        $this->publishes([
            __DIR__.'/Console/Commands/stubs' => resource_path('stubs')
        ], 'stub');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {

    }
}