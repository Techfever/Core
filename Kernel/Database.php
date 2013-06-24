<?php

namespace Kernel;

use Kernel\Database\Result;
use Kernel\Exception;
use Zend\Db\Adapter\Adapter;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Insert;
use Zend\Db\Sql\Update;
use Zend\Db\Sql\Delete;
use Zend\Db\Sql\Predicate;
use Zend\Db\Sql\TableIdentifier;
use Zend\Db\Sql\Having;
use Zend\Db\Sql\Where;
use Zend\Cache\Storage\Adapter\Filesystem;
use Zend\Cache\StorageFactory;

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
	private $adapter = null;

	/**
	 * Cache Adapter
	 *
	 * @var adapter
	 *
	 */
	private $cache = null;

	/**
	 * Cache Name
	 *
	 * @var cachename
	 *
	 */
	private $cachename = null;

	/** 
	 * Query Action
	 * 
	 * @var action 
	 */ 
	private $action = null;

	/** 
	 * Query Columns
	 * 
	 * @var columns 
	 */ 
	private $columns = null;

	/** 
	 * Query Prefix Columns
	 * 
	 * @var prefixColumnsWithTable 
	 */ 
	private $prefixColumnsWithTable = null;

	/** 
	 * Query From
	 * 
	 * @var from 
	 */ 
	private $from = null;

	/** 
	 * Query Table
	 * 
	 * @var table 
	 */ 
	private $table = null;

	/** 
	 * Query Join
	 * 
	 * @var join 
	 */ 
	private $join = array();

	/** 
	 * Query Join On
	 * 
	 * @var joinon 
	 */ 
	private $joinon = array();

	/** 
	 * Query Join Column
	 * 
	 * @var joincolumns 
	 */ 
	private $joincolumns = array();

	/** 
	 * Query Join Type
	 * 
	 * @var jointype 
	 */ 
	private $jointype = array();

	/** 
	 * Query Set
	 * 
	 * @var set 
	 */ 
	private $set = null;

	/** 
	 * Query Set Flag
	 * 
	 * @var setflag
	 */ 
	private $setflag = null;

	/** 
	 * Query Into
	 * 
	 * @var into 
	 */ 
	private $into = null;

	/** 
	 * Query Values
	 * 
	 * @var values 
	 */ 
	private $values = null;

	/** 
	 * Query Values Flag
	 * 
	 * @var valuesflag 
	 */ 
	private $valuesflag = null;

	/** 
	 * Query Where
	 * 
	 * @var where 
	 */ 
	private $where = null;

	/** 
	 * Query Where Combination
	 * 
	 * @var combination 
	 */ 
	private $wherecombination = null;

	/** 
	 * Query Group
	 * 
	 * @var group 
	 */ 
	private $group = null;

	/** 
	 * Query Having
	 * 
	 * @var having 
	 */ 
	private $having = null;

	/** 
	 * Query Having Combination
	 * 
	 * @var havingcombination 
	 */ 
	private $havingcombination = null;

	/** 
	 * Query Order
	 * 
	 * @var order 
	 */ 
	private $order = null;

	/** 
	 * Query Limit
	 * 
	 * @var limit 
	 */ 
	private $limit = null;

	/** 
	 * Query Offset
	 * 
	 * @var offset 
	 */ 
	private $offset = null;

	/** 
	 * Query Affected Rows
	 * 
	 * @var affectedrows 
	 */ 
	private $affectedrows = null;

	/**
	 * SQL Statement
	 *
	 * @var sql
	 *
	 */
	private $sql = null;

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
			throw new Exception\RuntimeException('$action must be declare');
		}
		switch ($action) {
			case 'select':
				$this->select();
				break;
			case 'insert':
				$this->insert();
				break;
			case 'delete':
				$this->delete();
				break;
			case 'update':
				$this->update();
				break;
			default:
				throw new Exception\RuntimeException('$action invalid. select/insert/delete/update');
				break;
		}
		$this->prepare();
	}

	/**
	 * Select
	 * $action = select
	 */
	public function select() {
		$this->action = 'select';
	}

	/**
	 * Insert
	 * $action = insert
	 */
	public function insert() {
		$this->action = 'insert';
	}

	/**
	 * Delete
	 * $action = delete
	 */
	public function delete() {
		$this->action = 'delete';
	}

	/**
	 * Update
	 * $action = update
	 */
	public function update() {
		$this->action = 'update';
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
	public function columns(array $columns, $prefixColumnsWithTable = true) {
		if ($this->action == 'delete' || $this->action == 'update') {
			throw new Exception\RuntimeException('Delete/Insert is not allow to use columns function');
		}
		if (!is_array($columns)) {
			throw new Exception\RuntimeException('$columns must be a array');
		}
		$this->columns = $columns;
		$this->prefixColumnsWithTable = (bool) $prefixColumnsWithTable;
	}

	/**
	 * Create from clause
	 * Pass to Function table
	 * 
	 * @param  string|array|TableIdentifier $table
	 * @throws Exception
	 */

	public function from($from) {
		if (!$this->action == 'delete') {
			throw new Exception\RuntimeException('Only "delete" is allow to use group function');
		}
		$this->table($from);
	}

	/**
	 * Create into clause
	 * 
	 * @param  string|array|TableIdentifier $table
	 * @throws Exception
	 */
	public function into($into) {
		if (!$this->action == 'insert') {
			throw new Exception\RuntimeException('Only "insert" is allow to use group function');
		}
		$this->table($into);
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
	public function table($table) {
		if ($this->action == 'select') {
			if (!is_string($table) && !is_array($table) && !$table instanceof TableIdentifier) {
				throw new Exception\RuntimeException('$table must be a string, array, or an instance of TableIdentifier');
			}
			if (is_array($table) && (!is_string(key($table)) || count($table) !== 1)) {
				throw new Exception\RuntimeException('from() expects $table as an array is a single element associative array');
			}
		} elseif ($this->action == 'insert' || $this->action == 'delete' || $this->action == 'update') {
			if (!is_string($table) && !$table instanceof TableIdentifier) {
				throw new Exception\RuntimeException('$table must be a string, or an instance of TableIdentifier');
			}
		}
		$this->table = $table;
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
	public function join($join, $on, $columns = self::SQL_STAR, $type = self::JOIN_INNER) {
		if (!$this->action == 'select') {
			throw new Exception\RuntimeException('Only "select" is allow to use group function');
		}
		if (is_array($join) && (!is_string(key($join)) || count($join) !== 1)) {
			throw new Exception\RuntimeException(sprintf("join() expects '%s' as an array is a single element associative array", array_shift($join)));
		}
		$this->join[] = $join;
		$this->joinon[] = $on;
		$this->joincolumns[] = $columns;
		$this->jointype[] = $type;
	}

	/**
	 * Set key/value pairs to update
	 *
	 * @param  array $values Associative array of key values
	 * @param  string $flag One of the VALUES_* constants
	 * @throws Exception
	 */
	public function set(array $set, $flag = self::VALUES_SET) {
		if (!$this->action == 'update') {
			throw new Exception\RuntimeException('Only "update" is allow to use group function');
		}
		if ($set == null) {
			throw new Exception\RuntimeException('set() expects an array of values');
		}
		if (!is_array($set)) {
			throw new Exception\RuntimeException('$set must be a array');
		}
		foreach ($set as $k => $v) {
			if (!is_string($k)) {
				throw new Exception\RuntimeException('set() expects a string for the value key');
			}
		}
		$this->set = $set;
		$this->setflag = $flag;
	}

	/**
	 * Specify values to insert
	 *
	 * @param  array $values
	 * @param  string $flag one of VALUES_MERGE or VALUES_SET; defaults to VALUES_SET
	 * @throws Exception
	 */
	public function values(array $values, $flag = self::VALUES_SET) {
		if (!$this->action == 'insert') {
			throw new Exception\RuntimeException('Only "insert" is allow to use group function');
		}
		if ($values == null) {
			throw new Exception\RuntimeException('values() expects an array of values');
		}
		if (!is_array($values)) {
			throw new Exception\RuntimeException('$values must be a array');
		}
		$this->values = $values;
		$this->valuesflag = $flag;
	}

	/**
	 * Create where clause
	 *
	 * @param  Where|\Closure|string|array|Predicate\PredicateInterface $predicate
	 * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
	 * @throws Exception
	 */
	public function where($where, $combination = Predicate\PredicateSet::OP_AND) {
		if ($this->action == 'insert') {
			throw new Exception\RuntimeException('"insert" is not allow to use where function');
		}
		if (!$where instanceof Where && !$where instanceof \Closure && !$where instanceof Predicate\PredicateInterface && !is_string($where) && !is_array($where)) {
			throw new Exception\RuntimeException('$where must be a string, array, or an instance of Having or Closure or Predicate\PredicateInterface');
		}

		$this->where = $where;
		$this->wherecombination = $combination;
	}

	public function group($group) {
		if (!$this->action == 'select') {
			throw new Exception\RuntimeException('Only "select" is allow to use group function');
		}
		$this->group = $group;
	}

	/**
	 * Create where clause
	 *
	 * @param  Where|\Closure|string|array $predicate
	 * @param  string $combination One of the OP_* constants from Predicate\PredicateSet
	 */
	public function having($having, $combination = Predicate\PredicateSet::OP_AND) {
		if (!$this->action == 'select') {
			throw new Exception\RuntimeException('Only "select" is allow to use having function');
		}
		if (!$having instanceof Having && !$having instanceof \Closure && !is_string($having) && !is_array($having)) {
			throw new Exception\RuntimeException('$having must be a string, array, or an instance of Having or Closure');
		}
		$this->having = $having;
		$this->havingcombination = $combination;
	}

	/**
	 * @param string|array $order
	 */
	public function order($order) {
		if (!$this->action == 'select') {
			throw new Exception\RuntimeException('Only "select" is allow to use order function');
		}
		if (!is_string($order) && !is_array($order)) {
			throw new Exception\RuntimeException('$order must be a string, or array');
		}
		$this->order = $order;
	}

	/**
	 * @param int $limit
	 */
	public function limit($limit) {
		if (!$this->action == 'select') {
			throw new Exception\RuntimeException('Only "select" is allow to use limit function');
		}
		if (!is_int($limit)) {
			throw new Exception\RuntimeException('$limit must be a int');
		}
		$this->limit = (int) $limit;
	}

	/**
	 * @param int $offset
	 */
	public function offset($offset) {
		if (!$this->action == 'select') {
			throw new Exception\RuntimeException('Only "select" is allow to use offset function');
		}
		if (!is_int($offset)) {
			throw new Exception\RuntimeException('$offset must be a int');
		}
		$this->offset = $offset;
	}

	public function execute() {
		$this->prepareSQL();
		$this->prepare();
		if (is_object($this->sql) && is_object($this->adapter)) {
			$statement = $this->adapter->createStatement();
			$this->sql->prepareStatement($this->adapter, $statement);
			$resultdata = $statement->execute();
			if ($this->action == 'select') {
				$hasdata = false;
				if ($this->hasCacheName() && $this->cache->hasItem($this->cachename)) {
					$resultdata = $this->cache->getItem($this->cachename);
					$hasdata = true;
				} elseif ($resultdata instanceof ResultInterface && $resultdata->isQueryResult()) {
					$result = new ResultSet;
					$result->initialize($resultdata);
					$resultdata = $result->toArray();
					$hasdata = true;
				}
				if ($hasdata) {
					$this->setResult($resultdata);

					if ($this->hasCacheName() && !$this->cache->hasItem($this->cachename)) {
						$this->setCache($resultdata);
					}
				}
			} elseif ($this->action == 'delete' || $this->action == 'update' || $this->action == 'insert') {
				$this->affectedrows = $resultdata->getAffectedRows();

				if ($this->hasCacheName()) {
					$this->cache->clearByPrefix($this->cachename);
				}
			}
		}
		return null;
	}

	public function affectedRows() {
		if ($this->affectedrows > 0) {
			return true;
		}
		return false;
	}

	public function prepareSQL() {
		$sql = null;
		if ($this->action == 'select') {
			$sql = new \Zend\Db\Sql\Select;
			if (!empty($this->columns)) {
				$sql->columns($this->columns, $this->prefixColumnsWithTable);
			}
			if (!empty($this->table)) {
				$sql->from($this->table);
			}
			if (!empty($this->join) && is_array($this->join)) {
				$countjoin = count($this->join);
				for ($i = 0; $i < $countjoin; $i++) {
					$sql->join($this->join[$i], $this->joinon[$i], $this->joincolumns[$i], $this->jointype[$i]);
				}
			}
			if (!empty($this->where)) {
				$sql->where($this->where, $this->wherecombination);
			}
			if (!empty($this->group)) {
				$sql->group($this->group);
			}
			if (!empty($this->having)) {
				$sql->having($this->having, $this->havingcombination);
			}
			if (!empty($this->order)) {
				$sql->order($this->order);
			}
			if (!empty($this->limit)) {
				$sql->limit($this->limit);
			}
			if (!empty($this->offset)) {
				$sql->offset($this->offset);
			}
		} elseif ($this->action == 'insert') {
			$sql = new \Zend\Db\Sql\Insert;
			if (!empty($this->table)) {
				$sql->into($this->table);
			}
			if (!empty($this->columns)) {
				$sql->columns($this->columns);
			}
			if (!empty($this->values)) {
				$sql->values($this->values, $this->valuesflag);
			}
		} elseif ($this->action == 'delete') {
			$sql = new \Zend\Db\Sql\Delete;
			if (!empty($this->table)) {
				$sql->from($this->table);
			}
			if (!empty($this->where)) {
				$sql->where($this->where, $this->wherecombination);
			}
		} elseif ($this->action == 'update') {
			$sql = new \Zend\Db\Sql\Update;
			if (!empty($this->table)) {
				$sql->table($this->table);
			}
			if (!empty($this->set)) {
				$sql->set($this->set, $this->setflag);
			}
			if (!empty($this->where)) {
				$sql->where($this->where, $this->wherecombination);
			}
		}
		$this->sql = $sql;
		if (!is_object($this->sql)) {
			throw new Exception\RuntimeException('Zend\Db\Sql\\' . $this->action . ' object not found');
		}
		return $this->sql;
	}

	public function getSqlString() {
		$this->prepareSQL();
		if (is_object($this->sql)) {
			return $this->sql->getSqlString($this->adapter->getPlatform());
		}
		return null;
	}

	public function prepare() {
		$this->prepareAdapter();
		$this->prepareCache();
	}

	public function prepareAdapter() {
		$configfile = CORE_PATH . '/Config/autoload/db.global.php';
		if (!file_exists($configfile)) {
			throw new Exception\RuntimeException(sprintf('Db "%s" file not exist', $configfile));
		}
		$config = include $configfile;
		if (!is_array($config)) {
			throw new Exception\RuntimeException(sprintf('Db "%s" file configuration invalid', $configfile));
		}
		$options = $config['db'];
		$this->adapter = new Adapter($options);
		if (!$this->adapter instanceof Adapter) {
			throw new Exception\RuntimeException('Zend\Db\Adapter object not found');
		}
		return $this->adapter;
	}

	public function prepareCache() {
		$configfile = CORE_PATH . '/Config/autoload/cache.storage.global.php';
		if (!file_exists($configfile)) {
			throw new Exception\RuntimeException(sprintf('Cache "%s" file not exist', $configfile));
		}
		$config = include $configfile;
		if (!is_array($config)) {
			throw new Exception\RuntimeException(sprintf('Cache "%s" file configuration invalid', $configfile));
		}

		$options = $config['cachestorage']['filesystem'];
		$options['options']['namespace'] = 'database';
		$options['options']['namespace_separator'] = '_';

		$cache = StorageFactory::adapterFactory('filesystem');
		$cache->setOptions($options['options']);

		$pluginConfig = $options['plugins'];
		$plugin = false;
		$pluginName = null;
		$pluginOption = null;
		if (isset($pluginConfig['clearexpiredbyfactor'])) {
			$pluginName = 'clearexpiredbyfactor';
			$pluginOption = $pluginConfig['clearexpiredbyfactor'];
			$plugin = true;
		} elseif (isset($pluginConfig['exceptionhandler'])) {
			$pluginName = 'exceptionhandler';
			$pluginOption = $pluginConfig['exceptionhandler'];
			$plugin = true;
		} elseif (isset($pluginConfig['ignoreuserabort'])) {
			$pluginName = 'ignoreuserabort';
			$pluginOption = $pluginConfig['ignoreuserabort'];
			$plugin = true;
		} elseif (isset($pluginConfig['optimizebyfactor'])) {
			$pluginName = 'optimizebyfactor';
			$pluginOption = $pluginConfig['optimizebyfactor'];
			$plugin = true;
		} elseif (isset($pluginConfig['serializer'])) {
			$pluginName = 'serializer';
			$pluginOption = $pluginConfig['serializer'];
			$plugin = true;
		}
		if ($plugin) {
			$plugin = StorageFactory::pluginFactory($pluginName, $pluginOption);
			$cache->addPlugin($plugin);
		}
		$this->cache = $cache;
		if (!$this->cache instanceof Filesystem) {
			throw new Exception\RuntimeException('Zend\Cache object not found');
		}
		return $this->cache;
	}

	/**
	 * Set namespace.
	 *
	 * @param  string $namespace
	 */
	public function setCacheName($cachename = null) {
		$this->cachename = $cachename;
	}

	/**
	 * Set namespace.
	 *
	 * @param  string $namespace
	 */
	public function hasCacheName() {
		if (empty($this->cachename)) {
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
		if (empty($this->cachename)) {
			throw new Exception\RuntimeException('Zend\Cache namespace no defined');
		}
		if (!is_object($this->cache)) {
			throw new Exception\RuntimeException('Zend\Cache object not found');
		}
		$this->cache->setItem($this->cachename, $result);
	}

	public function clearCache($cachename = null) {
		$this->prepareCache();
		if ($this->hasCacheName()) {
			$this->cache->clearByPrefix($cachename);
		}
	}
}
