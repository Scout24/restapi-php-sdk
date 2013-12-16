<?php

/**
 * Immocaster SDK
 * Datenspeicherung per Mysql(Datenbank)
 * in PHP Applikationen.
 *
 * @package    ImmobilienScout24 PHP-SDK
 * @author     Norman Braun (medienopfer98.de)
 * @link       http://www.immobilienscout24.de
 */

class Immocaster_Data_Mysql
{

	/**
     * Object mit der Verbindung
	 * zur Datenbank.
	 *
     * @var object
     */
	 private $_oDataConnection = null;
	
	/**
     * Name der Datenbank für
	 * die Datenspeicherung.
	 *
     * @var string
     */
	 private $_oDatabaseDb = null;
	 
	/**
	 * Name der Tabelle für
	 * die Datenspeicherung.
	 *
     * @var string
	 */
	 private $_sTableName = 'Immocaster_Storage';
	
	/**
     * Zeit nachdem ein Request-
	 * token gelöscht wird (in Minuten).
	 *
     * @var int
     */
	 private $_iRequestExpire = 60;
	
    /**
     * Singleton Pattern für die Erstellung
	 * der Instanz von Immocaster_Data_Mysql.
     *
	 * @var array Verbindungsdaten für die Datenbank
	 * @var string Alternativer Name für die Tabelle
     * @return Immocaster_Data_Mysql
     */
	static private $instance = null; 
	static public function getInstance($aConnection=array(),$sTableName=null) 
	{ 
		if (!isset(self::$instance)) 
		{ 
			self::$instance = new self($aConnection,$sTableName); 
		} 
		return self::$instance; 
	}
	
	/**
     * Verbindung zur Datenbank aufbauen und Tabelle
	 * erzeugen, sofern diese noch nicht existiert.
     *
	 * @var array Verbindungsdaten für die Datenbank
	 * @var string Alternativer Name für die Tabelle
     * @return boolean
     */
	public function __construct($aConnection,$sTableName)
	{
		if($sTableName)
		{
			$this->_sTableName = $sTableName;
		}
		if($this->connectDatabase($aConnection))
		{
			if(!$this->getDataTable())
			{
				if($this->setDataTable())
				{
					return true;
				}
			}
			else
			{
				$this->updateDataTableFields();
				return true;
			}
		}
		return false;
	}
	
	/**
     * MySQL-Datenbank konnektieren.
     *
	 * @var array Verbindungsdaten für die Datenbank
     * @return boolean
     */
	private function connectDatabase($aConnection=array())
	{
		if($db = @mysql_connect($aConnection[1],$aConnection[2],$aConnection[3]))
		{
			if(@mysql_select_db($this->_oDatabaseDb=$aConnection[4]))
			{
				$this->_oDataConnection = $db;
				return true;
			}
		}
		return false;
	}
	
	/**
     * Prüfen ob die Storage-Tabelle in der
	 * Datenbank existiert.
     *
     * @return boolean
     */
	private function getDataTable()
	{
		if($aLists = @mysql_list_tables($this->_oDatabaseDb))
		{
			while ($row = mysql_fetch_row($aLists)) {
				if($row[0]==$this->_sTableName)
				{
					return true;
				}
			}
		}
		return false;
	}
	
