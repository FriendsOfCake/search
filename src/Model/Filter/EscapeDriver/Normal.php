<?php
namespace Search\Model\Filter\EscapeDriver;

class Normal extends Base
{
    /**
     * Replace substitutions with original wildcards
     * but first, escape the original wildcards in the text to use them as normal search text
     *
     * @param string $value Value.
     * @return string Value
     */
    public function formatWildcards($value)
    {
        $from = $to = $substFrom = $substTo = [];
        if ($this->config('wildcardAny') !== '%') {
            $from[] = '%';
            $to[] = '\%';
            $substFrom[] = $this->config('wildcardAny');
            $substTo[] = '%';
        }
        if ($this->config('wildcardOne') !== '_') {
            $from[] = '_';
            $to[] = '\_';
            $substFrom[] = $this->config('wildcardOne');
            $substTo[] = '_';
        }
        if ($from) {
            // Escape first
            $value = str_replace($from, $to, $value);
            // Replace wildcards
            $value = str_replace($substFrom, $substTo, $value);
        }

        return $value;
    }
}
