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
class ChargeTable extends TableObjectBase{
	/**  */
	var $id;
	/**  */
	var $package;
	/**  */
	var $maintainer;
	/**  */
	var $role;
	var $factPackage;
	var $factMaintainer;


	function ChargeTable($id=null){
		$this->__init__($id);
	}
	function __init__($id=null){
		$this->id = null;
		$this->package = null;
		$this->maintainer = null;
		$this->role = null;
		$this->setId($id);
	}
	function connection(){
		if(!Rhaco::isVariable("_R_D_CON_","openpear")){
			Rhaco::addVariable("_R_D_CON_",new DbConnection("openpear"),"openpear");
		}
		return Rhaco::getVariable("_R_D_CON_",null,"openpear");
	}
	function table(){
		if(!Rhaco::isVariable("_R_D_T_","Charge")){
			Rhaco::addVariable("_R_D_T_",new Table(Rhaco::constant("DATABASE_openpear_PREFIX")."charge",__CLASS__),"Charge");
		}
		return Rhaco::getVariable("_R_D_T_",null,"Charge");
	}


	/**
	 * 
	 * @return database.model.Column
	 */
	function columnId(){
		if(!Rhaco::isVariable("_R_D_C_","Charge::Id")){
			$column = new Column("column=id,variable=id,type=serial,size=22,primary=true,",__CLASS__);
			$column->label(Message::_("id"));
			Rhaco::addVariable("_R_D_C_",$column,"Charge::Id");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Charge::Id");
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
		if(!Rhaco::isVariable("_R_D_C_","Charge::Package")){
			$column = new Column("column=package,variable=package,type=integer,size=22,unique=true,reference=Package::Id,uniqueWith=Charge::Maintainer,",__CLASS__);
			$column->label(Message::_("package"));
			Rhaco::addVariable("_R_D_C_",$column,"Charge::Package");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Charge::Package");
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
		if(!Rhaco::isVariable("_R_D_C_","Charge::Maintainer")){
			$column = new Column("column=maintainer,variable=maintainer,type=integer,size=22,unique=true,reference=Maintainer::Id,uniqueWith=Charge::Package,",__CLASS__);
			$column->label(Message::_("maintainer"));
			Rhaco::addVariable("_R_D_C_",$column,"Charge::Maintainer");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Charge::Maintainer");
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
	 * Choices
	 * 	lead: lead 
	 * 	developer: developer 
	 * 	contributor: contributor 
	 * 	helper: helper 
	 * 
	 * @return database.model.Column
	 */
	function columnRole(){
		if(!Rhaco::isVariable("_R_D_C_","Charge::Role")){
			$column = new Column("column=role,variable=role,type=string,",__CLASS__);
			$column->label(Message::_("role"));
			$column->choices(array("lead"=>Message::_("lead"),"developer"=>Message::_("developer"),"contributor"=>Message::_("contributor"),"helper"=>Message::_("helper"),));
			Rhaco::addVariable("_R_D_C_",$column,"Charge::Role");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Charge::Role");
	}
	/**
	 * Choices
	 * 	lead: lead 
	 * 	developer: developer 
	 * 	contributor: contributor 
	 * 	helper: helper 
	 * 
	 * @return string
	 */
	function setRole($value){
		$this->role = TableObjectUtil::cast($value,"string");
	}
	/**
	 * Choices
	 * 	lead: lead 
	 * 	developer: developer 
	 * 	contributor: contributor 
	 * 	helper: helper 
	 * 
	 */
	function getRole(){
		return $this->role;
	}
	function captionRole(){
		return TableObjectUtil::caption($this,Charge::columnRole());
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