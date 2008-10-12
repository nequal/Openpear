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
class FavoriteTable extends TableObjectBase{
	/**  */
	var $p;
	/**  */
	var $m;
	var $factP;
	var $factM;


	function FavoriteTable(){
		$this->__init__();
	}
	function __init__(){
		$this->p = null;
		$this->m = null;
	}
	function connection(){
		if(!Rhaco::isVariable("_R_D_CON_","openpear")){
			Rhaco::addVariable("_R_D_CON_",new DbConnection("openpear"),"openpear");
		}
		return Rhaco::getVariable("_R_D_CON_",null,"openpear");
	}
	function table(){
		if(!Rhaco::isVariable("_R_D_T_","Favorite")){
			Rhaco::addVariable("_R_D_T_",new Table(Rhaco::constant("DATABASE_openpear_PREFIX")."favorite",__CLASS__),"Favorite");
		}
		return Rhaco::getVariable("_R_D_T_",null,"Favorite");
	}


	/**
	 * 
	 * @return database.model.Column
	 */
	function columnP(){
		if(!Rhaco::isVariable("_R_D_C_","Favorite::P")){
			$column = new Column("column=package,variable=p,type=integer,size=22,unique=true,reference=Package::Id,uniqueWith=Favorite::M,",__CLASS__);
			$column->label(Message::_("package"));
			Rhaco::addVariable("_R_D_C_",$column,"Favorite::P");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Favorite::P");
	}
	/**
	 * 
	 * @return integer
	 */
	function setP($value){
		$this->p = TableObjectUtil::cast($value,"integer");
	}
	/**
	 * 
	 */
	function getP(){
		return $this->p;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnM(){
		if(!Rhaco::isVariable("_R_D_C_","Favorite::M")){
			$column = new Column("column=maintainer,variable=m,type=integer,size=22,unique=true,reference=Maintainer::Id,uniqueWith=Favorite::P,",__CLASS__);
			$column->label(Message::_("maintainer"));
			Rhaco::addVariable("_R_D_C_",$column,"Favorite::M");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Favorite::M");
	}
	/**
	 * 
	 * @return integer
	 */
	function setM($value){
		$this->m = TableObjectUtil::cast($value,"integer");
	}
	/**
	 * 
	 */
	function getM(){
		return $this->m;
	}


	function getFactP(){
		return $this->factP;
	}
	function setFactP($obj){
		$this->factP = $obj;
	}
	function getFactM(){
		return $this->factM;
	}
	function setFactM($obj){
		$this->factM = $obj;
	}
}
?>