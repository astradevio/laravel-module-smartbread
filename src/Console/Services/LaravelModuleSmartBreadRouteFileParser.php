<?php
/**
 * LaravelModuleSmartBreadRouteFileParser
 *
 * This class is responsible for parsing a route file in Laravel.
 * It extracts the namespace, uses, and body of the route file.
 * 
 * @package astradev\LaravelModuleSmartBreadGenerator\Services
 * 
 * Services form smartbread templates.
 * 
 * See: http://www.laravelmodule.com
 * 
 * @package astradev\ModuleSmartBreadGenerator
 * @author  Leandro Neves <leandro@astradev.io>
 * @license MIT
 * 
 */

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem as SymfonyFilesystem;
use Symfony\Component\Finder\Finder;
use SplFileInfo;

class LaravelModuleSmartBreadRoyuteFileParser {

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

    public function mergeUses(array $newUses): void
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