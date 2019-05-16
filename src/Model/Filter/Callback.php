<?php
declare(strict_types=1);

namespace Search\Model\Filter;

class Callback extends Base
{
    /**
     * Modify query using callback.
     *
     * @return bool
     */
    public function process(): bool
    {
        $ret = call_user_func(
            $this->getConfig('callback'),
            $this->getQuery(),
            $this->getArgs(),
            $this
        );
        if ($ret === null) {
            return true;
        }

        return $ret;
    }
}
