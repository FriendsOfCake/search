<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Command\Bake;

use Cake\Console\BaseCommand;
use Cake\Console\CommandCollection;
use Cake\Console\ConsoleInput;
use Cake\Core\Plugin;
use Cake\Event\EventManager;
use Cake\Filesystem\Filesystem;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase;
use Search\Command\Bake\FilterCollectionCommand;

class FilterCollectionCommandTest extends TestCase
{
    use StringCompareTrait;
    use ConsoleIntegrationTestTrait;

    /**
     * @var string[]
     */
    protected $fixtures = [
        'core.Posts',
    ];

    /**
     * @var string
     */
    protected $_generatedBasePath;

    /**
     * setup method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->_compareBasePath = Plugin::path('Search') . 'tests' . DS . 'comparisons' . DS . 'Filter' . DS;
        $this->_generatedBasePath = ROOT . DS . 'tests/test_app/TestApp/Model/Filter/';

        $this->_in = $this->getMockBuilder(ConsoleInput::class)->getMock();

        $fs = new Filesystem();
        $fs->deleteDir($this->_generatedBasePath);

        $this->useCommandRunner();

        EventManager::instance()->on(
            'Console.buildCommands',
            function ($event, CommandCollection $commands) {
                $commands->add(FilterCollectionCommand::defaultName(), FilterCollectionCommand::class);
            }
        );
    }

    /**
     * Test empty migration.
     *
     * @return void
     */
    public function testEmpty()
    {
        $this->exec('bake filter_collection Empty');

        $this->assertExitCode(BaseCommand::CODE_SUCCESS);

        $file = $this->_generatedBasePath . 'EmptyCollection.php';
        $result = file_get_contents($file);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * @return void
     */
    public function testDefault()
    {
        $this->exec('bake filter_collection Posts');

        $this->assertExitCode(BaseCommand::CODE_SUCCESS);

        $file = $this->_generatedBasePath . 'PostsCollection.php';
        $result = file_get_contents($file);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * @return void
     */
    public function testCustomName()
    {
        $this->exec('bake filter_collection MyPosts Posts');

        $this->assertExitCode(BaseCommand::CODE_SUCCESS);

        $file = $this->_generatedBasePath . 'MyPostsCollection.php';
        $result = file_get_contents($file);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }

    /**
     * @return void
     */
    public function testPrefix()
    {
        $this->exec('bake filter_collection PrefixedPosts Posts --prefix Admin');

        $this->assertExitCode(BaseCommand::CODE_SUCCESS);

        $file = $this->_generatedBasePath . 'Admin/PrefixedPostsCollection.php';
        $result = file_get_contents($file);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }
}
