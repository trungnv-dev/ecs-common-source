<?php

namespace Ecs\Common\Console;

use Illuminate\Console\GeneratorCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Str;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\suggest;

class ServiceMakeCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:service';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new service class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Service';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = $this->option('repository')
            ? '/stubs/service.repository.stub'
            : '/stubs/service.stub';

        return $this->resolveStubPath($stub);
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
     * Get the default namespace for the class.
     *
     * @param  string  $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Services';
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $replace = [];

        if ($this->option('repository')) {
            $replace = $this->buildRepositoryReplacements($replace);
        }

        $class = str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );

        return $class;
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
        return [
            ['repository', null, InputOption::VALUE_OPTIONAL, 'Generate a resource service for the given repository'],
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'Generate a resource repository for the given model if exists repository option'],
        ];
    }

    /**
     * Interact further with the user if they were prompted for missing arguments.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        if ($this->didReceiveOptions($input)) {
            return;
        }

        $input->setOption('repository', suggest(
            label: "What repository should this service be for? (Optional)",
            options: $this->possibleRepositories()
        ));

        if ($this->option('repository')) {
            $input->setOption('model', suggest(
                label: "What model should this repository be for? (Optional)",
                options: $this->possibleModels()
            ));
        }
    }

    /**
     * Get a list of possible repository names.
     *
     * @return array<int, string>
     */
    protected function possibleRepositories()
    {
        $path = is_dir(app_path('Repositories')) ? app_path('Repositories') : app_path();

        return collect(Finder::create()->files()->depth(0)->in($path))
            ->map(fn ($file) => $file->getBasename('.php'))
            ->sort()
            ->values()
            ->all();
    }
}