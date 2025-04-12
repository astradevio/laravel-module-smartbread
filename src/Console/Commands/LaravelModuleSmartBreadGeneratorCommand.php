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

 namespace astradevio\LaravelModuleSmartBreadGenerator\Console\Commands;

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

class LaravelModuleSmartBreadGeneratorCommand extends Command
{
    protected $signature = 'module:smartbread {template?} {module?} {model?} {table?}';
    protected $description = 'Create starter module from a template';
    protected string $moduleName = '';      // Name of module
    protected string $modelName = '';       // Name of model
    protected string $tableName = '';       // Name of table on model
    protected string $template = '';        // Chosen template
    protected string $templatePath = '';    // Path to template directory e.g: app_path() . /stubs/module-generator/$template
    protected string $tempPath = '';        // Path to temporary folder e.g: app_path() . /generator-temp
    protected string $modulePath = '';      // Path to module directory e.g: app_path() . /Modules/$moduleName
    protected array $replacements = [];     // Replacements for template files

    protected SymfonyFilesystem  $filesystem;

    protected string $configFile = 'module-smartbread';

    private function exit_fail(string $message): int
    {
        error($message);
        $this->filesystem->remove($this->tempPath);
        exit (Command::FAILURE);
    }

    private function exit_success(string $message): int
    {
        info($message);
        $this->filesystem->remove($this->tempPath);
        exit (Command::SUCCESS);
    }

    public function handle(): bool
    {
        /* 
         * Setup filesystem
         */
        $this->filesystem = new SymfonyFilesystem;

        /* 
         * Setup parameters
         */
        $this->useSingular = config($this->configFile . '.use_singular') === true ? true : false;
        
        /** 
         * Setup templates 
         */     
        $this->getTemplate();
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

        $this->setModuleName();

        if (! file_exists($this->modulePath.$this->moduleName)) {
            $this->exit_fail("$this->moduleName module does not exist.");
        }

        /**
         * Setup model's name in PascalCase
         */
        $this->setModelName();

        if (file_exists($this->modelExists($this->modelName))) {
            $this->exit_fail("$this->moduleName / $this->modelName exists.");
            return Command::FAILURE; 
        }

        /**
         * Setup table name in snake_case
         */

        $this->setTableName();

        /**
         * Load replacements in memory
         */
        $this->loadReplacements();

        /**
         * Generate models 
         */
        $this->generate();

        $this->newLine();
        $this->exit_success("Bread template $this->modelName on Module $this->moduleName created successfully.");
    }

    /**
     * Set the module name
     *
     * @return void
     */
    protected function setModuleName(): void
    {
        $this->moduleName = Str::studly($this->argument('module')) ?? '';

        if ($this->moduleName !== '') {
            return;
        }

        $this->moduleName = Str::studly(
            text(
                label: 'Please enter a name for the module (CamelCase): ',
                required: true,
                validate: fn(string $value) => match (true) {
                    strlen($value) < 1 => 'The name must be at least 1 characters.',
                    Str::contains($value, ' ') => 'The name must not contain spaces.',
                    ! file_exists($this->modulePath . Str::studly($value)) => "Module does not exist. " . $this->modulePath,
                    default => null
                }
            )
        );
    }

    /**
     * Set the model name
     *
     * @return void
     */
    protected function setModelName(): void
    {
        $this->modelName = Str::studly($this->argument('model')) ?? '';

        if ($this->modelName !== '') {
            return;
        }

        $this->modelName = Str::studly(
            text(
                label: 'Please enter a name for the model bread (CamelCase): ',
                required: true,
                validate: fn(string $value) => match (true) {
                    strlen($value) < 1 => 'The name must be at least 1 characters.',
                    Str::contains($value, ' ') => 'The name must not contain spaces.',
                    $this->modelExists(Str::studly($value)) => 'Model already exists.',
                    default => null
                }
            )
        );
    }

    /**
     * Get template option
     *
     * @return void
     */
    protected function getTemplate(): void
    {
        $template = $this->argument('template') ?? '';
        $templateConfig = config($this->configFile . '.templates');

        if ($template !== '') {
            if (in_array($template, array_keys($templateConfig))) {
                $template = $templateConfig[$template];
            } else {
                error("Invalid template option: $template");
                $template = '';
            }
        }

        if ($template === '') {
            $template = select(
                'Which template would you like to use?',
                array_keys($templateConfig)
            );
            $template = $templateConfig[$template];
        }

        $this->template = $template;
    }

