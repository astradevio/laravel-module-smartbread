<?php
/**
 * ModuleSmartBreadServices
 * Services form smartbread templates.
 * 
 * See: http://www.laravelmodule.com
 * 
 * @package astradev\ModuleSmartBreadGenerator
 * @author  Leandro Neves <leandro@astradev.io>
 * @license MIT
 * 
 * 
 */
namespace astradevio\LaravelModuleSmartBread\Services;

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

class SmartBreadService {

    public string $moduleName = '';      // Name of module
    public string $modelName = '';       // Name of model
    public string $tableName = '';       // Name of table on model
    public string $template = '';        // Chosen template
    public string $templatePath = '';    // Path to template directory e.g: app_path() . /stubs/module-generator/$template
    public string $tempPath = '';        // Path to temporary folder e.g: app_path() . /generator-temp
    public string $modulePath = '';      // Path to module directory e.g: app_path() . /Modules/$moduleName
    public array $replacements = [];     // Replacements for template files

    public bool $useSingular = false;    // Use singular for table name

    public Command $command; // Command instance ???

    public SymfonyFilesystem  $filesystem;

    public string $configFile = 'smartbread';

    public function __construct() {

        $this->filesystem = new SymfonyFilesystem();


        /* 
         * Setup parameters
         */
        $this->useSingular = config($this->configFile . '.use_singular') === true ? true : false;

        /**
         * Setup module's name in PascalCase
         */
        $modulePath = config('modules.paths.modules').'/';
        $this->modulePath = Str::endsWith($modulePath, '/') ? $modulePath : $modulePath.'/';


    }

    /**
     * @param string $message - Message to display
     * @return int - Command::FAILURE
     */
    public function exit_fail(string $message): int
    {
        error($message);
        $this->filesystem->remove($this->tempPath);
        exit (Command::FAILURE);
    }

    /**
     * @param string $message - Message to display
     * @return int - Command::SUCCESS
     */
    public function exit_success(string $message): int
    {
        info($message);
        $this->filesystem->remove($this->tempPath);
        exit (Command::SUCCESS);
    }

    /**
     * Load the module name
     *
     * @return string: Module name
     */
    public function loadModuleName($argument): string
    {
        $this->moduleName = Str::studly($argument) ?? '';

        if ($this->moduleName !== '') {
            return $this->moduleName;
        }

        $this->moduleName = Str::studly(
            text(
                label: 'Please enter a name for the module (CamelCase): ',
                required: true,
                validate: fn(string $value) => match (true) {
                    strlen($value) < 1 => 'The name must be at least 1 characters.',
                    Str::contains($value, ' ') => 'The name must not contain spaces.',
                    ! file_exists($this->modulePath . Str::studly($value)) => "Module does not exist: " . $this->modulePath . Str::studly($value) . ".",
                    default => null
                }
            )
        );

        return $this->moduleName;
    }

     /**
     * Load the model name
     *
     * @return string: Model name
     */
    public function loadModelName($argument, $invalidIf=false): string
    {
        $this->modelName = Str::studly($argument) ?? '';

        if ($this->modelName !== '') {
            return $this->modelName;
	}

	$this->service->controllerExists($this->service->modelName)

        $this->modelName = Str::studly(
            text(
                label: 'Please enter a name for the model bread (CamelCase): ',
                required: true,
                validate: fn(string $value) => match (true) {
                    strlen($value) < 1 => 'The name must be at least 1 characters.',
                    Str::contains($value, ' ') => 'The name must not contain spaces.',
		    ($invalidIf xor $this->service->controllerExists($this->service->modelName)) => "Module does not exist: " . $this->modulePath . Str::studly($value) . ".",
                    default => null
                }
            )
	);

	/*
	invalid if	file exist	invalid result (erro = true)
	false		false		false
	false		true		true
	true		false		true
	true		true		false
	 */

        return $this->modelName;
    }

