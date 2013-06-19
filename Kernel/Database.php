<?php

namespace Kernel;

use Kernel\Database\Result;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Having;
use Zend\Db\Sql\Where;
use Zend\Db\ResultSet\Exception;

class Database extends Result {
	/**#@+
	 * Constant
	 * @const
	 */
	const SELECT = 'select';
	const QUANTIFIER = 'quantifier';
	const COLUMNS = 'columns';
	const TABLE = 'table';
	const JOINS = 'joins';
	const WHERE = 'where';
	const GROUP = 'group';
	const HAVING = 'having';
	const ORDER = 'order';
	const LIMIT = 'limit';
	const OFFSET = 'offset';
	const QUANTIFIER_DISTINCT = 'DISTINCT';
	const QUANTIFIER_ALL = 'ALL';
	const JOIN_INNER = 'inner';
	const JOIN_OUTER = 'outer';
	const JOIN_LEFT = 'left';
	const JOIN_RIGHT = 'right';
	const SQL_STAR = '*';
	const ORDER_ASCENDING = 'ASC';
	const ORDER_DESCENDING = 'DESC';
	const VALUES_MERGE = 'merge';
	const VALUES_SET = 'set';
	const SPECIFICATION_INSERT = 'insert';
	const SPECIFICATION_DELETE = 'delete';
	const SPECIFICATION_WHERE = 'where';
	const SPECIFICATION_UPDATE = 'update';
	/**#@-*/

	/**
	 * Database Adapter
	 *
	 * @var adapter
	 *
	 */
	private static $adapter = null;

	/**
	 * Cache Adapter
	 *
	 * @var adapter
	 *
	 */
	private static $cache = null;

	/**
	 * Cache Name
	 *
	 * @var cachename
	 *
	 */
	private static $cachename = null;

	/** 
	 * Query Action
	 * 
	 * @var action 
	 */ 
	private static $action = null;

	/** 
	 * Query Columns
	 * 
	 * @var columns 
	 */ 
	private static $columns = null;

	/** 
	 * Query Prefix Columns
	 * 
	 * @var prefixColumnsWithTable 
	 */ 
	private static $prefixColumnsWithTable = null;

	/** 
	 * Query From
	 * 
	 * @var from 
	 */ 
	private static $from = null;

	/** 
	 * Query Table
	 * 
	 * @var table 
	 */ 
	private static $table = null;

	/** 
	 * Query Join
	 * 
	 * @var join 
	 */ 
	private static $join = null;

	/** 
	 * Query Join On
	 * 
	 * @var joinon 
	 */ 
	private static $joinon = null;

	/** 
	 * Query Join Column
	 * 
	 * @var joincolumns 
	 */ 
	private static $joincolumns = null;

	/** 
	 * Query Join Type
	 * 
	 * @var jointype 
	 */ 
	private static $jointype = null;

	/** 
	 * Query Set
	 * 
	 * @var set 
	 */ 
	private static $set = null;

	/** 
	 * Query Set Flag
	 * 
	 * @var setflag
	 */ 
	private static $setflag = null;

	/** 
	 * Query Into
	 * 
	 * @var into 
	 */ 
	private static $into = null;

	/** 
	 * Query Values
	 * 
	 * @var values 
	 */ 
	private static $values = null;

	/** 
	 * Query Values Flag
	 * 
	 * @var valuesflag 
	 */ 
	private static $valuesflag = null;

	/** 
	 * Query Where
	 * 
	 * @var where 
	 */ 
	private static $where = null;

	/** 
	 * Query Where Combination
	 * 
	 * @var combination 
	 */ 
	private static $wherecombination = null;

	/** 
	 * Query Group
	 * 
	 * @var group 
	 */ 
	private static $group = null;

	/** 
	 * Query Having
	 * 
	 * @var having 
	 */ 
	private static $having = null;

	/** 
	 * Query Having Combination
	 * 
	 * @var havingcombination 
	 */ 
	private static $havingcombination = null;

	/** 
	 * Query Order
	 * 
	 * @var order 
	 */ 
	private static $order = null;

	/** 
	 * Query Limit
	 * 
	 * @var limit 
	 */ 
	private static $limit = null;

	/** 
	 * Query Offset
	 * 
	 * @var offset 
	 */ 
	private static $offset = null;

	/** 
	 * Query Affected Rows
	 * 
	 * @var affectedrows 
	 */ 
	private static $affectedrows = null;

	/**
	 * SQL Statement
	 *
	 * @var sql
	 *
	 */
	private static $sql = null;

