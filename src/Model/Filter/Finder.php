<?php
declare(strict_types=1);

namespace Search\Model\Filter;

use Closure;

class Finder extends Base
{
    /**
     * @var array<string, mixed>
     */
    protected array $_defaultConfig = [
        'map' => [],
        'options' => [],
        'cast' => [],
    ];

    /**
     * Returns the finder method to use.
     *
     * @return string
     */
    public function finder(): string
    {
        return $this->getConfig('finder') ?: $this->name();
    }

    /**
     * Add a condition based on a custom finder.
     *
     * @return bool
     */
    public function process(): bool
    {
        $args = $this->getArgs();
        $map = $this->getConfig('map');
        foreach ($map as $to => $from) {
            $args[$to] = $args[$from] ?? null;
        }
        $casts = $this->getConfig('cast');
        foreach ($casts as $field => $toType) {
            $value = $args[$field] ?? null;
            if ($value === null) {
                continue;
            }

            if ($toType instanceof Closure) {
                $value = $toType($value);
            } else {
                settype($value, $toType);
            }

            $args[$field] = $value;
        }

        $options = $this->getConfig('options');
        $args += $options;

        $this->getQuery()->find($this->finder(), ...$args);

        return true;
    }
}
