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
class ReleaseTable extends TableObjectBase{
	/**  */
	var $package;
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


	function ReleaseTable($package=null){
		$this->__init__($package);
	}
	function __init__($package=null){
		$this->package = null;
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
		$this->setPackage($package);
	}
	function connection(){
		if(!Rhaco::isVariable("_R_D_CON_","openpear")){
			Rhaco::addVariable("_R_D_CON_",new DbConnection("openpear"),"openpear");
		}
		return Rhaco::getVariable("_R_D_CON_",null,"openpear");
	}
	function table(){
		if(!Rhaco::isVariable("_R_D_T_","Release")){
			Rhaco::addVariable("_R_D_T_",new Table(Rhaco::constant("DATABASE_openpear_PREFIX")."release",__CLASS__),"Release");
		}
		return Rhaco::getVariable("_R_D_T_",null,"Release");
	}


	/**
	 * 
	 * @return database.model.Column
	 */
	function columnPackage(){
		if(!Rhaco::isVariable("_R_D_C_","Release::Package")){
			$column = new Column("column=package,variable=package,type=integer,size=22,primary=true,reference=Package::Id,",__CLASS__);
			$column->label(Message::_("package"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::Package");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::Package");
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
	function columnProjectSrcDir(){
		if(!Rhaco::isVariable("_R_D_C_","Release::ProjectSrcDir")){
			$column = new Column("column=project_src_dir,variable=projectSrcDir,type=string,chartype=/^[A-Za-z0-9\.\/\_\-]+$/,",__CLASS__);
			$column->label(Message::_("[プロジェクト]ソース格納先"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::ProjectSrcDir");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::ProjectSrcDir");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::ProjectReleaseDir")){
			$column = new Column("column=project_release_dir,variable=projectReleaseDir,type=string,chartype=/^[A-Za-z0-9\.\/\_\-]+$/,",__CLASS__);
			$column->label(Message::_("[プロジェクト]パッケージ格納先"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::ProjectReleaseDir");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::ProjectReleaseDir");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::PackagePackageName")){
			$column = new Column("column=package_package_name,variable=packagePackageName,type=string,require=true,",__CLASS__);
			$column->label(Message::_("[パッケージ]パッケージ名"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::PackagePackageName");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::PackagePackageName");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::PackagePackageType")){
			$column = new Column("column=package_package_type,variable=packagePackageType,type=string,",__CLASS__);
			$column->label(Message::_("[パッケージ]パッケージタイプ"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::PackagePackageType");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::PackagePackageType");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::PackageBaseinstalldir")){
			$column = new Column("column=package_baseinstalldir,variable=packageBaseinstalldir,type=string,chartype=/^[A-Za-z0-9\.\/\_\-]+$/,",__CLASS__);
			$column->label(Message::_("[パッケージ]インストール先"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::PackageBaseinstalldir");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::PackageBaseinstalldir");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::PackageChannel")){
			$column = new Column("column=package_channel,variable=packageChannel,type=string,",__CLASS__);
			$column->label(Message::_("[パッケージ]チャンネル"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::PackageChannel");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::PackageChannel");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::PackageSummary")){
			$column = new Column("column=package_summary,variable=packageSummary,type=text,",__CLASS__);
			$column->label(Message::_("[パッケージ]概要"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::PackageSummary");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::PackageSummary");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::PackageDescription")){
			$column = new Column("column=package_description,variable=packageDescription,type=text,",__CLASS__);
			$column->label(Message::_("[パッケージ]詳細説明"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::PackageDescription");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::PackageDescription");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::PackageNotes")){
			$column = new Column("column=package_notes,variable=packageNotes,type=text,",__CLASS__);
			$column->label(Message::_("[パッケージ]更新履歴"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::PackageNotes");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::PackageNotes");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::VersionReleaseVer")){
			$column = new Column("column=version_release_ver,variable=versionReleaseVer,type=string,chartype=/^[0-9\.]+$/,",__CLASS__);
			$column->label(Message::_("[バージョン]リリースバージョン"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::VersionReleaseVer");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::VersionReleaseVer");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::VersionReleaseStab")){
			$column = new Column("column=version_release_stab,variable=versionReleaseStab,type=string,",__CLASS__);
			$column->label(Message::_("[バージョン]リリース状態"));
			$column->choices(array("stable"=>Message::_("stable"),"beta"=>Message::_("beta"),"alpha"=>Message::_("alpha"),));
			Rhaco::addVariable("_R_D_C_",$column,"Release::VersionReleaseStab");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::VersionReleaseStab");
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
		return TableObjectUtil::caption($this,Release::columnVersionReleaseStab());
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnVersionApiVer(){
		if(!Rhaco::isVariable("_R_D_C_","Release::VersionApiVer")){
			$column = new Column("column=version_api_ver,variable=versionApiVer,type=string,chartype=/^[0-9\.]+$/,",__CLASS__);
			$column->label(Message::_("[バージョン]APIバージョン"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::VersionApiVer");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::VersionApiVer");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::VersionApiVapiStab")){
			$column = new Column("column=version_api_vapi_stab,variable=versionApiVapiStab,type=string,",__CLASS__);
			$column->label(Message::_("[バージョン]API状態"));
			$column->choices(array("stable"=>Message::_("stable"),"beta"=>Message::_("beta"),"alpha"=>Message::_("alpha"),));
			Rhaco::addVariable("_R_D_C_",$column,"Release::VersionApiVapiStab");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::VersionApiVapiStab");
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
		return TableObjectUtil::caption($this,Release::columnVersionApiVapiStab());
	}
	/**
	 * 
	 * @return database.model.Column
	 */
	function columnVersionPhpMin(){
		if(!Rhaco::isVariable("_R_D_C_","Release::VersionPhpMin")){
			$column = new Column("column=version_php_min,variable=versionPhpMin,type=string,chartype=/^[0-9\.]+$/,",__CLASS__);
			$column->label(Message::_("[バージョン]PHPバージョン（最小）"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::VersionPhpMin");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::VersionPhpMin");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::VersionPearMin")){
			$column = new Column("column=version_pear_min,variable=versionPearMin,type=string,chartype=/^[0-9\.]+$/,",__CLASS__);
			$column->label(Message::_("[バージョン]PEARインストーラ（最小）"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::VersionPearMin");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::VersionPearMin");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::LicenseName")){
			$column = new Column("column=license_name,variable=licenseName,type=string,",__CLASS__);
			$column->label(Message::_("[ライセンス]ライセンス名"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::LicenseName");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::LicenseName");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::LicenseUri")){
			$column = new Column("column=license_uri,variable=licenseUri,type=string,",__CLASS__);
			$column->label(Message::_("[ライセンス]ライセンス参照先"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::LicenseUri");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::LicenseUri");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::Role")){
			$column = new Column("column=role,variable=role,type=text,",__CLASS__);
			$column->label(Message::_("[拡張子]拡張子毎のタイプの指定"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::Role");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::Role");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::File")){
			$column = new Column("column=file,variable=file,type=text,",__CLASS__);
			$column->label(Message::_("[ファイル]"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::File");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::File");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::Dep")){
			$column = new Column("column=dep,variable=dep,type=text,",__CLASS__);
			$column->label(Message::_("[パッケージ依存関係]"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::Dep");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::Dep");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::Installer")){
			$column = new Column("column=installer,variable=installer,type=text,",__CLASS__);
			$column->label(Message::_("[インストーラ]"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::Installer");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::Installer");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::PackageSummaryFile")){
			$column = new Column("column=package_summary_file,variable=packageSummaryFile,type=string,",__CLASS__);
			$column->label(Message::_("[パッケージ]概要(ファイル)"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::PackageSummaryFile");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::PackageSummaryFile");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::PackageDescriptionFile")){
			$column = new Column("column=package_description_file,variable=packageDescriptionFile,type=string,",__CLASS__);
			$column->label(Message::_("[パッケージ]詳細説明(ファイル)"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::PackageDescriptionFile");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::PackageDescriptionFile");
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
		if(!Rhaco::isVariable("_R_D_C_","Release::PackageNotesFile")){
			$column = new Column("column=package_notes_file,variable=packageNotesFile,type=string,",__CLASS__);
			$column->label(Message::_("[パッケージ]更新履歴(ファイル)"));
			Rhaco::addVariable("_R_D_C_",$column,"Release::PackageNotesFile");
		}
		return Rhaco::getVariable("_R_D_C_",null,"Release::PackageNotesFile");
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
}
?>