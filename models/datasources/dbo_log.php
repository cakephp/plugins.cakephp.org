<?php
// load lib
App::import('Datasource', 'DboSource');
App::import('Datasource', 'DboMysql');
/**
* @author RainChen @ Sun Feb 24 17:21:35 CST 2008
* @uses usage
* @link http://cakeexplorer.wordpress.com/2007/10/08/extending-of-dbosource-and-model-with-sql-generator-function/
* @access public
* @param parameter
* @return return
* @version 0.1
*/ 
class DboLog extends DboMysql {
	function logQuery($sql) {
		$return = parent::logQuery($sql);
		$this->log("sql[$this->_queriesCnt]:".$sql, 'sql');
		return $return;
	}
}
?>