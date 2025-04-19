<?php
/**
 * ModuleSmartBreadGenerator
 * Create starter bread template on a Laravel Module
 * 
 * See: http://www.laravelmodule.com
 * 
 * @package astradev\ModuleSmartBreadGenerator
 * @author  Leandro Neves <leandro@astradev.io>
 * @license MIT
 * 
 * This work is based on rewrite on a work of David Carr. 
 * 
 * See: 
 * dcblogdev/laravel-module-generator
 * https://github.com/dcblogdev/laravel-module-generator
 * 
 */
namespace astradevio\LaravelModuleSmartBread\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\Finder;
use SplFileInfo;

use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\select;
use function Laravel\Prompts\text;

use \astradevio\LaravelModuleSmartBread\Services\SmartBreadService;
use \astradevio\LaravelModuleSmartBread\Services\SmartBreadRouteFileParser;


class SmartBreadGeneratorCommand extends Command
{
    protected $signature = 'smartbread:generate {template?} {module?} {model?} {table?}';
    protected $description = 'Create starter module from a template.';

    public SmartBreadService $service;

    public function handle(): bool
    {

        $this->service = new SmartBreadService();

        // Template
        $result = $this->service->loadTemplate($this->argument('template'));

        if (!file_exists($this->service->templatePath)) {
            $this->service->exit_fail("$this->service->templatePath Path does not exist! Please check your config/module-generator.php file.");
        }

        $this->service->loadModuleName($this->argument('module'));

        if (!file_exists($this->service->modulePath . $this->service->moduleName)) {
            $this->service->exit_fail($this->service->modulePath . $this->service->moduleName . " module does not exists.");
        }

        /**
         * Setup model's name in PascalCase
         */
        $this->service->loadModelName($this->argument('model'), $error_on_exist = true);

        if ($this->service->modelExists($this->service->modelName) ||
            $this->service->controllerExists($this->service->modelName)) {
            $this->service->exit_fail('Error: ' . $this->service->moduleName . "/" . $this->service->modelName . " exists.");
        }

        /**
         * Setup table name in snake_case
         */

        $this->service->loadTableName($this->argument('table'));

        /**
         * Load replacements in memory
         */
        $this->service->loadReplacements();

        /**
         * Generate models 
         */
        $this->generate();

        $this->newLine();
        $this->service->exit_success('Bread template ' . $this->service->modelName . ' on Module ' . $this->service->moduleName . 'created successfully.');
    }

    
    protected function generate(): void
    {
        //ensure directory does not exist

        $this->service = $this->service;

        info('Creating temporary directory of stub files.');
        $this->service->delete($this->service->tempPath);

        $this->service->mirror($this->service->templatePath, $this->service->tempPath.'/Module');

        info('Writing template files from stubs.');
        $finder = new Finder();
        $finder->files()->in($this->service->tempPath);
        
        $progressBar = $this->output->createProgressBar(iterator_count($finder));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');

        
        foreach ($finder as $file) {

            // load stub and template file information
            $stub_file = new SplFileInfo($file);
            $template_file = new SplFileInfo($this->service->stubFilename($stub_file));

            $progressBar->setMessage("Processing " . $template_file->getFilename() . ".");
            
            //!!
            // ignores files from config
            if (in_array($template_file, config($this->service->configFile . '.ignore_files'), true)) {
                continue;
            }

            // replace content from stub on template
            $this->service->createFileFromStub($stub_file, $template_file);

            // move templates to destination

            // routes
            if (Str::endsWith($template_file, config($this->service->configFile . '.routes_pathnames'))) {

                $target_route_pathname = $this->service->getTargetPathname($template_file);
                if (!File::exists($target_route_pathname)) {
                    info('Route file does not exist. Creating new route file.');
                    $this->service->createTargetFile($template_file);                    
                    continue;   
                }

                info("Route " . $template_file->getFilename() . " exist. Merging route file."); 

                $template_routefile = new SmartBreadRouteFileParser($template_file);
                $target_routefile = new SmartBreadRouteFileParser($target_route_pathname);

                $target_routefile->mergeUses($template_routefile->getUses());
                $target_routefile->mergeBody($template_routefile->getBody());
                $target_routefile->save();

                continue;
            }



            $this->service->createTargetFile($template_file);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        
    }

}