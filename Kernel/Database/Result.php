<?php

namespace Kernel\Database;

use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\ResultSet\Exception;
use ArrayObject;

class Result {

	/**
	 * Database Resultset
	 *
	 * @var result
	 *
	 */
	private static $result = null;

	public function setResult($resultdata) {
		$result = new ResultSet;
		$result->initialize($resultdata);
		if (!$result instanceof ResultSet && !$result instanceof ArrayIterator) {
			throw new Exception('$result must be an instance of ResultSet / ArrayIterator');
		}
		$this->result = $result;
	}

	public function getResult() {
		if (!$this->result instanceof ResultSet && !$this->result instanceof ArrayIterator) {
			throw new Exception('$result must be an instance of ResultSet / ArrayIterator');
		}
		return $this->result;
	}

	public function buffer() {
		return $this->result->buffer();
	}

	public function isBuffered() {
		return $this->result->isBuffered();
	}

	/**
	 * Get the data source used to create the result set
	 *
	 * @return null|Iterator
	 */
	public function getDataSource() {
		return $this->result->getDataSource();
	}

	/**
	 * Retrieve count of fields in individual rows of the result set
	 *
	 * @return int
	 */
	public function getFieldCount() {
		return $this->result->getFieldCount();
	}

	/**
	 * Iterator: move pointer to next item
	 *
	 * @return void
	 */
	public function next() {
		$this->result->next();
	}

	/**
	 * Iterator: retrieve current key
	 *
	 * @return mixed
	 */
	public function key() {
		return $this->result->key();
	}

	/**
	 * Iterator: get current item
	 *
	 * @return array
	 */
	public function current() {
		$row = $this->result->current();
		if (is_array($row)) {
			$return[] = $row;
		} elseif (method_exists($row, 'toArray')) {
			$return[] = $row->toArray();
		} elseif ($row instanceof ArrayObject) {
			$return[] = $row->getArrayCopy();
		} else {
			throw new Exception\RuntimeException('Rows as part of this DataSource, with type ' . gettype($row) . ' cannot be cast to an array');
		}
		return $return;
	}

	/**
	 * Iterator: get current item
	 *
	 * @return array
	 */
	public function get($key = null) {
		$return = $this->current();
		if (!empty($key) && array_key_exists(0, $return)) {
			if (!array_key_exists($key, $return[0])) {
				return null;
			}
			return $return[0][$key];
		}
		return $return;
	}

	/**
	 * Iterator: is pointer valid?
	 *
	 * @return bool
	 */
	public function valid() {
		return $this->result->valid();

	}

	/**
	 * Iterator: rewind
	 *
	 * @return void
	 */
	public function rewind() {
		$this->result->rewind();
	}

	/**
	 * Countable: return count of rows
	 *
	 * @return int
	 */
	public function count() {
		return $this->result->count();
	}

	/**
	 * Has data: return count of rows
	 *
	 * @return int
	 */
	public function hasResult() {
		return ($this->result->count() > 0 ? true : false);
	}

	/**
	 * Cast result set to array of arrays
	 *
	 * @return array
	 * @throws Exception\RuntimeException if any row is not castable to an array
	 */
	public function toArray() {
		return $this->result->toArray();
	}
}
