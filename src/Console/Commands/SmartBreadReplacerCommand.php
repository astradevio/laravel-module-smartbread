<?php
/**
 * SmartBreadReplacer
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

use astradevio\LaravelModuleSmartBread\Services\SmartBreadServices;
use astradevio\LaravelModuleSmartBread\Services\SmartBreadRouteFileParser;

class SmartBreadReplacerCommand extends Command
{
    protected $signature = 'smartbread:replace {item}';
    protected $description = 'Replace items [views] files from a template on a module';
    protected string $moduleName = '';      // Name of module
    protected string $modelName = '';       // Name of model
    protected string $tableName = '';       // Name of table on model
    protected string $template = '';        // Chosen template
    protected string $templatePath = '';    // Path to template directory e.g: app_path() . /stubs/module-generator/$template
    protected string $tempPath = '';        // Path to temporary folder e.g: app_path() . /generator-temp
    protected string $modulePath = '';      // Path to module directory e.g: app_path() . /Modules/$moduleName
    protected array $replacements = [];     // Replacements for template files

    protected SymfonyFilesystem  $filesystem;

    public function handle(): bool
    {

        $service = new SmartBreadService();

        if ($item == 'view') {
            $folder = 'resources/views';
        } else {
            $service->exit_error("Choose a valid item to replace. Valid option is 'view'.");
        }

        $service->exit_success('View files replaced successfully.');

        /** 
         * Setup templates 
         */     
        $this->template = $service->loadTemplate();
        $this->templatePath = base_path($this->template);
        $this->tempPath     = base_path('.tmp'. Str::random(10));

        if (!file_exists($this->templatePath)) {
            $this->exit_fail("$this->templatePath Path does not exist! Please check your config/module-generator.php file.");
        }

        /**
         * Setup module's name in PascalCase
         */
        $modulePath = config('modules.paths.modules').'/';
        $this->modulePath = Str::endsWith($modulePath, '/') ? $modulePath : $modulePath.'/';

        $this->moduleName = $service->loadModuleName();

        if (! file_exists($this->modulePath.$this->moduleName)) {
            $service->exit_fail("$this->moduleName module does not exist. What do you want to do?");
        }

        /**
         * Setup model's name in PascalCase
         */
        $this->modelName = $service->loadModelName();

        if ($this->modelExists($this->modelName)) {
            service->exit_fail("$this->moduleName / $this->modelName exists.");
        }

        /**
         * Setup table name in snake_case
         */

        $this->tableName = $service->setTableName();
      

        /**
         * Generate models 
         */
        $this->generate();

        $this->newLine();
        $service->exit_success("Bread template $this->modelName on Module $this->moduleName created successfully.");
    }

    
    protected function generate(): void
    {
        //ensure directory does not exist

        info('Creating temporary directory of stub files.');
        $service->delete($this->tempPath);
        $service->mirror($this->templatePath, $this->tempPath.'/Module');

        info('Writing template files from stubs.');
        $finder = new Finder();
        $finder->files()->in($this->tempPath);
        
        $progressBar = $this->output->createProgressBar(iterator_count($finder));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        
        foreach ($finder as $file) {

            // load stub and template file information
            $stub_file = new SplFileInfo($file);
            $template_file = new SplFileInfo($service->stubFilename($stub_file));

            $progressBar->setMessage("Processing " . $template_file->getFilename() . ".");
            
            //!!
            // ignores files from config
            if (in_array($template_file, config($service->configFile . '.ignore_files'), true)) {
                continue;
            }

            // replace content from stub on template
            $service->createFileFromStub($stub_file, $template_file);

            // move templates to destination

            // routes
            if (Str::endsWith($template_file, config($service->configFile . '.routes_pathnames'))) {

                $target_route_pathname = $this->getTargetPathname($template_file);
                if (!File::exists($target_route_pathname)) {
                    info('Route file does not exist. Creating new route file.'); //$this
                    $service->createTargetFile($template_file);                    
                    continue;   
                }

                info("Route " . $template_file->getFilename() . " exist. Merging route file."); // $this

                $template_routefile = new LaravelModuleSmartBreadRouteFileParser($template_file);
                $target_routefile = new LaravelModuleSmartBreadRouteFileParser($target_route_pathname);

                $target_routefile->mergeUses($template_routefile->getUses());
                $target_routefile->mergeBody($template_routefile->getBody());
                $target_routefile->save();

                continue;
            }



            $service->createTargetFile($template_file);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        
    }

}