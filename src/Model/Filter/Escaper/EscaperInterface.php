<?php
declare(strict_types=1);

namespace Search\Model\Filter\Escaper;

interface EscaperInterface
{
    /**
     * Replace substitutions with original wildcards
     * but first, escape the original wildcards in the text to use them as normal search text
     *
     * @param string $value Value.
     * @return string Value
     */
    public function formatWildcards(string $value): string;
}
