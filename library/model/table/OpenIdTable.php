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
class OpenIdTable extends TableObjectBase{
	/**  */
	var $maintainer;
	/**  */
	var $url;
	var $factMaintainer;


	function OpenIdTable(){
		$this->__init__();
	}
	function __init__(){
		$this->maintainer = null;
		$this->url = null;
	}
	function connection(){
		if(!Rhaco::isVariable("_R_D_CON_","openpear")){
			Rhaco::addVariable("_R_D_CON_",new DbConnection("openpear"),"openpear");
		}
		return Rhaco::getVariable("_R_D_CON_",null,"openpear");
	}
	function table(){
		if(!Rhaco::isVariable("_R_D_T_","OpenId")){
			Rhaco::addVariable("_R_D_T_",new Table(Rhaco::constant("DATABASE_openpear_PREFIX")."open_id",__CLASS__),"OpenId");
		}
		return Rhaco::getVariable("_R_D_T_",null,"OpenId");
	}


	/**
	 * 
	 * @return database.model.Column
	 */
	function columnMaintainer(){
		if(!Rhaco::isVariable("_R_D_C_","OpenId::Maintainer")){
			$column = new Column("column=maintainer,variable=maintainer,type=integer,size=22,unique=true,reference=Maintainer::Id,uniqueWith=OpenId::Url,",__CLASS__);
			$column->label(Message::_("maintainer"));
			Rhaco::addVariable("_R_D_C_",$column,"OpenId::Maintainer");
		}
		return Rhaco::getVariable("_R_D_C_",null,"OpenId::Maintainer");
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
	function columnUrl(){
		if(!Rhaco::isVariable("_R_D_C_","OpenId::Url")){
			$column = new Column("column=url,variable=url,type=string,require=true,unique=true,uniqueWith=OpenId::Maintainer,",__CLASS__);
			$column->label(Message::_("url"));
			Rhaco::addVariable("_R_D_C_",$column,"OpenId::Url");
		}
		return Rhaco::getVariable("_R_D_C_",null,"OpenId::Url");
	}
	/**
	 * 
	 * @return string
	 */
	function setUrl($value){
		$this->url = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getUrl(){
		return $this->url;
	}


	function getFactMaintainer(){
		return $this->factMaintainer;
	}
	function setFactMaintainer($obj){
		$this->factMaintainer = $obj;
	}
}
?>