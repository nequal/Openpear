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
class ServerCategoriesTable extends TableObjectBase{
    /**  */
    var $name;
    /**  */
    var $desctn;


    function ServerCategoriesTable(){
        $this->__init__();
    }
    function __init__(){
        $this->name = null;
        $this->desctn = null;
    }
    function connection(){
        if(!Rhaco::isVariable("_R_D_CON_","server")){
            Rhaco::addVariable("_R_D_CON_",new DbConnection("server"),"server");
        }
        return Rhaco::getVariable("_R_D_CON_",null,"server");
    }
    function table(){
        if(!Rhaco::isVariable("_R_D_T_","ServerCategories")){
            Rhaco::addVariable("_R_D_T_",new Table(Rhaco::constant("DATABASE_server_PREFIX")."categories",__CLASS__),"ServerCategories");
        }
        return Rhaco::getVariable("_R_D_T_",null,"ServerCategories");
    }


    /**
     * 
     * @return database.model.Column
     */
    function columnName(){
        if(!Rhaco::isVariable("_R_D_C_","ServerCategories::Name")){
            $column = new Column("column=name,variable=name,type=string,",__CLASS__);
            $column->label(Message::_("name"));
            Rhaco::addVariable("_R_D_C_",$column,"ServerCategories::Name");
        }
        return Rhaco::getVariable("_R_D_C_",null,"ServerCategories::Name");
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
    function columnDesctn(){
        if(!Rhaco::isVariable("_R_D_C_","ServerCategories::Desctn")){
            $column = new Column("column=desctn,variable=desctn,type=text,",__CLASS__);
            $column->label(Message::_("desctn"));
            Rhaco::addVariable("_R_D_C_",$column,"ServerCategories::Desctn");
        }
        return Rhaco::getVariable("_R_D_C_",null,"ServerCategories::Desctn");
    }
    /**
     * 
     * @return text
     */
    function setDesctn($value){
        $this->desctn = TableObjectUtil::cast($value,"text");
    }
    /**
     * 
     */
    function getDesctn(){
        return $this->desctn;
    }


}
?>
