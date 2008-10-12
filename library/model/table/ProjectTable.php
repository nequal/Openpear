<?php
Rhaco::import("resources.Message");
Rhaco::import("database.model.TableObjectBase");
Rhaco::import("database.model.DbConnection");
Rhaco::import("model.Package");
Rhaco::import("database.TableObjectUtil");
Rhaco::import("database.model.Table");
Rhaco::import("database.model.Column");
/**
 * #ignore
 * 
 */
class ProjectTable extends Package{

	function table(){
		if(!Rhaco::isVariable("_R_D_T_","Project")){
			Rhaco::addVariable("_R_D_T_",new Table(parent::table(),__CLASS__),"Project");
		}
		return Rhaco::getVariable("_R_D_T_",null,"Project");
	}
	function columns(){
		return array(Project::columnId(),Project::columnName(),Project::columnDescription(),Project::columnPublic(),Project::columnLatestRelease(),Project::columnCreated(),Project::columnUpdated(),);
	}
	function columnId(){
		if(!Rhaco::isVariable("_R_D_C_","Project::Id")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnId(),__CLASS__),"Project::Id");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Project::Id");			
	}
	function columnName(){
		if(!Rhaco::isVariable("_R_D_C_","Project::Name")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnName(),__CLASS__),"Project::Name");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Project::Name");			
	}
	function columnDescription(){
		if(!Rhaco::isVariable("_R_D_C_","Project::Description")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnDescription(),__CLASS__),"Project::Description");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Project::Description");			
	}
	function columnPublic(){
		if(!Rhaco::isVariable("_R_D_C_","Project::Public")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnPublic(),__CLASS__),"Project::Public");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Project::Public");			
	}
	function columnLatestRelease(){
		if(!Rhaco::isVariable("_R_D_C_","Project::LatestRelease")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnLatestRelease(),__CLASS__),"Project::LatestRelease");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Project::LatestRelease");			
	}
	function columnCreated(){
		if(!Rhaco::isVariable("_R_D_C_","Project::Created")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnCreated(),__CLASS__),"Project::Created");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Project::Created");			
	}
	function columnUpdated(){
		if(!Rhaco::isVariable("_R_D_C_","Project::Updated")){
			Rhaco::addVariable("_R_D_C_",new Column(parent::columnUpdated(),__CLASS__),"Project::Updated");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Project::Updated");			
	}
}
?>