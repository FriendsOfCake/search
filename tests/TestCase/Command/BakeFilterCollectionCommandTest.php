<?php
declare(strict_types=1);

namespace Search\Test\TestCase\Command;

use Cake\Console\BaseCommand;
use Cake\Console\ConsoleInput;
use Cake\Core\Plugin;
use Cake\Filesystem\Folder;
use Cake\TestSuite\ConsoleIntegrationTestTrait;
use Cake\TestSuite\StringCompareTrait;
use Cake\TestSuite\TestCase;

class BakeFilterCollectionCommandTest extends TestCase
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

        $files = (new Folder($this->_generatedBasePath))->findRecursive();
        foreach ($files as $file) {
            unlink($file);
        }

        $this->useCommandRunner();
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

        $file = $this->_generatedBasePath . 'EmptyFilterCollection.php';
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

        $file = $this->_generatedBasePath . 'PostsFilterCollection.php';
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

        $file = $this->_generatedBasePath . 'MyPostsFilterCollection.php';
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

        $file = $this->_generatedBasePath . 'Admin/PrefixedPostsFilterCollection.php';
        $result = file_get_contents($file);
        $this->assertSameAsFile(__FUNCTION__ . '.php', $result);
    }
}
