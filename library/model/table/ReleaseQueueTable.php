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
class ReleaseQueueTable extends TableObjectBase{
	/**  */
	var $id;
	/**  */
	var $package;
	/**  */
	var $maintainer;
	/**  */
	var $revision;
	/**  */
	var $buildPath;
	/**  */
	var $buildConf;
	/**  */
	var $mailPossible;
	/**  */
	var $created;
	var $factPackage;
	var $factMaintainer;


	function ReleaseQueueTable($id=null){
		$this->__init__($id);
	}
	function __init__($id=null){
		$this->id = null;
		$this->package = null;
		$this->maintainer = null;
		$this->revision = null;
		$this->buildPath = null;
		$this->buildConf = null;
		$this->mailPossible = 1;
		$this->created = time();
		$this->setId($id);
	}
	function connection(){
		if(!Rhaco::isVariable("_R_D_CON_","openpear")){
			Rhaco::addVariable("_R_D_CON_",new DbConnection("openpear"),"openpear");
		}
		return Rhaco::getVariable("_R_D_CON_",null,"openpear");
	}
	function table(){
		if(!Rhaco::isVariable("_R_D_T_","ReleaseQueue")){
			Rhaco::addVariable("_R_D_T_",new Table(Rhaco::constant("DATABASE_openpear_PREFIX")."release_queue",__CLASS__),"ReleaseQueue");
		}
		return Rhaco::getVariable("_R_D_T_",null,"ReleaseQueue");
	}


	/**
	 * 
	 * @return database.model.Column
	 */
	function columnId(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Id")){
			$column = new Column("column=id,variable=id,type=serial,size=22,primary=true,",__CLASS__);
			$column->label(Message::_("id"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Id");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Id");
	}
	/**
	 * 
	 * @return serial
	 */
	function setId($value){
		$this->id = TableObjectUtil::cast($value,"serial");
	}
	/**
	 * 
	 */
	function getId(){
		return $this->id;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPackage(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Package")){
			$column = new Column("column=package,variable=package,type=integer,size=22,reference=Package::Id,",__CLASS__);
			$column->label(Message::_("package"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Package");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Package");
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
	function columnMaintainer(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Maintainer")){
			$column = new Column("column=maintainer,variable=maintainer,type=integer,size=22,reference=Maintainer::Id,",__CLASS__);
			$column->label(Message::_("maintainer"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Maintainer");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Maintainer");
	}
	/**
	 * 
	 * @return integer
	 */
	function setMaintainer($value){
		$this->maintainer = TableObjectUtil::cast($value,"integer");
	}
	/**
	 * 
	 */
	function getMaintainer(){
		return $this->maintainer;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnRevision(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Revision")){
			$column = new Column("column=revision,variable=revision,type=integer,size=22,",__CLASS__);
			$column->label(Message::_("revision"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Revision");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Revision");
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
	function columnBuildPath(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::BuildPath")){
			$column = new Column("column=build_path,variable=buildPath,type=string,require=true,",__CLASS__);
			$column->label(Message::_("build_path"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::BuildPath");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::BuildPath");
	}
	/**
	 * 
	 * @return string
	 */
	function setBuildPath($value){
		$this->buildPath = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getBuildPath(){
		return $this->buildPath;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnBuildConf(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::BuildConf")){
			$column = new Column("column=build_conf,variable=buildConf,type=text,",__CLASS__);
			$column->label(Message::_("build_conf"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::BuildConf");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::BuildConf");
	}
	/**
	 * 
	 * @return text
	 */
	function setBuildConf($value){
		$this->buildConf = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getBuildConf(){
		return $this->buildConf;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnMailPossible(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::MailPossible")){
			$column = new Column("column=mail_possible,variable=mailPossible,type=boolean,",__CLASS__);
			$column->label(Message::_("mail_possible"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::MailPossible");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::MailPossible");
	}
	/**
	 * 
	 * @return boolean
	 */
	function setMailPossible($value){
		$this->mailPossible = TableObjectUtil::cast($value,"boolean");
	}
	/**
	 * 
	 */
	function getMailPossible(){
		return $this->mailPossible;
	}
	/**  */
	function isMailPossible(){
		return Variable::bool($this->mailPossible);
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnCreated(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Created")){
			$column = new Column("column=created,variable=created,type=timestamp,",__CLASS__);
			$column->label(Message::_("created"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Created");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Created");
	}
	/**
	 * 
	 * @return timestamp
	 */
	function setCreated($value){
		$this->created = TableObjectUtil::cast($value,"timestamp");
	}
	/**
	 * 
	 */
	function getCreated(){
		return $this->created;
	}
	/**  */
	function formatCreated($format="Y/m/d H:i:s"){
		return DateUtil::format($this->created,$format);
	}


	function getFactPackage(){
		return $this->factPackage;
	}
	function setFactPackage($obj){
		$this->factPackage = $obj;
	}
	function getFactMaintainer(){
		return $this->factMaintainer;
	}
	function setFactMaintainer($obj){
		$this->factMaintainer = $obj;
	}
}
?>