	/**
     * Storage-Tabelle in der
	 * MySql-Datenbank anlegen.
     *
     * @return void
     */
	private function setDataTable()
	{
		if(!$this->getDataTable())
		{
			$sql = "CREATE TABLE  `".$this->_oDatabaseDb."`.`".$this->_sTableName."` (
			`ic_id` INT( 16 ) UNSIGNED NOT NULL AUTO_INCREMENT,
			`ic_desc` VARCHAR( 32 ) NOT NULL,
			`ic_key` VARCHAR( 128 ) NOT NULL,
			`ic_secret` VARCHAR( 128 ) NOT NULL,
			`ic_expire` DATETIME NOT NULL,
			PRIMARY KEY (  `ic_id` )
			) ENGINE = MYISAM";
			mysql_query($sql,$this->_oDataConnection);
		}
	}
	
	/**
     * Prüfen ob bestimmte Felder in der
	 * Datenbank existieren und bei Bedarf
	 * hinzufügen.
     *
     * @return boolean
     */
	private function updateDataTableFields()
	{
		$aFields = array(
			'ic_username' => 0
		);
		$sql = "SHOW COLUMNS FROM `".$this->_oDatabaseDb."`.`".$this->_sTableName."`";
		$res = mysql_query($sql,$this->_oDataConnection);
		while($record = mysql_fetch_array($res))
		{
			$aFields[$record['0']] = 1;
		}
		foreach($aFields as $key=>$value)
		{
			// Add username field
			if($key=='ic_username' && $value==0)
			{
				$sql_username = "ALTER TABLE `".$this->_oDatabaseDb."`.`".$this->_sTableName."` ADD ic_username VARCHAR(60) NOT NULL;";
				mysql_query($sql_username,$this->_oDataConnection);
			}
		}
	}
	
	/**
     * Requesttoken speichern.
     *
	 * @var string Token
	 * @var string Secret
     * @return boolean
     */
	public function saveRequestToken($sToken,$sSecret)
	{
		$this->cleanRequestToken();
		if(strlen($sToken)>8)
		{
			$dExpire = date('Y-m-d H:i:s');
			$sql = "INSERT INTO `".$this->_oDatabaseDb."`.`".$this->_sTableName."` (
			`ic_desc`,`ic_key`,`ic_secret`,`ic_expire`
			) VALUES (
			'REQUEST','".$sToken."','".$sSecret."','".date("Y-m-d H:i:s", strtotime ("+".$this->_iRequestExpire." minutes"))."'
			);";
			if(mysql_query($sql,$this->_oDataConnection))
			{
				return true;
			}
		}
		return false;
	}
	
	/**
     * Requesttoken ermitteln und zurückliefern.
     *
	 * @var string Token
     * @return mixed
     */
	public function getRequestToken($sToken=null)
	{
		if(strlen($sToken)<8){return false;}
		$sql = "SELECT * FROM `".$this->_oDatabaseDb."`.`".$this->_sTableName."` WHERE ic_desc='REQUEST' AND ic_key='".$sToken."'";
		$result = mysql_query($sql,$this->_oDataConnection);
		$obj = mysql_fetch_object($result);
		return $obj;
	}
	
	/**
     * Requesttoken nach einer 
	 * bestimmten Zeit löschen.
     *
     * @return void
     */
	private function cleanRequestToken()
	{
		$dNow = date("Y-m-d H:i:s");
		$sql = "SELECT * FROM `".$this->_oDatabaseDb."`.`".$this->_sTableName."` WHERE ic_desc='REQUEST'";
		$result = mysql_query($sql,$this->_oDataConnection);
		while($obj = mysql_fetch_object($result))
		{
			if($obj->ic_expire<$dNow)
			{
				$this->deleteRequestTokenById($obj->ic_id);
			}
		}
	}
	
	/**
     * Alle Requesttoken der
	 * Applikation löschen.
     *
     * @return void
     */
	public function deleteRequestToken()
	{
		$sql = "DELETE FROM `".$this->_oDatabaseDb."`.`".$this->_sTableName."` WHERE ic_desc='REQUEST'";
		mysql_query($sql,$this->_oDataConnection);
	}
	
	/**
     * Requesttoken anhand einer
	 * einzelnen ID löschen.
     *
	 * @param int Id des zu löschenden Tokens
     * @return boolean
     */
	public function deleteRequestTokenById($iId)
	{
		$sql = "DELETE FROM `".$this->_oDatabaseDb."`.`".$this->_sTableName."` WHERE ic_desc='REQUEST' AND ic_id=".$iId;
		if(mysql_query($sql,$this->_oDataConnection))
		{
			return true;
		}
		return false;
	}
	
	/**
     * Accesstoken für die
	 * Applikation speichern.
     *
	 * @var string Token
	 * @var string Secret
     * @return boolean
     */
	public function saveApplicationToken($sToken,$sSecret,$sUser='')
	{
		if(strlen($sToken)>8)
		{
			if($sUser == ''){ $sUser = 'me'; }
			$sql = "INSERT INTO `".$this->_oDatabaseDb."`.`".$this->_sTableName."` (
			`ic_desc`,`ic_key`,`ic_secret`,`ic_username`
			) VALUES (
			'APPLICATION','".$sToken."','".$sSecret."','".$sUser."'
			);";
			if(mysql_query($sql,$this->_oDataConnection))
			{
				@$this->deleteRequestToken();
				return true;
			}
		}
		return false;
	}
	
	/**
     * Accesstoken für die Application
	 * ermitteln und zurückliefern.
     *
     * @return object
     */
	public function getApplicationToken($sUser='')
	{
		if($sUser=='')
		{
			$sUser = "me";
		}
		$sql = "SELECT * FROM `".$this->_oDatabaseDb."`.`".$this->_sTableName."` WHERE ic_desc='APPLICATION' AND ic_username='".$sUser."'";
		$result = mysql_query($sql,$this->_oDataConnection);
		if($obj = mysql_fetch_object($result))
		{
			return $obj;
		}
		return false;
	}
	
	/**
     * Alle Accesstoken für die Application
	 * ermitteln und zurückliefern.
     *
     * @return array
     */
	public function getAllApplicationUsers()
	{
		$aUsers = array();
		$sql = "SELECT * FROM `".$this->_oDatabaseDb."`.`".$this->_sTableName."` WHERE ic_desc='APPLICATION'";
		$result = mysql_query($sql,$this->_oDataConnection);
		while($obj = mysql_fetch_object($result))
		{
			array_push($aUsers,$obj->ic_username);
		}
		return $aUsers;
	}
	
	/**
     * Accesstoken für die
	 * Applikation löschen.
     *
     * @return void
     */
	public function deleteApplicationToken()
	{
		$sql = "DELETE FROM `".$this->_oDatabaseDb."`.`".$this->_sTableName."` WHERE ic_desc='APPLICATION'";
		mysql_query($sql,$this->_oDataConnection);
	}
	
}