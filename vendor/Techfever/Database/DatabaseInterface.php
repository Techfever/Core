<?php
namespace Techfever\Database;

interface DatabaseInterface {

	public function select();

	public function insert();

	public function delete();

	public function update();

	public function columns(array $columns, $prefixColumnsWithTable);

	public function from($from);

	public function into($into);

	public function table($table);

	public function join($join, $on, $columns, $type);

	public function set(array $set, $flag);

	public function values(array $values, $flag);

	public function where($where, $combination);

	public function group($group);

	public function having($having, $combination);

	public function order($order);

	public function limit($limit);

	public function offset($offset);

	public function execute();

	public function affectedRows();

	public function getLastGeneratedValue();

	public function getSqlString();

	public function setCacheName($cachename = null);

	public function hasCacheName();

	public function setCache($result);

	public function clearCache($cachename = null);
}
