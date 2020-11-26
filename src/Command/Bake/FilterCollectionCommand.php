<?php
declare(strict_types=1);

namespace Search\Command\Bake;

use Bake\Command\BakeCommand;
use Bake\Utility\TemplateRenderer;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\Core\Configure;
use Cake\Database\Exception;
use Cake\ORM\Table;

/**
 * For generating filter collection classes.
 *
 * E.g.:
 * src/Model/Filter/MyCustomFilterCollection.php
 * src/Model/Filter/Admin/UsersFilterCollection.php
 */
class FilterCollectionCommand extends BakeCommand
{
    /**
     * Task name used in path generation.
     *
     * @var string
     */
    public $pathFragment = 'Model/Filter/';

    /**
     * @var string
     */
    protected $_name;

    /**
     * @var string[][]
     */
    protected $_map = [
        'value' => [
            'integer',
            'tinyinteger',
            'biginteger',
            'smallinteger',
            'uuid',
            'binaryuuid',
            'boolean',
        ],
        'like' => [
            'string',
            'char',
        ],
    ];

    /**
     * @var string[]
     */
    protected $_ignoreFields = [
        'lft',
        'rght',
        'password',
    ];

    /**
     * @inheritDoc
     */
    public static function defaultName(): string
    {
        return 'bake filter_collection';
    }

    /**
     * Execute the command.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $this->extractCommonProperties($args);

        $this->bake((string)$args->getArgumentAt(0), $args, $io);

        return static::CODE_SUCCESS;
    }

    /**
     * @inheritDoc
     */
    public function bake(string $name, Arguments $args, ConsoleIo $io): void
    {
        $this->_name = $name;

        $fields = $this->getFields($name, $args->getArgumentAt(1));
        $templateData = $this->templateData($args) + [
            'fields' => $fields,
        ];

        $renderer = new TemplateRenderer((string)$args->getOption('theme'));
        $renderer->set('name', $name);
        $renderer->set($templateData);
        $contents = $renderer->generate($this->template());

        $filename = $this->getPath($args) . $this->fileName($name);
        $io->createFile($filename, $contents, (bool)$args->getOption('force'));
    }

    /**
     * @inheritDoc
     */
    public function template(): string
    {
        return 'Search.FilterCollection/filter_collection';
    }

    /**
     * @param \Cake\Console\Arguments $arguments Arguments
     * @return array
     */
    public function templateData(Arguments $arguments): array
    {
        $name = $this->_name;
        $namespace = Configure::read('App.namespace');
        $pluginPath = '';
        if ($this->plugin) {
            $namespace = $this->_pluginNamespace($this->plugin);
            $pluginPath = $this->plugin . '.';
        }

        $namespace .= '\\Model\\Filter';

        $namespacePart = null;
        if (strpos($name, '/') !== false) {
            $parts = explode('/', $name);
            $name = array_pop($parts);
            $namespacePart = implode('\\', $parts);
        }
        if ($namespacePart) {
            $namespace .= '\\' . $namespacePart;
        }

        $prefix = $arguments->getOption('prefix');
        if ($prefix) {
            $namespace .= '\\' . $prefix;
        }

        return [
            'plugin' => $this->plugin,
            'pluginPath' => $pluginPath,
            'namespace' => $namespace,
            'prefix' => $prefix,
            'name' => $name,
        ];
    }

    /**
     * @inheritDoc
     */
    public function name(): string
    {
        return 'filter_collection';
    }

    /**
     * @inheritDoc
     */
    public function fileName(string $name): string
    {
        return $name . 'FilterCollection.php';
    }

    /**
     * @param string $name Name of filter collection
     * @param string|null $modelName Model name
     * @return array
     */
    protected function getFields(string $name, ?string $modelName): array
    {
        $model = $modelName ?: $name;
        try {
            $table = $this->getTableLocator()->get($model);
            $columns = $table->getSchema()->columns();
        } catch (Exception $exception) {
            return [];
        }

        $fields = [];
        foreach ($columns as $column) {
            if ($this->shouldSkip($column, $table)) {
                continue;
            }

            $columnInfo = (array)$table->getSchema()->getColumn($column);
            $type = $columnInfo['type'];
            if (in_array($type, $this->_map['value'], true)) {
                $fields[$column] = 'value';
                continue;
            }

            if (in_array($type, $this->_map['like'], true)) {
                $fields[$column] = 'like';
                continue;
            }
        }

        return $fields;
    }

    /**
     * Gets the option parser instance and configures it.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser The option parser to update.
     * @return \Cake\Console\ConsoleOptionParser
     */
    public function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser = $this->_setCommonOptions($parser);

        $parser->setDescription(
            'Bake filter collections for a model search functionality.'
        )->addArgument('name', [
            'help' => 'Name of the filter collection to bake. You can use Plugin.name '
            . 'as a shortcut for plugin baking. By default this will also try to find the model based on that name.',
            'required' => true,
        ])->addArgument('model', [
            'help' => 'Model to use if you use a custom collection name that does not match the model.',
        ])->addOption('prefix', [
            'help' => 'The namespace prefix to use.',
            'default' => false,
        ]);

        return $parser;
    }

    /**
     * Checks if this column should be skipped.
     *
     * This hook method can be extended and customized as per application needs.
     *
     * @param string $column Column name
     * @param \Cake\ORM\Table $table Table instance
     * @return bool
     */
    protected function shouldSkip(string $column, Table $table): bool
    {
        return $column === $table->getPrimaryKey() || in_array($column, $this->_ignoreFields, true);
    }
}