	/**
	 * Constructor
	 *
	 * $action = select/insert/delete/update
	 *
	 * @param  null|string action
	 */
	public function __construct($action = null) {
		$action = strtolower($action);
		if (empty($action)) {
			throw new Exception('$action must be declare');
		}
		switch ($action) {
			case 'select':
				self::select();
				break;
			case 'insert':
				self::insert();
				break;
			case 'delete':
				self::delete();
				break;
			case 'update':
				self::update();
				break;
			default:
				throw new Exception('$action invalid. select/insert/delete/update');
				break;
		}
		self::prepare();
	}

	/**
	 * Select
	 * $action = select
	 */
	public static function select() {
		self::$action = 'select';
	}

	/**
	 * Insert
	 * $action = insert
	 */
	public static function insert() {
		self::$action = 'insert';
	}

	/**
	 * Delete
	 * $action = delete
	 */
	public static function delete() {
		self::$action = 'delete';
	}

	/**
	 * Update
	 * $action = update
	 */
	public static function update() {
		self::$action = 'update';
	}

	/**
	 * Specify columns
	 *
	 * Sql\Select
	 * Possible valid states:
	 *
	 *   array(*)
	 *
	 *   array(value, ...)
	 *     value can be strings or Expression objects
	 *
	 *   array(string => value, ...)
	 *     key string will be use as alias,
	 *     value can be string or Expression objects
	 *     
	 * Sql\Insert
	 * 
	 * @param  array $columns
	 * @param  bool  $prefixColumnsWithTable
	 */
	public static function columns(array $columns, $prefixColumnsWithTable = true) {
		if (self::$action == 'delete' || self::$action == 'update') {
			throw new Exception('Delete/Insert is not allow to use columns function');
		}
		if (!is_array($columns)) {
			throw new Exception('$columns must be a array');
		}
		self::$columns = $columns;
		self::$prefixColumnsWithTable = (bool) $prefixColumnsWithTable;
	}

	/**
	 * Create from clause
	 * Pass to Function table
	 * 
	 * @param  string|array|TableIdentifier $table
	 * @throws Exception
	 */

	public static function from($from) {
		if (!self::$action == 'delete') {
			throw new Exception('Only "delete" is allow to use group function');
		}
		self::table($from);
	}

	/**
	 * Create into clause
	 * 
	 * @param  string|array|TableIdentifier $table
	 * @throws Exception
	 */
	public static function into($into) {
		if (!self::$action == 'insert') {
			throw new Exception('Only "insert" is allow to use group function');
		}
		self::table($into);
	}

	/**
	 * Create table clause
	 * 
	 * Sql\Select
	 * Array
	 * array('t' => 'table'); = FROM table as t
	 * String
	 * 'table'; = FROM table
	 * 
	 * Sql\Update or Sql\Insert or Sql\Delete
	 * String
	 * 'table'; = DELETE FROM table or UPDATE table or INSERT INTO table
	 * 
	 * @param  string|array|TableIdentifier $table
	 * @throws Exception
	 */
	public static function table($table) {
		if (self::$action == 'select') {
			if (!is_string($table) && !is_array($table) && !$table instanceof TableIdentifier) {
				throw new Exception('$table must be a string, array, or an instance of TableIdentifier');
			}
			if (is_array($table) && (!is_string(key($table)) || count($table) !== 1)) {
				throw new Exception('from() expects $table as an array is a single element associative array');
			}
		} elseif (self::$action == 'insert' || self::$action == 'delete' || self::$action == 'update') {
			if (!is_string($table) && !$table instanceof TableIdentifier) {
				throw new Exception('$table must be a string, or an instance of TableIdentifier');
			}
		}
		self::$table = $table;
	}

	/**
	 * Create join clause
	 *
	 * @param  string|array $name
	 * @param  string $on
	 * @param  string|array $columns
	 * @param  string $type one of the JOIN_* constants
	 * @throws Exception
	 */
	public static function join($join, $on, $columns = self::SQL_STAR, $type = self::JOIN_INNER) {
		if (!self::$action == 'select') {
			throw new Exception('Only "select" is allow to use group function');
		}
		if (is_array($join) && (!is_string(key($join)) || count($join) !== 1)) {
			throw new Exception(sprintf("join() expects '%s' as an array is a single element associative array", array_shift($join)));
		}
		self::$join = $join;
		self::$joinon = $on;
		self::$joincolumns = $columns;
		self::$jointype = $type;
	}

