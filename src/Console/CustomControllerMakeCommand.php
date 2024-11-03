<?php

namespace Ecs\Command\Console;

use Illuminate\Routing\Console\ControllerMakeCommand;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Support\Str;
use function Laravel\Prompts\confirm;

class CustomControllerMakeCommand extends ControllerMakeCommand
{
    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        if ($this->option('repository')) {
            return $this->resolveStubPath('/stubs/controller.repository.stub');
        }

        return parent::getStub();
    }

    /**
     * Resolve the fully-qualified path to the stub.
     *
     * @param  string  $stub
     * @return string
     */
    protected function resolveStubPath($stub)
    {
        return file_exists($customPath = $this->laravel->basePath(trim($stub, '/')))
                        ? $customPath
                        : __DIR__.$stub;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        if ($this->option('repository')) {
            $replace = [];

            $replace = $this->buildRepositoryReplacements($replace);

            $stub = $this->files->get($this->getStub());

            $stub = $this->replaceNamespace($stub, $name)->replaceClass($stub, $name);

            $class = str_replace(
                array_keys($replace), array_values($replace), $stub
            );

            return $class;
        }

        return parent::buildClass($name);
    }

    /**
     * Build the repository replacement values.
     *
     * @param  array  $replace
     * @return array
     */
    protected function buildRepositoryReplacements(array $replace)
    {
        $repositoryClass = $this->qualifyRepository($this->option('repository'));

        if (! class_exists($repositoryClass) && confirm("A {$repositoryClass} repository does not exist. Do you want to generate it?", default: true)) {
            $this->call('make:repository', ['name' => $repositoryClass, '--model' => $this->option('model')]);
        }

        return array_merge($replace, [
            'DummyFullRepositoryClass' => $repositoryClass,
            '{{ namespacedRepository }}' => $repositoryClass,
            '{{namespacedRepository}}' => $repositoryClass,
            'DummyRepositoryClass' => class_basename($repositoryClass),
            '{{ repository }}' => class_basename($repositoryClass),
            '{{repository}}' => class_basename($repositoryClass),
            'DummyRepositoryVariable' => lcfirst(class_basename($repositoryClass)),
            '{{ repositoryVariable }}' => lcfirst(class_basename($repositoryClass)),
            '{{repositoryVariable}}' => lcfirst(class_basename($repositoryClass)),
        ]);
    }

    /**
     * Qualify the given repository class base name.
     *
     * @param  string  $repository
     * @return string
     */
    protected function qualifyRepository(string $repository)
    {
        $repository = ltrim($repository, '\\/');

        $repository = str_replace('/', '\\', $repository);

        $rootNamespace = $this->rootNamespace();

        if (Str::startsWith($repository, $rootNamespace)) {
            return $repository;
        }

        return is_dir(app_path('Repositories'))
                    ? $rootNamespace.'Repositories\\'.$repository
                    : $rootNamespace.$repository;
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['repository', null, InputOption::VALUE_OPTIONAL, 'Generate a resource controller for the given repository']
        ]);
    }
}
