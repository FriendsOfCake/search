<?php
	namespace Search\Model\Filter;

	use Cake\I18n\Time;

	class DateBetween extends Base {

		/**
		 * Option to filter date range
		 *
		 * @return void
		 */
		public function process() {
			if ($this->skip()) {
				return;
			}

			if (strlen($this->args()[$this->name()]['from']) > 0) {
				$this->query()->andWhere([$this->name() . ' >=' => new Time($this->args()[$this->name()]['from'])]);
			}
			if (strlen($this->args()[$this->name()]['to']) > 0) {
				$this->query()->andWhere([$this->name() . ' <=' => new Time($this->args()[$this->name()]['to'])]);
			}
		}
	}