	/**
	 * Set key/value pairs to update
	 *
	 * @param  array $values Associative array of key values
	 * @param  string $flag One of the VALUES_* constants
	 * @throws Exception
	 */
	public static function set(array $set, $flag = self::VALUES_SET) {
		if (!self::$action == 'update') {
			throw new Exception('Only "update" is allow to use group function');
		}
		if ($set == null) {
			throw new Exception('set() expects an array of values');
		}
		if (!is_array($set)) {
			throw new Exception('$set must be a array');
		}
		foreach ($set as $k => $v) {
			if (!is_string($k)) {
				throw new Exception('set() expects a string for the value key');
			}
		}
		self::$set = $set;
		self::$setflag = $flag;
	}

	/**
	 * Specify values to insert
	 *
	 * @param  array $values
	 * @param  string $flag one of VALUES_MERGE or VALUES_SET; defaults to VALUES_SET
	 * @throws Exception
	 */
	public static function values(array $values, $flag = self::VALUES_SET) {
		if (!self::$action == 'insert') {
			throw new Exception('Only "insert" is allow to use group function');
		}
		if ($values == null) {
			throw new Exception('values() expects an array of values');
		}
		if (!is_array($values)) {
			throw new Exception('$values must be a array');
		}
		self::$values = $values;
		self::$valuesflag = $flag;
	}

	/**
	 * Create where clause
	 *
	 * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
	 * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
	 * @throws Exception
	 */
	public static function where($where, $combination = Predicate\PredicateSet::OP_AND) {
		if (self::$action == 'insert') {
			throw new Exception('"insert" is not allow to use where function');
		}
		if (!$where instanceof Where && !$where instanceof \Closure && !$where instanceof Predicate\PredicateInterface && !is_string($where) && !is_array($where)) {
			throw new Exception('$where must be a string, array, or an instance of Having or Closure or Predicate\PredicateInterface');
		}

		self::$where = $where;
		self::$wherecombination = $combination;
	}

	public static function group($group) {
		if (!self::$action == 'select') {
			throw new Exception('Only "select" is allow to use group function');
		}
		self::$group = $group;
	}

	/**
	 * Create where clause
	 *
	 * @param  Where|\Closure|string|array $predicate
	 * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
	 */
	public static function having($having, $combination = Predicate\PredicateSet::OP_AND) {
		if (!self::$action == 'select') {
			throw new Exception('Only "select" is allow to use having function');
		}
		if (!$having instanceof Having && !$having instanceof \Closure && !is_string($having) && !is_array($having)) {
			throw new Exception('$having must be a string, array, or an instance of Having or Closure');
		}
		self::$having = $having;
		self::$havingcombination = $combination;
	}

	/**
	 * @param string|array $order
	 */
	public static function order($order) {
		if (!self::$action == 'select') {
			throw new Exception('Only "select" is allow to use order function');
		}
		if (!is_string($order) && !is_array($order)) {
			throw new Exception('$order must be a string, or array');
		}
		self::$order = $order;
	}

	/**
	 * @param int $limit
	 */
	public static function limit($limit) {
		if (!self::$action == 'select') {
			throw new Exception('Only "select" is allow to use limit function');
		}
		if (!is_int($limit)) {
			throw new Exception('$limit must be a int');
		}
		self::$limit = $limit;
	}

	/**
	 * @param int $offset
	 */
	public function offset($offset) {
		if (!self::$action == 'select') {
			throw new Exception('Only "select" is allow to use offset function');
		}
		if (!is_int($offset)) {
			throw new Exception('$offset must be a int');
		}
		self::$offset = $offset;
	}

	public static function execute() {
		self::prepareSQL();
		self::prepare();
		if (is_object(self::$sql) && is_object(self::$adapter)) {
			$statement = self::$adapter->createStatement();
			self::$sql->prepareStatement(self::$adapter, $statement);
			$resultdata = $statement->execute();
			if (self::$action == 'select') {
				$hasdata = false;
				if (self::hasCacheName() && self::$cache->hasItem(self::$cachename)) {
					$resultdata = self::$cache->getItem(self::$cachename);
					$hasdata = true;
				} elseif ($resultdata instanceof ResultInterface && $resultdata->isQueryResult()) {
					$result = new ResultSet;
					$result->initialize($resultdata);
					$resultdata = $result->toArray();
					$hasdata = true;
				}
				if ($hasdata) {
					self::setResult($resultdata);

					if (self::hasCacheName() && !self::$cache->hasItem(self::$cachename)) {
						self::setCache($resultdata);
					}
				}
			} elseif (self::$action == 'delete' || self::$action == 'update' || self::$action == 'insert') {
				self::$affectedrows = $resultdata->getAffectedRows();

				self::$cache->clearByPrefix(self::$cachename);
			}
		}
		return null;
	}

