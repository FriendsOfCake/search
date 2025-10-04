<?php
declare(strict_types=1);

namespace Search\Test\TestApp\Form;

use Cake\Form\Form;
use Cake\Validation\Validator;

class SearchForm extends Form
{
    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->allowEmptyString('q')
            ->minLength('q', 3, 'Search query must be at least 3 characters long');

        return $validator;
    }
}
