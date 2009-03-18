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
class ReleaseQueueTable extends TableObjectBase{
	/**  */
	var $id;
	/**  */
	var $package;
	/**  */
	var $maintainer;
	/**  */
	var $revision;
	/**  */
	var $buildPath;
	/**  */
	var $mailPossible;
	/**  */
	var $created;
	/**  */
	var $projectSrcDir;
	/**  */
	var $projectReleaseDir;
	/**  */
	var $packagePackageName;
	/**  */
	var $packagePackageType;
	/**  */
	var $packageBaseinstalldir;
	/**  */
	var $packageChannel;
	/**  */
	var $packageSummary;
	/**  */
	var $packageDescription;
	/**  */
	var $packageNotes;
	/**  */
	var $versionReleaseVer;
	/**  */
	var $versionReleaseStab;
	/**  */
	var $versionApiVer;
	/**  */
	var $versionApiVapiStab;
	/**  */
	var $versionPhpMin;
	/**  */
	var $versionPearMin;
	/**  */
	var $licenseName;
	/**  */
	var $licenseUri;
	/**  */
	var $role;
	/**  */
	var $file;
	/**  */
	var $dep;
	/**  */
	var $installer;
	var $packageSummaryFile;
	var $packageDescriptionFile;
	var $packageNotesFile;
	var $factPackage;
	var $factMaintainer;


	function ReleaseQueueTable($id=null){
		$this->__init__($id);
	}
	function __init__($id=null){
		$this->id = null;
		$this->package = null;
		$this->maintainer = null;
		$this->revision = null;
		$this->buildPath = null;
		$this->mailPossible = 1;
		$this->created = time();
		$this->projectSrcDir = "src";
		$this->projectReleaseDir = "release";
		$this->packagePackageName = null;
		$this->packagePackageType = "php";
		$this->packageBaseinstalldir = null;
		$this->packageChannel = "openpear.org";
		$this->packageSummary = null;
		$this->packageDescription = null;
		$this->packageNotes = null;
		$this->versionReleaseVer = "1.0.0";
		$this->versionReleaseStab = "stable";
		$this->versionApiVer = "1.0.0";
		$this->versionApiVapiStab = "stable";
		$this->versionPhpMin = "5.2.0";
		$this->versionPearMin = "1.7.2";
		$this->licenseName = "New BSD Licence";
		$this->licenseUri = "http://creativecommons.org/licenses/BSD/";
		$this->role = null;
		$this->file = null;
		$this->dep = null;
		$this->installer = null;
		$this->packageSummaryFile = "summary.txt";
		$this->packageDescriptionFile = "desc.txt";
		$this->packageNotesFile = "notes.txt";
		$this->setId($id);
	}
	function connection(){
		if(!Rhaco::isVariable("_R_D_CON_","openpear")){
			Rhaco::addVariable("_R_D_CON_",new DbConnection("openpear"),"openpear");
		}
		return Rhaco::getVariable("_R_D_CON_",null,"openpear");
	}
	function table(){
		if(!Rhaco::isVariable("_R_D_T_","ReleaseQueue")){
			Rhaco::addVariable("_R_D_T_",new Table(Rhaco::constant("DATABASE_openpear_PREFIX")."release_queue",__CLASS__),"ReleaseQueue");
		}
		return Rhaco::getVariable("_R_D_T_",null,"ReleaseQueue");
	}