	public static function affectedRows() {
		if (self::$affectedrows > 0) {
			return true;
		}
		return false;
	}

	public static function prepareSQL() {
		$sql = null;
		if (self::$action == 'select') {
			$sql = new \Zend\Db\Sql\Select;
			if (!empty(self::$columns)) {
				$sql->columns(self::$columns, self::$prefixColumnsWithTable);
			}
			if (!empty(self::$table)) {
				$sql->from(self::$table);
			}
			if (!empty(self::$join)) {
				$sql->join(self::$join, self::$joinon, self::$joincolumns, self::$jointype);
			}
			if (!empty(self::$where)) {
				$sql->where(self::$where, self::$wherecombination);
			}
			if (!empty(self::$group)) {
				$sql->group(self::$group);
			}
			if (!empty(self::$having)) {
				$sql->having(self::$having, self::$havingcombination);
			}
			if (!empty(self::$order)) {
				$sql->order(self::$order);
			}
			if (!empty(self::$limit)) {
				$sql->limit(self::$limit);
			}
			if (!empty(self::$offset)) {
				$sql->offset(self::$offset);
			}
		} elseif (self::$action == 'insert') {
			$sql = new \Zend\Db\Sql\Insert;
			if (!empty(self::$into)) {
				$sql->into(self::$into);
			}
			if (!empty(self::$columns)) {
				$sql->columns(self::$columns);
			}
			if (!empty(self::$values)) {
				$sql->values(self::$values, self::$valuesflag);
			}
		} elseif (self::$action == 'delete') {
			$sql = new \Zend\Db\Sql\Delete;
			if (!empty(self::$table)) {
				$sql->from(self::$table);
			}
			if (!empty(self::$where)) {
				$sql->where(self::$where, self::$wherecombination);
			}
		} elseif (self::$action == 'update') {
			$sql = new \Zend\Db\Sql\Update;
			if (!empty(self::$table)) {
				$sql->table(self::$table);
			}
			if (!empty(self::$set)) {
				$sql->set(self::$set, self::$setflag);
			}
			if (!empty(self::$where)) {
				$sql->where(self::$where, self::$wherecombination);
			}
		}
		self::$sql = $sql;
		if (!is_object(self::$sql)) {
			throw new Exception('Zend\Db\Sql\\' . self::$action . ' object not found');
		}
		return self::$sql;
	}

	public static function getSqlString() {
		self::prepareSQL();
		if (is_object(self::$sql)) {
			return self::$sql->getSqlString(self::$adapter->getPlatform());
		}
		return null;
	}

	public static function prepare() {
		self::prepareAdapter();
		self::prepareCache();
	}

	public static function prepareAdapter() {
		if (!is_object(self::$adapter)) {
			self::$adapter = ServiceLocator::getServiceManager('db');
		}
		if (!is_object(self::$adapter)) {
			throw new Exception('Zend\Db\Adapter object not found');
		}
		return self::$adapter;
	}

	public static function prepareCache() {
		if (!is_object(self::$cache)) {
			self::$cache = ServiceLocator::getServiceManager('cache\filesystem');
			$config = ServiceLocator::getServiceConfig('cachestorage');
			$cacheoption = $config['filesystem']['options'];
			$cacheoption['namespace'] = 'database';
			self::$cache->setOptions($cacheoption);
		}
		if (!is_object(self::$cache)) {
			throw new Exception('Zend\Cache object not found');
		}
		return self::$cache;
	}

	/**
	 * Set namespace.
	 *
	 * @param  string $namespace
	 */
	public static function setCacheName($cachename = null) {
		self::$cachename = $cachename;
	}

	/**
	 * Set namespace.
	 *
	 * @param  string $namespace
	 */
	public static function hasCacheName() {
		if (empty(self::$cachename)) {
			return false;
		}
		return true;
	}

	/**
	 * Store an item.
	 *
	 * @param  string $key
	 * @param  mixed  $result
	 * @return bool
	 */
	public function setCache($result) {
		if (empty(self::$cachename)) {
			throw new Exception('Zend\Cache namespace no defined');
		}
		if (!is_object(self::$cache)) {
			throw new Exception('Zend\Cache object not found');
		}
		self::$cache->setItem(self::$cachename, $result);
	}

	public static function clearCache($cachename = null) {
		self::prepareCache();
		if (self::hasCacheName()) {
			self::$cache->clearByPrefix($cachename);
		}
	}
}
