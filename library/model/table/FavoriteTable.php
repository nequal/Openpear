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
    var $package;
    /**  */
    var $maintainer;
    var $factPackage;
    var $factMaintainer;


    function FavoriteTable(){
        $this->__init__();
    }
    function __init__(){
        $this->package = null;
        $this->maintainer = null;
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
    function columnPackage(){
        if(!Rhaco::isVariable("_R_D_C_","Favorite::Package")){
            $column = new Column("column=package,variable=package,type=integer,size=22,unique=true,reference=Package::Id,uniqueWith=Favorite::Maintainer,",__CLASS__);
            $column->label(Message::_("package"));
            Rhaco::addVariable("_R_D_C_",$column,"Favorite::Package");
        }
        return Rhaco::getVariable("_R_D_C_",null,"Favorite::Package");
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
        if(!Rhaco::isVariable("_R_D_C_","Favorite::Maintainer")){
            $column = new Column("column=maintainer,variable=maintainer,type=integer,size=22,unique=true,reference=Maintainer::Id,uniqueWith=Favorite::Package,",__CLASS__);
            $column->label(Message::_("maintainer"));
            Rhaco::addVariable("_R_D_C_",$column,"Favorite::Maintainer");
        }
        return Rhaco::getVariable("_R_D_C_",null,"Favorite::Maintainer");
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
