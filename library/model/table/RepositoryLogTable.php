<?php
Rhaco::import("resources.Message");
Rhaco::import("database.model.TableObjectBase");
Rhaco::import("database.model.DbConnection");
Rhaco::import("database.TableObjectUtil");
Rhaco::import("database.model.Table");
Rhaco::import("database.model.Column");
/**
 * #ignore
 * 
 */
class RepositoryLogTable extends TableObjectBase{
	/**  */
	var $revision;
	/**  */
	var $author;
	/**  */
	var $package;
	/**  */
	var $log;
	/**  */
	var $diff;
	/**  */
	var $date;
	var $factPackage;


	function RepositoryLogTable(){
		$this->__init__();
	}
	function __init__(){
		$this->revision = null;
		$this->author = null;
		$this->package = null;
		$this->log = null;
		$this->diff = null;
		$this->date = null;
	}
	function connection(){
		if(!Rhaco::isVariable("_R_D_CON_","openpear")){
			Rhaco::addVariable("_R_D_CON_",new DbConnection("openpear"),"openpear");
		}
		return Rhaco::getVariable("_R_D_CON_",null,"openpear");
	}
	function table(){
		if(!Rhaco::isVariable("_R_D_T_","RepositoryLog")){
			Rhaco::addVariable("_R_D_T_",new Table(Rhaco::constant("DATABASE_openpear_PREFIX")."repository_log",__CLASS__),"RepositoryLog");
		}
		return Rhaco::getVariable("_R_D_T_",null,"RepositoryLog");
	}


	/**
	 * 
	 * @return database.model.Column
	 */
	function columnRevision(){
		if(!Rhaco::isVariable("_R_D_C_","RepositoryLog::Revision")){
			$column = new Column("column=revision,variable=revision,type=integer,size=22,",__CLASS__);
			$column->label(Message::_("revision"));
			Rhaco::addVariable("_R_D_C_",$column,"RepositoryLog::Revision");
		}
		return Rhaco::getVariable("_R_D_C_",null,"RepositoryLog::Revision");
	}
	/**
	 * 
	 * @return integer
	 */
	function setRevision($value){
		$this->revision = TableObjectUtil::cast($value,"integer");
	}
	/**
	 * 
	 */
	function getRevision(){
		return $this->revision;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnAuthor(){
		if(!Rhaco::isVariable("_R_D_C_","RepositoryLog::Author")){
			$column = new Column("column=author,variable=author,type=string,",__CLASS__);
			$column->label(Message::_("author"));
			Rhaco::addVariable("_R_D_C_",$column,"RepositoryLog::Author");
		}
		return Rhaco::getVariable("_R_D_C_",null,"RepositoryLog::Author");
	}
	/**
	 * 
	 * @return string
	 */
	function setAuthor($value){
		$this->author = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getAuthor(){
		return $this->author;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPackage(){
		if(!Rhaco::isVariable("_R_D_C_","RepositoryLog::Package")){
			$column = new Column("column=package,variable=package,type=integer,size=22,reference=Package::Id,",__CLASS__);
			$column->label(Message::_("package"));
			Rhaco::addVariable("_R_D_C_",$column,"RepositoryLog::Package");
		}
		return Rhaco::getVariable("_R_D_C_",null,"RepositoryLog::Package");
	}
	/**
	 * 
	 * @return integer
	 */
	function setPackage($value){
		$this->package = TableObjectUtil::cast($value,"integer");
	}
	/**
	 * 
	 */
	function getPackage(){
		return $this->package;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnLog(){
		if(!Rhaco::isVariable("_R_D_C_","RepositoryLog::Log")){
			$column = new Column("column=log,variable=log,type=text,",__CLASS__);
			$column->label(Message::_("log"));
			Rhaco::addVariable("_R_D_C_",$column,"RepositoryLog::Log");
		}
		return Rhaco::getVariable("_R_D_C_",null,"RepositoryLog::Log");
	}
	/**
	 * 
	 * @return text
	 */
	function setLog($value){
		$this->log = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getLog(){
		return $this->log;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnDiff(){
		if(!Rhaco::isVariable("_R_D_C_","RepositoryLog::Diff")){
			$column = new Column("column=diff,variable=diff,type=text,",__CLASS__);
			$column->label(Message::_("diff"));
			Rhaco::addVariable("_R_D_C_",$column,"RepositoryLog::Diff");
		}
		return Rhaco::getVariable("_R_D_C_",null,"RepositoryLog::Diff");
	}
	/**
	 * 
	 * @return text
	 */
	function setDiff($value){
		$this->diff = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getDiff(){
		return $this->diff;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnDate(){
		if(!Rhaco::isVariable("_R_D_C_","RepositoryLog::Date")){
			$column = new Column("column=date,variable=date,type=timestamp,",__CLASS__);
			$column->label(Message::_("date"));
			Rhaco::addVariable("_R_D_C_",$column,"RepositoryLog::Date");
		}
		return Rhaco::getVariable("_R_D_C_",null,"RepositoryLog::Date");
	}
	/**
	 * 
	 * @return timestamp
	 */
	function setDate($value){
		$this->date = TableObjectUtil::cast($value,"timestamp");
	}
	/**
	 * 
	 */
	function getDate(){
		return $this->date;
	}
	/**  */
	function formatDate($format="Y/m/d H:i:s"){
		return DateUtil::format($this->date,$format);
	}


	function getFactPackage(){
		return $this->factPackage;
	}
	function setFactPackage($obj){
		$this->factPackage = $obj;
	}
}
?>