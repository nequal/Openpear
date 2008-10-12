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
class ServerMaintainersTable extends TableObjectBase{
	/**  */
	var $hundle;
	/**  */
	var $fullname;
	/**  */
	var $email;
	/**  */
	var $stats;


	function ServerMaintainersTable(){
		$this->__init__();
	}
	function __init__(){
		$this->hundle = null;
		$this->fullname = null;
		$this->email = null;
		$this->stats = null;
	}
	function connection(){
		if(!Rhaco::isVariable("_R_D_CON_","server")){
			Rhaco::addVariable("_R_D_CON_",new DbConnection("server"),"server");
		}
		return Rhaco::getVariable("_R_D_CON_",null,"server");
	}
	function table(){
		if(!Rhaco::isVariable("_R_D_T_","ServerMaintainers")){
			Rhaco::addVariable("_R_D_T_",new Table(Rhaco::constant("DATABASE_server_PREFIX")."maintainers",__CLASS__),"ServerMaintainers");
		}
		return Rhaco::getVariable("_R_D_T_",null,"ServerMaintainers");
	}


	/**
	 * 
	 * @return database.model.Column
	 */
	function columnHundle(){
		if(!Rhaco::isVariable("_R_D_C_","ServerMaintainers::Hundle")){
			$column = new Column("column=hundle,variable=hundle,type=string,size=30,",__CLASS__);
			$column->label(Message::_("hundle"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerMaintainers::Hundle");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerMaintainers::Hundle");
	}
	/**
	 * 
	 * @return string
	 */
	function setHundle($value){
		$this->hundle = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getHundle(){
		return $this->hundle;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnFullname(){
		if(!Rhaco::isVariable("_R_D_C_","ServerMaintainers::Fullname")){
			$column = new Column("column=fullname,variable=fullname,type=string,size=100,",__CLASS__);
			$column->label(Message::_("fullname"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerMaintainers::Fullname");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerMaintainers::Fullname");
	}
	/**
	 * 
	 * @return string
	 */
	function setFullname($value){
		$this->fullname = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getFullname(){
		return $this->fullname;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnEmail(){
		if(!Rhaco::isVariable("_R_D_C_","ServerMaintainers::Email")){
			$column = new Column("column=email,variable=email,type=string,size=100,",__CLASS__);
			$column->label(Message::_("email"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerMaintainers::Email");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerMaintainers::Email");
	}
	/**
	 * 
	 * @return string
	 */
	function setEmail($value){
		$this->email = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getEmail(){
		return $this->email;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnStats(){
		if(!Rhaco::isVariable("_R_D_C_","ServerMaintainers::Stats")){
			$column = new Column("column=stats,variable=stats,type=string,size=10,",__CLASS__);
			$column->label(Message::_("stats"));
			Rhaco::addVariable("_R_D_C_",$column,"ServerMaintainers::Stats");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ServerMaintainers::Stats");
	}
	/**
	 * 
	 * @return string
	 */
	function setStats($value){
		$this->stats = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getStats(){
		return $this->stats;
	}


}
?>