    /**
     * Get the model's table name  
     */
    protected function setTableName(): void
    {
        $this->tableName = Str::snake($this->argument('table')) ?? '';

        if ($this->tableName !== '') {
            if (config($this->configFile . '.append_module_to_tablename') === true) {
                $this->tableName = Str::snake($this->moduleName) . '_' . $this->tableName;
            }
            return;
        }

        $this->tableName = Str::snake(Str::studly(
            text(
                label: 'Please enter a name for the table bread (snake_case): ',
                required: true,
                validate: fn(string $value) => match (true) {
                    strlen($value) < 1 => 'The name must be at least 1 characters.',
                    Str::contains($value, ' ') => 'The name must not contain spaces.',
                    default => null,
                },
                //default: $this->modelName,
            )
        ));

        if (config($this->configFile . '.append_module_to_tablename') === true) {
            $this->tableName = Str::snake($this->moduleName) . '_' . $this->tableName;
        }
    }

    /**
     * Check if the model exists
     *
     * @param string $model_name
     * @return bool
     */
    protected function modelExists($model_name): bool
    {   
        $model_file = $this->modulePath . $this->moduleName . '/app/Model/' . $model_name . '.php';
        return file_exists($model_file);
    }

    protected function delete($path): void
    {
        if (file_exists($path)) {
            $this->filesystem->remove($path);
        }
    }

    protected function mirror($source, $destination): void
    {
        $this->filesystem->mirror($source, $destination);
    }

    protected function generate(): void
    {
        //ensure directory does not exist

        info('Creating temporary directory of stub files.');
        $this->delete($this->tempPath);
        $this->mirror($this->templatePath, $this->tempPath.'/Module');

        info('Writing template files from stubs.');
        $finder = new Finder();
        $finder->files()->in($this->tempPath);
        
        $progressBar = $this->output->createProgressBar(iterator_count($finder));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        
        foreach ($finder as $file) {

            // load stub and template file information
            $stub_file = new SplFileInfo($file);
            $template_file = new SplFileInfo($this->stubFilename($stub_file));

            $progressBar->setMessage("Processing " . $template_file->getFilename() . ".");
            
            // ignores files from config
            if (in_array($template_file, config($this->configFile . '.ignore_files'), true)) {
                continue;
            }

            // replace content from stub on template
            $this->createFileFromStub($stub_file, $template_file);

            // move templates to destination

            // routes
            if (Str::endsWith($template_file, config($this->configFile . '.routes_pathnames'))) {

                $target_route_pathname = $this->getTargetPathname($template_file);
                if (!File::exists($target_route_pathname)) {
                    $this->info('Route file does not exist. Creating new route file.');
                    $this->createTargetFile($template_file);                    
                    continue;   
                }

                $this->info("Route " . $template_file->getFilename() . " exist. Merging route file.");

                $template_routefile = new RouteFileParser($template_file);
                $target_routefile = new RouteFileParser($target_route_pathname);

                $target_routefile->mergeUses($template_routefile->getUses());
                $target_routefile->mergeBody($template_routefile->getBody());
                $target_routefile->save();

                continue;
            }

            $this->createTargetFile($template_file);

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine();
        
    }

    protected function getTargetPathname(SplFileInfo $template): string 
    {
        $template_relative_pathname = substr($template->getPathname(), strlen($this->tempPath . '/Module/'));           
        $template_relative_path = dirname($template_relative_pathname);

        $target_path = $this->modulePath . $this->moduleName . '/' . $template_relative_path;
        $target_pathname = $this->modulePath . $this->moduleName . '/' . $template_relative_pathname;

        return $target_pathname;
        
    }
    protected function createTargetPath(SplFileInfo $template): string
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
    
    /**
     * Create a target file from a template
     *
     * @param SplFileInfo $template
     * @return void
     */

    protected function createTargetFile(SplFileInfo $template): void
    {

        $target_pathname = $this->getTargetPathname($template);
        $target_path = $this->createTargetPath($template);

        $this->filesystem->copy($template->getPathname(), $target_pathname);

    }

    protected function appendTimestamp($sourceFile): array|string
    {
        $timestamp = date('Y_m_d_his_');
        $file = basename($sourceFile);

        return str_replace($file, $timestamp.$file, $sourceFile);
    }

    /**
     * Create a file from a stub
     *
     * @param SplFileInfo $source_file
     * @param SplFileInfo $destination_file
     * @return void
     */
    protected function createFileFromStub(SplFileInfo $source_file, SplFileInfo $destination_file): void
    {

        $destination_path = $destination_file->getPath();

        if ( ! File::exists($destination_path) && 
             ! File::makeDirectory($destination_path, $mode = 0755, $recursive = false, $force = false) && 
             ! File::exists($destination_path)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $destination_path)); 
        }

