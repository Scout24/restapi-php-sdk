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
		$this->_oDataConnection = mysqli_connect($aConnection[1],$aConnection[2],$aConnection[3],$aConnection[4]);
		if (mysqli_connect_errno()) {
			return FALSE;
		}
		$this->_oDatabaseDb=$aConnection[4];
		return TRUE;
	}

	/**
     * Prüfen ob die Storage-Tabelle in der
	 * Datenbank existiert.
     *
     * @return boolean
     */
	private function getDataTable()
	{
		if($aLists = mysqli_query($this->_oDataConnection,'SHOW TABLES'))
		{
		while ($row = mysqli_fetch_row($aLists)) {
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
            `ic_username` VARCHAR(60),
            PRIMARY KEY (  `ic_id` )
            ) ENGINE = MYISAM";
			mysqli_query($this->_oDataConnection,$sql);
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
		$res = mysqli_query($this->_oDataConnection,$sql);
		while($record = mysqli_fetch_array($res))
		{
			$aFields[$record['0']] = 1;
		}
		foreach($aFields as $key=>$value)
		{
			// Add username field
			if($key=='ic_username' && $value==0)
			{
				$sql_username = "ALTER TABLE `".$this->_oDatabaseDb."`.`".$this->_sTableName."` ADD ic_username VARCHAR(60) NOT NULL;";
				mysqli_query($this->_oDataConnection,$sql_username);
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
			if(mysqli_query($this->_oDataConnection,$sql))
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
		$result = mysqli_query($this->_oDataConnection,$sql);
		$obj = mysqli_fetch_object($result);
		return $obj;
	}

    /**
     * Einen Requesttoken ohne Session ermitteln und zurückliefern.
     *
     * @return mixed
     */
    public function getRequestTokenWithoutSession()
    {
        $sql = "SELECT * FROM `".$this->_oDatabaseDb."`.`".$this->_sTableName."` WHERE ic_desc='REQUEST' order by ic_id desc LIMIT 1";
        $result = mysqli_query($this->_oDataConnection,$sql);
        $obj = mysqli_fetch_object($result);
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
		$result = mysqli_query($this->_oDataConnection,$sql);
		while($obj = mysqli_fetch_object($result))
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
		mysqli_query($this->_oDataConnection,$sql);
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
		if(mysqli_query($this->_oDataConnection,$sql))
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
	public function saveApplicationToken($sToken,$sSecret,$sUser)
	{
		if(strlen($sToken)>8)
		{
			$sql = "INSERT INTO `".$this->_oDatabaseDb."`.`".$this->_sTableName."` (
			`ic_desc`,`ic_key`,`ic_secret`,`ic_expire`,`ic_username`
			) VALUES (
			'APPLICATION','".$sToken."','".$sSecret."','1000-01-01 00:00:00.000000','".$sUser."'
			);";
			if(mysqli_query($this->_oDataConnection,$sql))
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
	public function getApplicationToken($sUser)
	{
		$sql = "SELECT * FROM `".$this->_oDatabaseDb."`.`".$this->_sTableName."` WHERE ic_desc='APPLICATION' AND ic_username='".$sUser."'";
		$result = mysqli_query($this->_oDataConnection,$sql);
		if($obj = mysqli_fetch_object($result))
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
		$result = mysqli_query($this->_oDataConnection,$sql);
		while($obj = mysqli_fetch_object($result))
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
		mysqli_query($this->_oDataConnection,$sql);
	}

}
