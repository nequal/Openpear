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
class MaintainerTable extends TableObjectBase{
	/**  */
	var $id;
	/**  */
	var $name;
	/**  */
	var $mail;
	/**  */
	var $fullname;
	/**  */
	var $profile;
	/**  */
	var $password;
	/**  */
	var $created;
	var $dependOpenIds;
	var $dependNewprojectQueues;
	var $dependReleaseQueues;
	var $dependFavorites;
	var $dependCharges;
	var $packages;


	function MaintainerTable($id=null){
		$this->__init__($id);
	}
	function __init__($id=null){
		$this->id = null;
		$this->name = null;
		$this->mail = null;
		$this->fullname = null;
		$this->profile = null;
		$this->password = null;
		$this->created = time();
		$this->setId($id);
	}
	function connection(){
		if(!Rhaco::isVariable("_R_D_CON_","openpear")){
			Rhaco::addVariable("_R_D_CON_",new DbConnection("openpear"),"openpear");
		}
		return Rhaco::getVariable("_R_D_CON_",null,"openpear");
	}
	function table(){
		if(!Rhaco::isVariable("_R_D_T_","Maintainer")){
			Rhaco::addVariable("_R_D_T_",new Table(Rhaco::constant("DATABASE_openpear_PREFIX")."maintainer",__CLASS__),"Maintainer");
		}
		return Rhaco::getVariable("_R_D_T_",null,"Maintainer");
	}


	/**
	 * 
	 * @return database.model.Column
	 */
	function columnId(){
		if(!Rhaco::isVariable("_R_D_C_","Maintainer::Id")){
			$column = new Column("column=id,variable=id,type=serial,size=22,primary=true,",__CLASS__);
			$column->label(Message::_("id"));
			$column->depend("OpenId::Maintainer","NewprojectQueue::Maintainer","ReleaseQueue::Maintainer","Favorite::Maintainer","Charge::Maintainer");
			Rhaco::addVariable("_R_D_C_",$column,"Maintainer::Id");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Maintainer::Id");
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
	function columnName(){
		if(!Rhaco::isVariable("_R_D_C_","Maintainer::Name")){
			$column = new Column("column=name,variable=name,type=string,max=20,require=true,unique=true,chartype=/^[A-Za-z0-9_\-]+$/,",__CLASS__);
			$column->label(Message::_("name"));
			Rhaco::addVariable("_R_D_C_",$column,"Maintainer::Name");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Maintainer::Name");
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
	function columnMail(){
		if(!Rhaco::isVariable("_R_D_C_","Maintainer::Mail")){
			$column = new Column("column=mail,variable=mail,type=email,size=255,",__CLASS__);
			$column->label(Message::_("mail"));
			Rhaco::addVariable("_R_D_C_",$column,"Maintainer::Mail");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Maintainer::Mail");
	}
	/**
	 * 
	 * @return email
	 */
	function setMail($value){
		$this->mail = TableObjectUtil::cast($value,"email");
	}
	/**
	 * 
	 */
	function getMail(){
		return $this->mail;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnFullname(){
		if(!Rhaco::isVariable("_R_D_C_","Maintainer::Fullname")){
			$column = new Column("column=fullname,variable=fullname,type=string,max=30,",__CLASS__);
			$column->label(Message::_("fullname"));
			Rhaco::addVariable("_R_D_C_",$column,"Maintainer::Fullname");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Maintainer::Fullname");
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
	function columnProfile(){
		if(!Rhaco::isVariable("_R_D_C_","Maintainer::Profile")){
			$column = new Column("column=profile,variable=profile,type=text,",__CLASS__);
			$column->label(Message::_("profile"));
			Rhaco::addVariable("_R_D_C_",$column,"Maintainer::Profile");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Maintainer::Profile");
	}
	/**
	 * 
	 * @return text
	 */
	function setProfile($value){
		$this->profile = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getProfile(){
		return $this->profile;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPassword(){
		if(!Rhaco::isVariable("_R_D_C_","Maintainer::Password")){
			$column = new Column("column=password,variable=password,type=string,require=true,",__CLASS__);
			$column->label(Message::_("password"));
			Rhaco::addVariable("_R_D_C_",$column,"Maintainer::Password");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Maintainer::Password");
	}
	/**
	 * 
	 * @return string
	 */
	function setPassword($value){
		$this->password = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getPassword(){
		return $this->password;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnCreated(){
		if(!Rhaco::isVariable("_R_D_C_","Maintainer::Created")){
			$column = new Column("column=created,variable=created,type=timestamp,",__CLASS__);
			$column->label(Message::_("created"));
			Rhaco::addVariable("_R_D_C_",$column,"Maintainer::Created");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Maintainer::Created");
	}
	/**
	 * 
	 * @return timestamp
	 */
	function setCreated($value){
		$this->created = TableObjectUtil::cast($value,"timestamp");
	}
	/**
	 * 
	 */
	function getCreated(){
		return $this->created;
	}
	/**  */
	function formatCreated($format="Y/m/d H:i:s"){
		return DateUtil::format($this->created,$format);
	}


	function setDependOpenIds($value){
		$this->dependOpenIds = $value;
	}
	function getDependOpenIds(){
		return $this->dependOpenIds;
	}
	function setDependNewprojectQueues($value){
		$this->dependNewprojectQueues = $value;
	}
	function getDependNewprojectQueues(){
		return $this->dependNewprojectQueues;
	}
	function setDependReleaseQueues($value){
		$this->dependReleaseQueues = $value;
	}
	function getDependReleaseQueues(){
		return $this->dependReleaseQueues;
	}
	function setDependFavorites($value){
		$this->dependFavorites = $value;
	}
	function getDependFavorites(){
		return $this->dependFavorites;
	}
	function setDependCharges($value){
		$this->dependCharges = $value;
	}
	function getDependCharges(){
		return $this->dependCharges;
	}
	function setPackages($value){
		$this->packages = $value;
	}
	function getPackages(){
		return $this->packages;
	}
}
?>