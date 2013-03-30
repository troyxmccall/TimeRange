<?php
/**
 * Compare and loop time ranges.
 *
 * @package     TimeRange
 * @author      Joonas Järnstedt
 * @version 	0.1
 *
 */
class TimeRange implements Iterator {
	
	private $defaultFormat = 'Y-m-d H:i:s';

	private $start;
	private $end;

	// Iterator
	private $position = 0;
	private $dates = array();

	const SECOND = 0;
	const MINUTE = 1;
	const HOUR = 2;
	const DAY = 3;
	const MONTH = 4;
	const YEAR = 5;

	const FORWARD = 1;
	const BACKWARD = 0;

	/**
	 * Create TimeRange from DateTime objects or time strings.
	 */
	public function __construct($start, $end) {

		$this->position = 0;

		try {
			if (!is_object($start)) {
				// Create datetime from string
				$start = new Datetime($start);
			}

			if (!is_object($end)) {
				// Create datetime from string
				$end = new Datetime($end);
			}

			if ($start instanceof DateTime and $end instanceof DateTime) {
				$this->start = clone $start;
				$this->end = clone $end;
			} else {
				throw new InvalidArgumentException('Invalid DateTime.');
			}

		} catch (Exception $e) {
			throw new InvalidArgumentException('Invalid DateTime.');
		}

		if ($this->start > $this->end) {
			throw new InvalidArgumentException(
				'Invalid TimeRange. The starting time must be before the ending time.');
		}
	}

	/**
	 * Change start datetime.
	 */
	public function setStart($start) {
		try {
			if (!is_object($start)) {
				// Create datetime from string
				$start = new Datetime($start);
			}

			if ($start instanceof DateTime) {
				$this->start = clone $start;
			} else {
				throw new InvalidArgumentException('Invalid DateTime.');
			}

		} catch (Exception $e) {
			throw new InvalidArgumentException('Invalid DateTime.');
		}

		if ($this->start > $this->end) {
			throw new InvalidArgumentException(
				'Invalid TimeRange. The starting time must be before the ending time.');
		}
	}

	/**
	 * Change end datetime.
	 */
	public function setEnd($end) {
		try {
			if (!is_object($end)) {
				// Create datetime from string
				$end = new Datetime($end);
			}

			if ($end instanceof DateTime) {
				$this->end = clone $end;
			} else {
				throw new InvalidArgumentException('Invalid DateTime.');
			}

		} catch (Exception $e) {
			throw new InvalidArgumentException('Invalid DateTime.');
		}

		if ($this->start > $this->end) {
			throw new InvalidArgumentException(
				'Invalid TimeRange. The starting time must be before the ending time.');
		}
	}

	/**
	 * Returns true if the two given time ranges overlap.
	 * @return bool
	 */
	public function overlaps($timeRange, $precision = NULL) {

		switch ($precision) {
			case self::YEAR:
			$format = 'Y';
			break;
			case self::MONTH:
			$format = 'Ym';
			break;
			case self::DAY:
			$format = 'Ymd';
			break;
			case self::HOUR:
			$format = 'YmdH';
			break;
			case self::MINUTE:
			$format = 'YmdHi';
			break;
			default:
			// Compare precision is seconds (default)
			$format = 'YmdHis';
		}

		try {
			if (is_object($timeRange)) {
				if ($timeRange instanceof TimeRange) {
					
					if ($this->start->format($format) <= $timeRange->end->format($format) and
						$timeRange->start->format($format) <= $this->end->format($format))
					{
						return true;
					} else {
						return false;
					}
				}
			} else {
				$timeRange = new DateTime($timeRange);
			}

			if ($this->start->format($format) <= $timeRange->format($format) and
				$this->end->format($format) >= $timeRange->format($format))
			{
				return true;
			} else {
				return false;
			}

		} catch (Exception $e) {
			throw new InvalidArgumentException('Invalid TimeRange: ' . $e);
		}

	}

	public function getMinutes($interval = 1, $direction = self::FORWARD) {

		$this->dates = array();

		if ($direction == self::FORWARD) {
			$iterator = clone $this->start;
			$iterator->setTime($this->start->format('H'), $this->start->format('i'), 0);

			while ($iterator <= $this->end) {

				$this->dates[] = clone $iterator;
				$iterator->modify("+$interval minute");
			}

		} else {
			$iterator = clone $this->end;
			
			while ($iterator >= $this->start) {
				$iterator->setTime($iterator->format('H'), $iterator->format('i'), 0);
				$this->dates[] = clone $iterator;
				$iterator->setTime($iterator->format('H'), $iterator->format('i'), 59);
				$iterator->modify("-$interval minute");
			}
		}

		return $this->dates;
	}

	public function getHours($interval = 1, $direction = self::FORWARD) {

		$this->dates = array();

		if ($direction == self::FORWARD) {
			$iterator = clone $this->start;
			$iterator->setTime($this->start->format('H'), 0, 0);

			while ($iterator <= $this->end) {

				$this->dates[] = clone $iterator;
				$iterator->modify("+$interval hour");
			}

		} else {
			$iterator = clone $this->end;
			
			while ($iterator >= $this->start) {
				$iterator->setTime($iterator->format('H'), 0, 0);
				$this->dates[] = clone $iterator;
				$iterator->setTime($iterator->format('H'), 59, 59);
				$iterator->modify("-$interval hour");
			}
		}

		return $this->dates;
	}

	/**
	 * Get array of days in the range.
	 * @param int $interval 
	 * @param int $direction 
	 * @return DateTime array
	 */
	public function getDays($interval = 1, $direction = self::FORWARD) {

		$this->dates = array();

		if ($direction == self::FORWARD) {
			$iterator = clone $this->start;
			$iterator->setTime(0, 0, 0);

			while ($iterator <= $this->end) {

				$this->dates[] = clone $iterator;
				$iterator->modify("+$interval day");
			}

		} else {
			$iterator = clone $this->end;
			
			while ($iterator >= $this->start) {
				$iterator->setTime(0, 0, 0);
				$this->dates[] = clone $iterator;
				$iterator->setTime(23, 59, 59);
				$iterator->modify("-$interval day");
			}
		}

		return $this->dates;
	}

	/**
	 * Get array of months in the range.
	 * @param type $interval 
	 * @param type $direction 
	 * @return type
	 */
	public function getMonths($interval = 1, $direction = self::FORWARD) {

		$dates = array();

		if ($direction == self::FORWARD) {
			$iterator = clone $this->start;
			$iterator->setTime(0, 0, 0);
			$iterator->setDate($this->start->format('Y'), $this->start->format('m'), 1);

			while ($iterator <= $this->end) {

				$dates[] = clone $iterator;
				$iterator->modify("+$interval month");
			}

		} else {
			$iterator = clone $this->end;
			$iterator->setDate($this->end->format('Y'), $this->end->format('m'), 1);
			$iterator->setTime(0, 0, 0);
			
			while ($iterator->format('Ym') >= $this->start->format('Ym')) {
				$dates[] = clone $iterator;
				$iterator->modify("-$interval month");
			}
		}

		return $dates;
	}

	/**
	 * Iteration functions
	 */
	function rewind() {
		$this->getDays();
		$this->position = 0;
	}
	function current() {
		return $this->dates[$this->position];
	}
	function key() {
		return $this->position;
	}
	function next() {
		return ++$this->position;
	}
	function valid() {
		return isset($this->dates[$this->position]);
	}
}