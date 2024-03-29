<?php
declare(strict_types=1);

namespace Search;

use Cake\Console\CommandCollection;
use Cake\Core\BasePlugin;

class SearchPlugin extends BasePlugin
{
    /**
     * Plugin name
     *
     * @var string|null
     */
    protected ?string $name = 'Search';

    /**
     * Do bootstrapping or not
     *
     * @var bool
     */
    protected bool $bootstrapEnabled = false;

    /**
     * Load routes or not
     *
     * @var bool
     */
    protected bool $routesEnabled = false;

    /**
     * Add console commands for the plugin.
     *
     * @param \Cake\Console\CommandCollection $commands The command collection to update
     * @return \Cake\Console\CommandCollection
     */
    public function console(CommandCollection $commands): CommandCollection
    {
        // Bake plugin handles discovery of bake commands itself.
        // Since we currently only have a command class for bake command, we don't need to to anything here.
        return $commands;
    }
}
