<?php

namespace Msantang\QueryFilters\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeQueryFilter extends Command
{
    use \Illuminate\Console\DetectsApplicationNamespace;

    /**
     * The name of the console command.
     *
     * @var string
     */
    protected $signature = 'queryfilter:make {name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create A New Query Filter';

    /**
     * Class to create.
     *
     * @var array|string
     */
    protected $class;

    /**
     * The filesystem instance.
     *
     * @var Filesystem
     */
    protected $files;

    /**
     * MakeEloquentFilter constructor.
     *
     * @param Filesystem $files
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->makeClassName()->compileStub();
        $this->info(class_basename($this->getClassName()).' Created Successfully!');
    }

    public function compileStub()
    {
        if ($this->files->exists($path = $this->getPath())) {
            $this->error("\n\n\t".$path.' Already Exists!'."\n");
            die;
        }
        if ($this->files->exists($pathInput = $this->getInputPath())) {
            $this->error("\n\n\t".$pathInput.' Already Exists!'."\n");
            die;
        }
        $this->makeDirectory($path);
        // make filter
        $tmp = $this->applyValuesToStub($this->files->get(__DIR__.'/../stubs/QueryFilter.stub'));
        $this->files->put($path, $tmp);
        // make filter input
        $tmp = $this->applyValuesToStub($this->files->get(__DIR__.'/../stubs/QueryFilterInput.stub'));
        $this->files->put($pathInput, $tmp);
    }

    public function applyValuesToStub($stub)
    {
        $className = class_basename($this->getClassName());
        $search = ['{{class}}', '{{namespace}}'];
        $replace = [$className, str_replace('\\'.$className, '', $this->getClassName())];

        return str_replace($search, $replace, $stub);
    }

    public function getPath()
    {
        return app_path(str_replace([$this->getAppNamespace(), '\\'], ['', '/'], $this->getClassName().'.php'));
    }

    public function getInputPath()
    {
        return app_path(str_replace([$this->getAppNamespace(), '\\'], ['', '/'], $this->getClassName().'Input.php'));
    }

    /**
     * Build the directory for the class if necessary.
     *
     * @param  string $path
     * @return string
     */
    protected function makeDirectory($path)
    {
        if (! $this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0777, true, true);
        }
    }

    /**
     * Create Filter Class Name.
     *
     * @return $this
     */
    public function makeClassName()
    {
        $parts = array_map('studly_case', explode('\\', $this->argument('name')));
        $className = array_pop($parts);
        $ns = count($parts) > 0 ? implode('\\', $parts).'\\' : '';

        $fqClass = config('queryfilters.namespace', 'App\\QueryFilters\\').$ns.$className;

        if (substr($fqClass, -6, 6) !== 'Filter') {
            $fqClass .= 'Filter';
        }

        $this->setClassName($fqClass);

        if (class_exists($this->getClassName())) {
            $this->error("\n\n\t".$this->getClassName().' Already Exists!'."\n");
            die;
        }

        return $this;
    }

    public function setClassName($name)
    {
        $this->class = $name;

        return $this;
    }

    public function getClassName()
    {
        return $this->class;
    }
}
