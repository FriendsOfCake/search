<?php
declare(strict_types=1);

namespace Search\Model\Filter;

use function Cake\Core\deprecationWarning;

class Callback extends Base
{
    /**
     * Default configuration.
     *
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'extraParams' => [],
    ];

    /**
     * Modify query using callback.
     *
     * The callback must return a `bool` indicating whether it modified the
     * query (controls `isSearch()`). Returning `null`/void is supported for
     * backwards compatibility but is deprecated and will throw in a future
     * version.
     *
     * @return bool
     */
    public function process(): bool
    {
        $result = call_user_func(
            $this->getConfig('callback'),
            $this->getQuery(),
            $this->getArgs(),
            $this,
        );

        if ($result === null) {
            deprecationWarning(
                '7.9.0',
                sprintf(
                    'Callback filter `%s` returned null; callbacks must return bool to control isSearch(). '
                    . 'In a future version returning null/void will throw an error.',
                    $this->name(),
                ),
            );

            return true;
        }

        return $result;
    }
}