    /**
     * load template option
     *
     * @return string: Template name
     */
    public function loadTemplate($argument): string
    {
        $template = $argument ?? '';

        $templateConfig = config($this->configFile . '.templates');

        if ($template !== '') {
            if (in_array($template, array_keys($templateConfig))) {
                $template = $templateConfig[$template];
            } else {
                $template = '';
            }
        }

        if ($template == '') {
            $template = select(
                'Which template would you like to use?',
                array_keys($templateConfig)
            );
            $template = $templateConfig[$template];
        }

        $this->template     = $template;
        $this->templatePath = base_path($this->template);
        $this->tempPath     = base_path('.tmp'. Str::random(10));

        return $this->template;
    }
   
    /**
     * Load the model's table name  
     * 
     * @return string: Table name
     */
    public function loadTableName($argument): string
    {
        $this->tableName = Str::snake($argument) ?? '';

        if ($this->tableName !== '') {
            if (config($this->configFile . '.append_module_to_tablename') === true) {
                $this->tableName = Str::snake($this->moduleName) . '_' . $this->tableName;
            }
            return $this->tableName;
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

        return $this->tableName;
    }

    /**
     * Check if the model exists
     *
     * @param string $model_name
     * @return bool
     */
    public function modelExists($model_name): bool
    {   
        $model_file = $this->modulePath . $this->moduleName . '/app/Models/' . $model_name . '.php';
        return file_exists($model_file);
    }

    public function controllerExists($controller_name): bool
    {   
        $model_file = $this->modulePath . $this->moduleName . '/app/Http/Controllers/' . $controller_name . 'Controller.php';
        return file_exists($model_file);
    }


    /**
     * Delete a file or directory
     * 
     * @param string $path
     * @return void
     */
    public function delete($path): void
    {
        if (file_exists($path)) {
            $this->filesystem->remove($path);
        }
    }

    /**
     * Mirror a directory from source to destination
     * 
     * @param string $source
     * @param string $destination
     * @return void
     */
    public function mirror($source, $destination): void
    {
        $this->filesystem->mirror($source, $destination);
    }

    

    public function getTargetPathname(SplFileInfo $template): string 
    {
        $template_relative_pathname = substr($template->getPathname(), strlen($this->tempPath . '/Module/'));           
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
    
    /**
     * Create a target file from a template
     *
     * @param SplFileInfo $template
     * @return void
     */

    public function createTargetFile(SplFileInfo $template): void
    {

        $target_pathname = $this->getTargetPathname($template);
        $target_path = $this->createTargetPath($template);

        $this->filesystem->copy($template->getPathname(), $target_pathname);

    }

    /**
     * Append a timestamp to the filename
     *
     * @param string $sourceFile
     * @return string
     */
    public function appendTimestamp($sourceFile): string
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
    public function createFileFromStub(SplFileInfo $source_file, SplFileInfo $destination_file): void
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

    public function replaceContent(string $content): string    {
        return str_replace(array_keys($this->replacements), array_values($this->replacements), $content);
    }

    /**
     * Get the stub filename
     *
     * @param SplFileInfo $sourceFile
     * @return string
     */
    public function stubFilename(SplFileInfo $sourceFile): string {

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

    /**
     * Rename placeholders in a string
     *
     * @param string $model
     * @param string $separator
     * @param bool $arrayMap
     * @return string
     */
    
    public function renamePlaceholders($model, $separator, $arrayMap = null): string
    {
        $parts = preg_split('/(?=[A-Z])/', $model, -1, PREG_SPLIT_NO_EMPTY);

        if ($arrayMap) {
            $parts = array_map('strtolower', $parts);
        }

        return implode($separator, $parts);
    }

    /**
     * Load replacements for the template
     *
     * @return void
     */

    public function loadReplacements(): void {

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

            '{table}' => Str::snake(Str::studly($this->tableName))

        ];

        if ($this->useSingular) {
            $this->replacements['{module_plural}'] = trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', strtolower(Str::singular($module))));
            $this->replacements['{model_plural}']  = trim(preg_replace('/(?<!\ )[A-Z]/', ' $0', strtolower(Str::singular($model))));
            $this->replacements['{table}'] = Str::snake($this->tableName);
        }

    }
}

