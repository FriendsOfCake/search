<?php
namespace Search\Model\Filter;

use Cake\Datasource\QueryInterface;

/**
 * Filter Interface
 */
interface FilterInterface
{
    /**
     * Get the database field name.
     *
     * @return string|array
     */
    public function field();

    /**
     * Get the database field name(s) as an array.
     *
     * @return array
     */
    public function fields();

    /**
     * Get the field name from HTTP GET query string.
     *
     * @return string
     */
    public function name();

    /**
     * Check if the name is present in the arguments from HTTP GET.
     *
     * @return bool
     */
    public function present();

    /**
     * Check if empty value for name in query string should be filtered out.
     *
     * @return bool
     */
    public function filterEmpty();

    /**
     * Checks whether this finder should be skipped.
     *
     * @return bool
     */
    public function skip();

    /**
     * Get the value of the "name" from HTTP GET arguments.
     *
     * @return mixed
     */
    public function value();

    /**
     * Sets the args.
     *
     * @param array $args Value.
     *
     * @return void
     */
    public function setArgs(array $args);

    /**
     * Gets the args.
     *
     * @return array
     */
    public function getArgs();

    /**
     * Get / Set the validation rules.
     *
     * @param array|null $value Value.
     * @return array|null
     * @codeCoverageIgnore
     * @internal
     */
    public function validate(array $value = null);

    /**
     * Sets the query object.
     *
     * @param \Cake\Datasource\QueryInterface $value Value.
     * @return void
     */
    public function setQuery(QueryInterface $value);

    /**
     * Gets the query object.
     *
     * @return \Cake\Datasource\QueryInterface|null
     */
    public function getQuery();

    /**
     * Modify the actual query object and append conditions based on the
     * subclass business rules and type.
     *
     * @return bool True if processed, false if skipped
     */
    public function process();
}
