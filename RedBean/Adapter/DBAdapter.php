<?php
/**
 * DBAdapter		(Database Adapter)
 * @file			RedBean/Adapter/DBAdapter.php
 * @desc			An adapter class to connect various database systems to RedBean
 * @author			Gabor de Mooij and the RedBeanPHP Community. 
 * @license			BSD/GPLv2
 *
 * (c) copyright G.J.G.T. (Gabor) de Mooij and the RedBeanPHP community.
 * This source file is subject to the BSD/GPLv2 License that is bundled
 * with this source code in the file license.txt.
 */
class RedBean_Adapter_DBAdapter extends RedBean_Observable implements RedBean_Adapter {
	/**
	 * ADODB compatible class
	 * @var RedBean_Driver
	 */
	private $db = null;
	/**
	 * Contains SQL snippet
	 * @var string
	 */
	private $sql = '';
	/**
	 * Constructor.
	 * Creates an instance of the RedBean Adapter Class.
	 * This class provides an interface for RedBean to work
	 * with ADO compatible DB instances.
	 *
	 * @param RedBean_Driver $database ADO Compatible DB Instance
	 */
	public function __construct($database) {
		$this->db = $database;
	}
	/**
	 * Returns the latest SQL Statement.
	 * This method returns the most recently executed SQL statement string.
	 * This can be used for building logging features.
	 *
	 * @return string $SQL latest SQL statement
	 */
	public function getSQL() {
		return $this->sql;
	}
	/**
	 * Escapes a string for use in a Query.
	 * This method escapes the value argument using the native
	 * driver escaping functions.
	 *
	 * @param  string $sqlvalue SQL value to escape
	 *
	 * @return string $escapedValue escaped value
	 */
	public function escape($sqlvalue) {
		return $this->db->Escape($sqlvalue);
	}
	/**
	 * Executes SQL code; any query without
	 * returning a resultset.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL.
	 *
	 * @param  string  $sql			SQL Code to execute
	 * @param  array   $values		assoc. array binding values
	 * @param  boolean $noevent   if TRUE this will suppress the event 'sql_exec'
	 *
	 * @return mixed  $undefSet	whatever driver returns, undefined
	 */
	public function exec($sql , $aValues=array(), $noevent=false) {
		if (!$noevent) {
			$this->sql = $sql;
			$this->signal('sql_exec', $this);
		}
		return $this->db->Execute($sql, $aValues);
	}
	/**
	 * Multi array SQL fetch. Fetches a multi dimensional array.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL.
	 *
	 * @param  string $sql		SQL code to execute
	 * @param  array  $values	assoc. array binding values
	 *
	 * @return array  $result	two dimensional array result set
	 */
	public function get($sql, $aValues = array()) {
		$this->sql = $sql;
		$this->signal('sql_exec', $this);
		return $this->db->GetAll($sql, $aValues);
	}
	/**
	 * Executes SQL and fetches a single row.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL.
	 *
	 * @param  string $sql		SQL code to execute
	 * @param  array  $values	assoc. array binding values
	 *
	 * @return array	$result	one dimensional array result set
	 */
	public function getRow($sql, $aValues = array()) {
		$this->sql = $sql;
		$this->signal('sql_exec', $this);
		return $this->db->GetRow($sql, $aValues);
	}
	/**
	 * Executes SQL and returns a one dimensional array result set.
	 * This function rotates the result matrix to obtain a column result set.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL.
	 *
	 * @param  string $sql		SQL code to execute
	 * @param  array  $values	assoc. array binding values
	 *
	 * @return array  $result	one dimensional array result set
	 */
	public function getCol($sql, $aValues = array()) {
		$this->sql = $sql;
		$this->signal('sql_exec', $this);
		return $this->db->GetCol( $sql,$aValues );
	}
	/**
	 * Executes an SQL Query and fetches the first two columns only.
	 * Then this function builds an associative array using the first
	 * column for the keys and the second result column for the
	 * values. For instance: SELECT id, name FROM... will produce
	 * an array like: id => name.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL.
	 *
	 * @param  string $sql		SQL code to execute
	 * @param  array  $values	assoc. array binding values
	 *
	 * @return array  $result	multi dimensional assoc. array result set
	 */
	public function getAssoc($sql, $aValues = array()) {
		$this->sql = $sql;
		$this->signal('sql_exec', $this);
		$rows = $this->db->GetAll( $sql, $aValues );
		$assoc = array();
		if ($rows) {
			foreach($rows as $row) {
				if (is_array($row) && count($row)>0) {
					if (count($row)>1) {
						$key = array_shift($row);
						$value = array_shift($row);
					}
					elseif (count($row)==1) {
						$key = array_shift($row);
						$value=$key;
					}
					$assoc[$key] = $value;
				}
			}
		}
		return $assoc;
	}
	/**
	 * Retrieves a single cell.
	 * This function allows you to provide an array with values to bind
	 * to query parameters. For instance you can bind values to question
	 * marks in the query. Each value in the array corresponds to the
	 * question mark in the query that matches the position of the value in the
	 * array. You can also bind values using explicit keys, for instance
	 * array(":key"=>123) will bind the integer 123 to the key :key in the
	 * SQL.
	 *
	 * @param  string $sql	  sql code to execute
	 * @param  array  $values assoc. array binding values
	 *
	 * @return array  $result scalar result set
	 */
	public function getCell($sql, $aValues = array(), $noSignal = null) {
		$this->sql = $sql;
		if (!$noSignal) $this->signal('sql_exec', $this);
		$arr = $this->db->getCol( $sql, $aValues );
		if ($arr && is_array($arr))	return ($arr[0]); else return false;
	}
	/**
	 * Returns latest insert id, most recently inserted id.
	 * Following an insert-SQL statement this method will return the most recently
	 * primary key ID of an inserted record.
	 *
	 * @return integer $id latest insert ID
	 */
	public function getInsertID() {
		return $this->db->getInsertID();
	}
	/**
	 * Returns number of affected rows.
	 * Returns the number of rows that have been affected by the most recent
	 * SQL query.
	 *
	 * @return integer $numOfAffectRows
	 */
	public function getAffectedRows() {
		return $this->db->Affected_Rows();
	}
	/**
	 * Unwrap the original database object.
	 * Returns the database driver instance. For instance this can be
	 * an OCI object or a PDO instance or some other third party driver.
	 *
	 * @return RedBean_Driver $database	returns the inner database object
	 */
	public function getDatabase() {
		return $this->db;
	}
	/**
	 * Transactions.
	 * Part of the transaction management infrastructure of RedBeanPHP.
	 * Starts a transaction.
	 * Note that transactions may not work in fluid mode depending on your 
	 * database platform.
	 */
	public function startTransaction() {
		return $this->db->StartTrans();
	}
	/**
	 * Transactions.
	 * Part of the transaction management infrastructure of RedBeanPHP.
	 * Commits a transaction.
	 * Note that transactions may not work in fluid mode depending on your 
	 * database platform.
	 */
	public function commit() {
		return $this->db->CommitTrans();
	}
	/**
	 * Transactions.
	 * Part of the transaction management infrastructure of RedBeanPHP.
	 * Rolls back transaction. This will undo all changes that have been
	 * part of the transaction.
	 * Note that transactions may not work in fluid mode depending on your 
	 * database platform.
	 */
	public function rollback() {
		return $this->db->FailTrans();
	}
	/**
	 * Closes the database connection.
	 */
	public function close() {
		$this->db->close();
	}
}