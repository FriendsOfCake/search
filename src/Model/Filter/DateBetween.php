<?php
namespace Search\Model\Filter;

use Cake\I18n\Time;

class DateBetween extends Base
{

    /**
     * Process dates conditions
     *
     * @return void
     */
    public function process()
    {
        if ($this->skip()) {
            return;
        }

        if (!empty($this->args()[$this->name()]['from']) > 0) {
            $timeFrom = new Time($this->args()[$this->name()]['from']);
            $this->query()->andWhere([$this->name() . ' >=' => $timeFrom]);
        }
        if (!empty($this->args()[$this->name()]['to']) > 0) {
            $timeTo = new Time($this->args()[$this->name()]['to']);
            $this->query()->andWhere([$this->name() . ' <=' => $timeTo]);
        }
    }
}