        File::put($destination_file->getPathname(), $this->replaceContent(File::get($source_file->getPathname())));
    }

    /**
     * Replace content of a string
     *
     * @param string $content
     * @return string
     */

    protected function replaceContent(string $content): string    {
        return str_replace(array_keys($this->replacements), array_values($this->replacements), $content);
    }

    protected function stubFilename(SplFileInfo $sourceFile): string {

        $destinationPath = $this->replaceContent($sourceFile->getPath());
        $destinationFile = $this->replaceContent($sourceFile->getFilename());

        // compatibily with older laravel versions
        $destinationPath = str_replace("Entities", "Models", $destinationPath);

        if (Str::endsWith($destinationPath, ['migrations', 'Migrations'])) {
            // Rename a migration file
            $destinationFile = $this->appendTimestamp($destinationFile);
        }

        return $destinationPath . '/' . $destinationFile;

    }
    

    protected function renamePlaceholders($model, $separator, $arrayMap = null): string
    {
        $parts = preg_split('/(?=[A-Z])/', $model, -1, PREG_SPLIT_NO_EMPTY);

        if ($arrayMap) {
            $parts = array_map('strtolower', $parts);
        }

        return implode($separator, $parts);
    }


    protected function loadReplacements(): void {

        $module = $this->moduleName;
        $model  = $this->modelName;

        $this->replacements = [
            '{Module}' => $module,
            '{Module }' => trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', $module)),
            '{Module-}' => $this->renamePlaceholders($module, '-'),
            '{Module_}' => $this->renamePlaceholders($module, '_'),

            '{module}' => strtolower($module),
            '{module }' => trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', strtolower($module))),
            '{module_}' => $this->renamePlaceholders($module, '_', arrayMap: true),
            '{module-}' => $this->renamePlaceholders($module, '-', arrayMap: true),
            '{module_plural}' => trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', strtolower(Str::plural($module)))),

            '{Model}' => $model,
            '{Model }' => trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', $model)),
            '{Model-}' => $this->renamePlaceholders($model, '-'),
            '{Model_}' => $this->renamePlaceholders($model, '_'),
            '{model_plural}' => trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', strtolower(Str::singular($model)))),

            '{model}' => strtolower($model),
            '{model }' => trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', strtolower($model))),
            '{model_}' => $this->renamePlaceholders($model, '_', arrayMap: true),
            '{model-}' => $this->renamePlaceholders($model, '-', arrayMap: true),

            '{table}' => Str::snake(Str::studly((Str::plural($this->tableName))))

        ];

        if ($this->useSingular) {
            $this->replacements['{module_plural}'] = trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', strtolower(Str::singular($module))));
            $this->replacements['{model_plural}']  = trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', strtolower(Str::singular($model))));
            $this->replacements['{table}'] = Str::snake($this->tableName);
        }

    }
}

class RouteFileParser
{

    protected SplFileInfo $routeFile;
    protected $namespace = '';
    protected $uses = [];
    protected $body = '';

    public function __construct(SplFileInfo|string $routeFile = null) {
        if ($routeFile !== null) {
            $this->parse($routeFile);
        }
    }

    public function parse(SplFileInfo|string $routeFile) {


        if (!file_exists($routeFile)) {
            throw new RuntimeException(sprintf('Route file "%s" does not exist', $routeFile));
        }

        $this->routeFile = new SplFileInfo($routeFile);

        $content = File::get($routeFile);

        // get the namespace
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $this->namespace = trim($matches[1]);
        }

        // get uses
        preg_match_all('/use\s+[^;]+;/', $content, $useMatches);
        $this->uses = $useMatches[0];

        // get body
        $contentWithoutNamespaceAndUses = preg_replace('/^(namespace\s+[^;]+;\s*)|(use\s+[^;]+;\s*)/m', '', $content);

        // Remove a tag <?php se existir
        $contentWithoutNamespaceAndUses = str_replace('<?php', '', $contentWithoutNamespaceAndUses);

        $this->body = trim($contentWithoutNamespaceAndUses);

    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getUses(): array
    {
        return $this->uses;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function mergeUses(array $newUses)
    {
        $this->uses = array_unique(array_merge($this->uses, $newUses));
        sort($this->uses); // opcional: organizar em ordem alfabética
    }

    public function mergeBody(string $newBody)
    {
        $this->body .= "\n" . $newBody;
    }

    public function build(): string
    {
        $namespaceLine = $this->namespace ? "namespace {$this->namespace};\n\n" : '';

        $usesBlock = implode("\n", $this->uses);
        $body = trim($this->body);

        return trim('<?php' . "\n\n" . $namespaceLine . $usesBlock . "\n\n" . $body) . "\n";
    }

    public function saveToFile(string $pathname): void
    {
        File::put($pathname, $this->build());
    }

    public function save(): void
    {
        $this->saveToFile($this->routeFile);
    }
}