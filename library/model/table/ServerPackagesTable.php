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
class ServerPackagesTable extends TableObjectBase{
	/**  */
	var $channel;
	/**  */
	var $date;
	/**  */
	var $dependencies;
	/**  */
	var $description;
	/**  */
	var $licenselocation;
	/**  */
	var $maintainers;
	/**  */
	var $name;
	/**  */
	var $notes;
	/**  */
	var $package;
	/**  */
	var $stability;
	/**  */
	var $state;
	/**  */
	var $summary;
	/**  */
	var $version;
	/**  */
	var $filesize;
	/**  */
	var $contents;


	function ServerPackagesTable(){
		$this->__init__();
	}
	function __init__(){
		$this->channel = null;
		$this->date = null;
		$this->dependencies = null;
		$this->description = null;
		$this->licenselocation = null;
		$this->maintainers = null;
		$this->name = null;
		$this->notes = null;
		$this->package = null;
		$this->stability = null;
		$this->state = null;
		$this->summary = null;
		$this->version = null;
		$this->filesize = null;
		$this->contents = null;
	}
	function connection(){
		if(!Rhaco::isVariable("_R_D_CON_","server")){
			Rhaco::addVariable("_R_D_CON_",new DbConnection("server"),"server");
		}
		return Rhaco::getVariable("_R_D_CON_",null,"server");
	}
	function table(){
		if(!Rhaco::isVariable("_R_D_T_","ServerPackages")){
			Rhaco::addVariable("_R_D_T_",new Table(Rhaco::constant("DATABASE_server_PREFIX")."packages",__CLASS__),"ServerPackages");
		}
		return Rhaco::getVariable("_R_D_T_",null,"ServerPackages");
	}


	/**
	 * 
	 * @return database.model.Column
	 */
	function columnChannel(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Channel")){
			$column = new Column("column=channel,variable=channel,type=string,size=100,",__CLASS__);
			$column->label(Message::_("channel"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Channel");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Channel");
	}
	/**
	 * 
	 * @return string
	 */
	function setChannel($value){
		$this->channel = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getChannel(){
		return $this->channel;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnDate(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Date")){
			$column = new Column("column=date,variable=date,type=string,size=30,",__CLASS__);
			$column->label(Message::_("date"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Date");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Date");
	}
	/**
	 * 
	 * @return string
	 */
	function setDate($value){
		$this->date = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getDate(){
		return $this->date;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnDependencies(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Dependencies")){
			$column = new Column("column=dependencies,variable=dependencies,type=text,",__CLASS__);
			$column->label(Message::_("dependencies"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Dependencies");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Dependencies");
	}
	/**
	 * 
	 * @return text
	 */
	function setDependencies($value){
		$this->dependencies = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getDependencies(){
		return $this->dependencies;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnDescription(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Description")){
			$column = new Column("column=description,variable=description,type=text,",__CLASS__);
			$column->label(Message::_("description"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Description");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Description");
	}
	/**
	 * 
	 * @return text
	 */
	function setDescription($value){
		$this->description = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getDescription(){
		return $this->description;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnLicenselocation(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Licenselocation")){
			$column = new Column("column=licenselocation,variable=licenselocation,type=text,",__CLASS__);
			$column->label(Message::_("licenselocation"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Licenselocation");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Licenselocation");
	}
	/**
	 * 
	 * @return text
	 */
	function setLicenselocation($value){
		$this->licenselocation = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getLicenselocation(){
		return $this->licenselocation;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnMaintainers(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Maintainers")){
			$column = new Column("column=maintainers,variable=maintainers,type=text,",__CLASS__);
			$column->label(Message::_("maintainers"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Maintainers");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Maintainers");
	}
	/**
	 * 
	 * @return text
	 */
	function setMaintainers($value){
		$this->maintainers = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getMaintainers(){
		return $this->maintainers;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnName(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Name")){
			$column = new Column("column=name,variable=name,type=string,size=100,",__CLASS__);
			$column->label(Message::_("name"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Name");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Name");
	}
	/**
	 * 
	 * @return string
	 */
	function setName($value){
		$this->name = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getName(){
		return $this->name;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnNotes(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Notes")){
			$column = new Column("column=notes,variable=notes,type=text,",__CLASS__);
			$column->label(Message::_("notes"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Notes");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Notes");
	}
	/**
	 * 
	 * @return text
	 */
	function setNotes($value){
		$this->notes = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getNotes(){
		return $this->notes;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPackage(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Package")){
			$column = new Column("column=package,variable=package,type=string,size=100,",__CLASS__);
			$column->label(Message::_("package"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Package");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Package");
	}
	/**
	 * 
	 * @return string
	 */
	function setPackage($value){
		$this->package = TableObjectUtil::cast($value,"string");
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
	function columnStability(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Stability")){
			$column = new Column("column=stability,variable=stability,type=text,",__CLASS__);
			$column->label(Message::_("stability"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Stability");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Stability");
	}
	/**
	 * 
	 * @return text
	 */
	function setStability($value){
		$this->stability = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getStability(){
		return $this->stability;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnState(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::State")){
			$column = new Column("column=state,variable=state,type=string,size=10,",__CLASS__);
			$column->label(Message::_("state"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::State");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::State");
	}
	/**
	 * 
	 * @return string
	 */
	function setState($value){
		$this->state = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getState(){
		return $this->state;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnSummary(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Summary")){
			$column = new Column("column=summary,variable=summary,type=text,",__CLASS__);
			$column->label(Message::_("summary"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Summary");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Summary");
	}
	/**
	 * 
	 * @return text
	 */
	function setSummary($value){
		$this->summary = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getSummary(){
		return $this->summary;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnVersion(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Version")){
			$column = new Column("column=version,variable=version,type=string,size=10,",__CLASS__);
			$column->label(Message::_("version"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Version");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Version");
	}
	/**
	 * 
	 * @return string
	 */
	function setVersion($value){
		$this->version = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getVersion(){
		return $this->version;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnFilesize(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Filesize")){
			$column = new Column("column=filesize,variable=filesize,type=string,size=10,",__CLASS__);
			$column->label(Message::_("filesize"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Filesize");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Filesize");
	}
	/**
	 * 
	 * @return string
	 */
	function setFilesize($value){
		$this->filesize = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getFilesize(){
		return $this->filesize;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnContents(){
		if(!Rhaco::isVariable("_R_D_C_","ServerPackages::Contents")){
			$column = new Column("column=contents,variable=contents,type=text,",__CLASS__);
			$column->label(Message::_("contents"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerPackages::Contents");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerPackages::Contents");
	}
	/**
	 * 
	 * @return text
	 */
	function setContents($value){
		$this->contents = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getContents(){
		return $this->contents;
	}


}
?>