<?php
Rhaco::import("resources.Message");
Rhaco::import("database.model.TableObjectBase");
Rhaco::import("database.model.DbConnection");
Rhaco::import("model.Maintainer");
Rhaco::import("database.TableObjectUtil");
Rhaco::import("database.model.Table");
Rhaco::import("database.model.Column");
/**
 * #ignore
 * 
 */
class UserTable extends Maintainer{

	function table(){
		if(!Rhaco::isVariable("_R_D_T_","User")){
			Rhaco::addVariable("_R_D_T_",new Table(parent::table(),__CLASS__),"User");
		}
		return Rhaco::getVariable("_R_D_T_",null,"User");
	}
	function columns(){
		return array(User::columnId(),User::columnName(),User::columnMail(),User::columnFullname(),User::columnProfile(),User::columnPassword(),User::columnCreated(),);
	}
	function columnId(){
		if(!Rhaco::isVariable("_R_D_C_","User::Id")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnId(),__CLASS__),"User::Id");
		}
		return Rhaco::getVariable("_R_D_C_",null,"User::Id");			
	}
	function columnName(){
		if(!Rhaco::isVariable("_R_D_C_","User::Name")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnName(),__CLASS__),"User::Name");
		}
		return Rhaco::getVariable("_R_D_C_",null,"User::Name");			
	}
	function columnMail(){
		if(!Rhaco::isVariable("_R_D_C_","User::Mail")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnMail(),__CLASS__),"User::Mail");
		}
		return Rhaco::getVariable("_R_D_C_",null,"User::Mail");			
	}
	function columnFullname(){
		if(!Rhaco::isVariable("_R_D_C_","User::Fullname")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnFullname(),__CLASS__),"User::Fullname");
		}
		return Rhaco::getVariable("_R_D_C_",null,"User::Fullname");			
	}
	function columnProfile(){
		if(!Rhaco::isVariable("_R_D_C_","User::Profile")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnProfile(),__CLASS__),"User::Profile");
		}
		return Rhaco::getVariable("_R_D_C_",null,"User::Profile");			
	}
	function columnPassword(){
		if(!Rhaco::isVariable("_R_D_C_","User::Password")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnPassword(),__CLASS__),"User::Password");
		}
		return Rhaco::getVariable("_R_D_C_",null,"User::Password");			
	}
	function columnCreated(){
		if(!Rhaco::isVariable("_R_D_C_","User::Created")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnCreated(),__CLASS__),"User::Created");
		}
		return Rhaco::getVariable("_R_D_C_",null,"User::Created");			
	}
}
?>