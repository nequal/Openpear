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
class TagPackageTable extends TableObjectBase{
	/**  */
	var $package;
	/**  */
	var $tag;
	var $factPackage;
	var $factTag;


	function TagPackageTable(){
		$this->__init__();
	}
	function __init__(){
		$this->package = null;
		$this->tag = null;
	}
	function connection(){
		if(!Rhaco::isVariable("_R_D_CON_","openpear")){
			Rhaco::addVariable("_R_D_CON_",new DbConnection("openpear"),"openpear");
		}
		return Rhaco::getVariable("_R_D_CON_",null,"openpear");
	}
	function table(){
		if(!Rhaco::isVariable("_R_D_T_","TagPackage")){
			Rhaco::addVariable("_R_D_T_",new Table(Rhaco::constant("DATABASE_openpear_PREFIX")."tag_package",__CLASS__),"TagPackage");
		}
		return Rhaco::getVariable("_R_D_T_",null,"TagPackage");
	}


	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPackage(){
		if(!Rhaco::isVariable("_R_D_C_","TagPackage::Package")){
			$column = new Column("column=package,variable=package,type=integer,size=22,require=true,unique=true,reference=Package::Id,uniqueWith=TagPackage::Tag,",__CLASS__);
			$column->label(Message::_("package"));
			Rhaco::addVariable("_R_D_C_",$column,"TagPackage::Package");
		}
		return Rhaco::getVariable("_R_D_C_",null,"TagPackage::Package");
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
	function columnTag(){
		if(!Rhaco::isVariable("_R_D_C_","TagPackage::Tag")){
			$column = new Column("column=tag,variable=tag,type=integer,size=22,require=true,unique=true,reference=Tag::Id,uniqueWith=TagPackage::Package,",__CLASS__);
			$column->label(Message::_("tag"));
			Rhaco::addVariable("_R_D_C_",$column,"TagPackage::Tag");
		}
		return Rhaco::getVariable("_R_D_C_",null,"TagPackage::Tag");
	}
	/**
	 * 
	 * @return integer
	 */
	function setTag($value){
		$this->tag = TableObjectUtil::cast($value,"integer");
	}
	/**
	 * 
	 */
	function getTag(){
		return $this->tag;
	}


	function getFactPackage(){
		return $this->factPackage;
	}
	function setFactPackage($obj){
		$this->factPackage = $obj;
	}
	function getFactTag(){
		return $this->factTag;
	}
	function setFactTag($obj){
		$this->factTag = $obj;
	}
}
?>