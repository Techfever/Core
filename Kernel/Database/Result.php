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

	public static function setResult($resultdata) {
		$result = new ResultSet;
		$result->initialize($resultdata);
		if (!$result instanceof ResultSet && !$result instanceof ArrayIterator) {
			throw new Exception('$result must be an instance of ResultSet / ArrayIterator');
		}
		self::$result = $result;
	}

	public static function getResult() {
		if (!self::$result instanceof ResultSet && !self::$result instanceof ArrayIterator) {
			throw new Exception('$result must be an instance of ResultSet / ArrayIterator');
		}
		return self::$result;
	}

	public static function buffer() {
		return self::$result->buffer();
	}

	public static function isBuffered() {
		return self::$result->isBuffered();
	}

	/**
	 * Get the data source used to create the result set
	 *
	 * @return null|Iterator
	 */
	public static function getDataSource() {
		return self::$result->getDataSource();
	}

	/**
	 * Retrieve count of fields in individual rows of the result set
	 *
	 * @return int
	 */
	public static function getFieldCount() {
		return self::$result->getFieldCount();
	}

	/**
	 * Iterator: move pointer to next item
	 *
	 * @return void
	 */
	public static function next() {
		self::$result->next();
	}

	/**
	 * Iterator: retrieve current key
	 *
	 * @return mixed
	 */
	public static function key() {
		return self::$result->key();
	}

	/**
	 * Iterator: get current item
	 *
	 * @return array
	 */
	public static function current() {
		$row = self::$result->current();
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
	public static function get($key = null) {
		$return = self::current();
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
	public static function valid() {
		return self::$result->valid();

	}

	/**
	 * Iterator: rewind
	 *
	 * @return void
	 */
	public static function rewind() {
		self::$result->rewind();
	}

	/**
	 * Countable: return count of rows
	 *
	 * @return int
	 */
	public static function count() {
		return self::$result->count();
	}

	/**
	 * Has data: return count of rows
	 *
	 * @return int
	 */
	public static function hasResult() {
		return (self::$result->count() > 0 ? true : false);
	}

	/**
	 * Cast result set to array of arrays
	 *
	 * @return array
	 * @throws Exception\RuntimeException if any row is not castable to an array
	 */
	public static function toArray() {
		return self::$result->toArray();
	}
}
