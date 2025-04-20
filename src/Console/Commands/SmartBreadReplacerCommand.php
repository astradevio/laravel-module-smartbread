<?php
/**
 * SmartBreadReplacer
 * Create starter bread template on a Laravel Module
 * 
 * See: http://www.laravelmodule.com
 * 
 * @package astradev\ModuleSmartBread
 * @author  Leandro Neves <leandro@astradev.io>
 * @license MIT
 * 
 * This work is based on rewrite on a ideas of David Carr. 
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


class SmartBreadReplacerCommand extends Command
{
    protected $signature = 'smartbread:replace {option} {template?} {module?} {model?}';
    protected $description = 'Relapce [view] files from a template.';
    private $subPath = '';

    public SmartBreadService $service;

    public function handle(): bool
    {
        if ($this->argument('option') !== 'view') {
            $this->service->exit_fail('Error: option not supported.');
        } else {
            $this->subPath = 'resources/views';
        }

        $this->service = new SmartBreadService();

        // Template
        $result = $this->service->loadTemplate($this->argument('template'));

        if (!file_exists($this->service->templatePath)) {
            $this->service->exit_fail("$this->service->templatePath Path does not exist! Please check your config/module-generator.php file.");
        }

        $this->moduleName = $this->service->loadModuleName($this->argument('module'));

        if (!file_exists($this->service->modulePath . $this->service->moduleName)) {
            $this->service->exit_fail($this->service->modulePath . $this->service->moduleName . " module does not exists.");
        }

        /**
         * Setup model's name in PascalCase
         */
        $this->service->loadModelName($this->argument('model'), $invertResult = true);

        if (! $this->service->modelExists($this->service->modelName)) {
            $this->service->exit_fail('Error: ' . $this->service->moduleName . "/" . $this->service->modelName . "does not exists.");
        }

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
        info('Creating temporary directory of stub files.');
        $this->service->delete($this->service->tempPath);

        $this->templatePath = $this->service->templatePath . '/' . $this->subPath;
        $this->tempPath = $this->service->tempPath;

        $this->service->mirror($this->templatePath, $this->tempPath. '/'. $this->subPath);

        info('Writing template files from stubs.');
        $finder = new Finder();

        $finder->files()->in($this->tempPath . '/'. $this->subPath);
        
        $progressBar = $this->output->createProgressBar(iterator_count($finder));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');

        $modulePath = config('modules.paths.modules').'/';
        $this->modulePath = Str::endsWith($modulePath, '/') ? $modulePath : $modulePath.'/';
       
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

            // Create target file
            $this->createTargetFile($template_file);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        
    }

    // override
    public function getTargetPathname(SplFileInfo $template): string 
    {
        $template_relative_pathname = substr($template->getPathname(), strlen($this->tempPath . '/'));           
        $template_relative_path = dirname($template_relative_pathname);

        $target_path = $this->modulePath . $this->moduleName . '/' . $template_relative_path;
        $target_pathname = $this->modulePath . $this->moduleName . '/' . $template_relative_pathname;

        return $target_pathname;
        
    }

    public function createTargetPath(SplFileInfo $template): string
    {
        $target_pathname = $this->getTargetPathname($template);
        $target_path = dirname($target_pathname);

        // create target directory if not exists
        if ( !File::exists($target_path) && 
                ! File::makeDirectory($target_path, $mode = 0755, $recursive = true, $force = false) && 
                ! File::exists($target_path)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $target_path)); 
        }

        return $target_path;
    }

    public function createTargetFile(SplFileInfo $template): void
    {

        $target_pathname = $this->getTargetPathname($template);
        $target_path = $this->createTargetPath($template);

        $this->service->filesystem->copy($template->getPathname(), $target_pathname);

    }

}