	/**
	 * 
	 * @return database.model.Column
	 */
	function columnId(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Id")){
			$column = new Column("column=id,variable=id,type=serial,size=22,primary=true,",__CLASS__);
			$column->label(Message::_("id"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Id");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Id");
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
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Package")){
			$column = new Column("column=package,variable=package,type=integer,size=22,reference=Package::Id,",__CLASS__);
			$column->label(Message::_("package"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Package");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Package");
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
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Maintainer")){
			$column = new Column("column=maintainer,variable=maintainer,type=integer,size=22,reference=Maintainer::Id,",__CLASS__);
			$column->label(Message::_("maintainer"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Maintainer");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Maintainer");
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
	function columnRevision(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Revision")){
			$column = new Column("column=revision,variable=revision,type=integer,size=22,",__CLASS__);
			$column->label(Message::_("revision"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Revision");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Revision");
	}
	/**
	 * 
	 * @return integer
	 */
	function setRevision($value){
		$this->revision = TableObjectUtil::cast($value,"integer");
	}
	/**
	 * 
	 */
	function getRevision(){
		return $this->revision;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnBuildPath(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::BuildPath")){
			$column = new Column("column=build_path,variable=buildPath,type=string,require=true,",__CLASS__);
			$column->label(Message::_("build_path"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::BuildPath");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::BuildPath");
	}
	/**
	 * 
	 * @return string
	 */
	function setBuildPath($value){
		$this->buildPath = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getBuildPath(){
		return $this->buildPath;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnMailPossible(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::MailPossible")){
			$column = new Column("column=mail_possible,variable=mailPossible,type=boolean,",__CLASS__);
			$column->label(Message::_("mail_possible"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::MailPossible");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::MailPossible");
	}
	/**
	 * 
	 * @return boolean
	 */
	function setMailPossible($value){
		$this->mailPossible = TableObjectUtil::cast($value,"boolean");
	}
	/**
	 * 
	 */
	function getMailPossible(){
		return $this->mailPossible;
	}
	/**  */
	function isMailPossible(){
		return Variable::bool($this->mailPossible);
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnCreated(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Created")){
			$column = new Column("column=created,variable=created,type=timestamp,",__CLASS__);
			$column->label(Message::_("created"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Created");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Created");
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
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnProjectSrcDir(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::ProjectSrcDir")){
			$column = new Column("column=project_src_dir,variable=projectSrcDir,type=string,chartype=/^[A-Za-z0-9\.\/\_\-]+$/,",__CLASS__);
			$column->label(Message::_("[プロジェクト]ソース格納先"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::ProjectSrcDir");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::ProjectSrcDir");
	}
	/**
	 * 
	 * @return string
	 */
	function setProjectSrcDir($value){
		$this->projectSrcDir = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getProjectSrcDir(){
		return $this->projectSrcDir;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnProjectReleaseDir(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::ProjectReleaseDir")){
			$column = new Column("column=project_release_dir,variable=projectReleaseDir,type=string,chartype=/^[A-Za-z0-9\.\/\_\-]+$/,",__CLASS__);
			$column->label(Message::_("[プロジェクト]パッケージ格納先"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::ProjectReleaseDir");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::ProjectReleaseDir");
	}
	/**
	 * 
	 * @return string
	 */
	function setProjectReleaseDir($value){
		$this->projectReleaseDir = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getProjectReleaseDir(){
		return $this->projectReleaseDir;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPackagePackageName(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::PackagePackageName")){
			$column = new Column("column=package_package_name,variable=packagePackageName,type=string,require=true,",__CLASS__);
			$column->label(Message::_("[パッケージ]パッケージ名"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::PackagePackageName");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::PackagePackageName");
	}
	/**
	 * 
	 * @return string
	 */
	function setPackagePackageName($value){
		$this->packagePackageName = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getPackagePackageName(){
		return $this->packagePackageName;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPackagePackageType(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::PackagePackageType")){
			$column = new Column("column=package_package_type,variable=packagePackageType,type=string,",__CLASS__);
			$column->label(Message::_("[パッケージ]パッケージタイプ"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::PackagePackageType");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::PackagePackageType");
	}
	/**
	 * 
	 * @return string
	 */
	function setPackagePackageType($value){
		$this->packagePackageType = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getPackagePackageType(){
		return $this->packagePackageType;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPackageBaseinstalldir(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::PackageBaseinstalldir")){
			$column = new Column("column=package_baseinstalldir,variable=packageBaseinstalldir,type=string,chartype=/^[A-Za-z0-9\.\/\_\-]+$/,",__CLASS__);
			$column->label(Message::_("[パッケージ]インストール先"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::PackageBaseinstalldir");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::PackageBaseinstalldir");
	}
	/**
	 * 
	 * @return string
	 */
	function setPackageBaseinstalldir($value){
		$this->packageBaseinstalldir = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getPackageBaseinstalldir(){
		return $this->packageBaseinstalldir;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPackageChannel(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::PackageChannel")){
			$column = new Column("column=package_channel,variable=packageChannel,type=string,",__CLASS__);
			$column->label(Message::_("[パッケージ]チャンネル"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::PackageChannel");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::PackageChannel");
	}
	/**
	 * 
	 * @return string
	 */
	function setPackageChannel($value){
		$this->packageChannel = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getPackageChannel(){
		return $this->packageChannel;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPackageSummary(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::PackageSummary")){
			$column = new Column("column=package_summary,variable=packageSummary,type=text,",__CLASS__);
			$column->label(Message::_("[パッケージ]概要"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::PackageSummary");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::PackageSummary");
	}
	/**
	 * 
	 * @return text
	 */
	function setPackageSummary($value){
		$this->packageSummary = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getPackageSummary(){
		return $this->packageSummary;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPackageDescription(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::PackageDescription")){
			$column = new Column("column=package_description,variable=packageDescription,type=text,",__CLASS__);
			$column->label(Message::_("[パッケージ]詳細説明"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::PackageDescription");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::PackageDescription");
	}
	/**
	 * 
	 * @return text
	 */
	function setPackageDescription($value){
		$this->packageDescription = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getPackageDescription(){
		return $this->packageDescription;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPackageNotes(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::PackageNotes")){
			$column = new Column("column=package_notes,variable=packageNotes,type=text,",__CLASS__);
			$column->label(Message::_("[パッケージ]更新履歴"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::PackageNotes");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::PackageNotes");
	}
	/**
	 * 
	 * @return text
	 */
	function setPackageNotes($value){
		$this->packageNotes = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getPackageNotes(){
		return $this->packageNotes;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnVersionReleaseVer(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::VersionReleaseVer")){
			$column = new Column("column=version_release_ver,variable=versionReleaseVer,type=string,chartype=/^[0-9\.]+$/,",__CLASS__);
			$column->label(Message::_("[バージョン]リリースバージョン"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::VersionReleaseVer");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::VersionReleaseVer");
	}
	/**
	 * 
	 * @return string
	 */
	function setVersionReleaseVer($value){
		$this->versionReleaseVer = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getVersionReleaseVer(){
		return $this->versionReleaseVer;
	}
	/**
	 * Choices
	 * 	stable: stable 
	 * 	beta: beta 
	 * 	alpha: alpha 
	 * 
	 * @return database.model.Column
	 */
	function columnVersionReleaseStab(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::VersionReleaseStab")){
			$column = new Column("column=version_release_stab,variable=versionReleaseStab,type=string,",__CLASS__);
			$column->label(Message::_("[バージョン]リリース状態"));
			$column->choices(array("stable"=>Message::_("stable"),"beta"=>Message::_("beta"),"alpha"=>Message::_("alpha"),));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::VersionReleaseStab");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::VersionReleaseStab");
	}
	/**
	 * Choices
	 * 	stable: stable 
	 * 	beta: beta 
	 * 	alpha: alpha 
	 * 
	 * @return string
	 */
	function setVersionReleaseStab($value){
		$this->versionReleaseStab = TableObjectUtil::cast($value,"string");
	}
	/**
	 * Choices
	 * 	stable: stable 
	 * 	beta: beta 
	 * 	alpha: alpha 
	 * 
	 */
	function getVersionReleaseStab(){
		return $this->versionReleaseStab;
	}
	function captionVersionReleaseStab(){
		return TableObjectUtil::caption($this,ReleaseQueue::columnVersionReleaseStab());
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnVersionApiVer(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::VersionApiVer")){
			$column = new Column("column=version_api_ver,variable=versionApiVer,type=string,chartype=/^[0-9\.]+$/,",__CLASS__);
			$column->label(Message::_("[バージョン]APIバージョン"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::VersionApiVer");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::VersionApiVer");
	}
	/**
	 * 
	 * @return string
	 */
	function setVersionApiVer($value){
		$this->versionApiVer = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getVersionApiVer(){
		return $this->versionApiVer;
	}
	/**
	 * Choices
	 * 	stable: stable 
	 * 	beta: beta 
	 * 	alpha: alpha 
	 * 
	 * @return database.model.Column
	 */
	function columnVersionApiVapiStab(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::VersionApiVapiStab")){
			$column = new Column("column=version_api_vapi_stab,variable=versionApiVapiStab,type=string,",__CLASS__);
			$column->label(Message::_("[バージョン]API状態"));
			$column->choices(array("stable"=>Message::_("stable"),"beta"=>Message::_("beta"),"alpha"=>Message::_("alpha"),));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::VersionApiVapiStab");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::VersionApiVapiStab");
	}
	/**
	 * Choices
	 * 	stable: stable 
	 * 	beta: beta 
	 * 	alpha: alpha 
	 * 
	 * @return string
	 */
	function setVersionApiVapiStab($value){
		$this->versionApiVapiStab = TableObjectUtil::cast($value,"string");
	}
	/**
	 * Choices
	 * 	stable: stable 
	 * 	beta: beta 
	 * 	alpha: alpha 
	 * 
	 */
	function getVersionApiVapiStab(){
		return $this->versionApiVapiStab;
	}
	function captionVersionApiVapiStab(){
		return TableObjectUtil::caption($this,ReleaseQueue::columnVersionApiVapiStab());
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnVersionPhpMin(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::VersionPhpMin")){
			$column = new Column("column=version_php_min,variable=versionPhpMin,type=string,chartype=/^[0-9\.]+$/,",__CLASS__);
			$column->label(Message::_("[バージョン]PHPバージョン（最小）"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::VersionPhpMin");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::VersionPhpMin");
	}
	/**
	 * 
	 * @return string
	 */
	function setVersionPhpMin($value){
		$this->versionPhpMin = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getVersionPhpMin(){
		return $this->versionPhpMin;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnVersionPearMin(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::VersionPearMin")){
			$column = new Column("column=version_pear_min,variable=versionPearMin,type=string,chartype=/^[0-9\.]+$/,",__CLASS__);
			$column->label(Message::_("[バージョン]PEARインストーラ（最小）"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::VersionPearMin");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::VersionPearMin");
	}
	/**
	 * 
	 * @return string
	 */
	function setVersionPearMin($value){
		$this->versionPearMin = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getVersionPearMin(){
		return $this->versionPearMin;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnLicenseName(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::LicenseName")){
			$column = new Column("column=license_name,variable=licenseName,type=string,",__CLASS__);
			$column->label(Message::_("[ライセンス]ライセンス名"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::LicenseName");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::LicenseName");
	}
	/**
	 * 
	 * @return string
	 */
	function setLicenseName($value){
		$this->licenseName = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getLicenseName(){
		return $this->licenseName;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnLicenseUri(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::LicenseUri")){
			$column = new Column("column=license_uri,variable=licenseUri,type=string,",__CLASS__);
			$column->label(Message::_("[ライセンス]ライセンス参照先"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::LicenseUri");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::LicenseUri");
	}
	/**
	 * 
	 * @return string
	 */
	function setLicenseUri($value){
		$this->licenseUri = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getLicenseUri(){
		return $this->licenseUri;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnRole(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Role")){
			$column = new Column("column=role,variable=role,type=text,",__CLASS__);
			$column->label(Message::_("[拡張子]拡張子毎のタイプの指定"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Role");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Role");
	}
	/**
	 * 
	 * @return text
	 */
	function setRole($value){
		$this->role = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getRole(){
		return $this->role;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnFile(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::File")){
			$column = new Column("column=file,variable=file,type=text,",__CLASS__);
			$column->label(Message::_("[ファイル]"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::File");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::File");
	}
	/**
	 * 
	 * @return text
	 */
	function setFile($value){
		$this->file = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getFile(){
		return $this->file;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnDep(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Dep")){
			$column = new Column("column=dep,variable=dep,type=text,",__CLASS__);
			$column->label(Message::_("[パッケージ依存関係]"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Dep");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Dep");
	}
	/**
	 * 
	 * @return text
	 */
	function setDep($value){
		$this->dep = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getDep(){
		return $this->dep;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnInstaller(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::Installer")){
			$column = new Column("column=installer,variable=installer,type=text,",__CLASS__);
			$column->label(Message::_("[インストーラ]"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::Installer");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::Installer");
	}
	/**
	 * 
	 * @return text
	 */
	function setInstaller($value){
		$this->installer = TableObjectUtil::cast($value,"text");
	}
	/**
	 * 
	 */
	function getInstaller(){
		return $this->installer;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function extraPackageSummaryFile(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::PackageSummaryFile")){
			$column = new Column("column=package_summary_file,variable=packageSummaryFile,type=string,",__CLASS__);
			$column->label(Message::_("[パッケージ]概要(ファイル)"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::PackageSummaryFile");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::PackageSummaryFile");
	}
	/**
	 * 
	 * @return string
	 */
	function setPackageSummaryFile($value){
		$this->packageSummaryFile = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getPackageSummaryFile(){
		return $this->packageSummaryFile;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function extraPackageDescriptionFile(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::PackageDescriptionFile")){
			$column = new Column("column=package_description_file,variable=packageDescriptionFile,type=string,",__CLASS__);
			$column->label(Message::_("[パッケージ]詳細説明(ファイル)"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::PackageDescriptionFile");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::PackageDescriptionFile");
	}
	/**
	 * 
	 * @return string
	 */
	function setPackageDescriptionFile($value){
		$this->packageDescriptionFile = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getPackageDescriptionFile(){
		return $this->packageDescriptionFile;
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function extraPackageNotesFile(){
		if(!Rhaco::isVariable("_R_D_C_","ReleaseQueue::PackageNotesFile")){
			$column = new Column("column=package_notes_file,variable=packageNotesFile,type=string,",__CLASS__);
			$column->label(Message::_("[パッケージ]更新履歴(ファイル)"));
			Rhaco::addVariable("_R_D_C_",$column,"ReleaseQueue::PackageNotesFile");
		}
		return Rhaco::getVariable("_R_D_C_",null,"ReleaseQueue::PackageNotesFile");
	}
	/**
	 * 
	 * @return string
	 */
	function setPackageNotesFile($value){
		$this->packageNotesFile = TableObjectUtil::cast($value,"string");
	}
	/**
	 * 
	 */
	function getPackageNotesFile(){
		return $this->packageNotesFile;
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