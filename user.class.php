<?php
require_once __DIR__ . '/' . 'torrent.class.php';

class User extends Torrent {

  private $link;
  private $statement;

  private function connect() {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);
    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $link;
  }

  public function getCredentials() {
    $link = $this->connect();

    $statement = $link->query("SELECT * FROM `identifiants` AS i INNER JOIN `servers` AS s ON i.uid = s.uid INNER JOIN `autodownload` AS a ON i.uid = s.uid GROUP BY i.uid");
    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    $link = null;

    return $result;
  }

  function getLogins() {
    $link = $this->connect();

    $statement = $link->query("SELECT * FROM identifiants", PDO::FETCH_OBJ);
    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    $statement = null;
    $link = null;

    return $result;
  }

  public function storeCredentials($uid, $username, $password) {
    $link = $this->connect();

    $statement = $link->prepare("INSERT INTO identifiants(uid, t411username, t411password)
      VALUES(:uid, :username, :pass)
      ON DUPLICATE KEY UPDATE t411username=VALUES(t411username), t411password=VALUES(t411password)");
    $statement->bindParam(':uid', $uid);
    $statement->bindParam(':username', $username);
    $statement->bindParam(':pass', $password);
    $statement->execute();

    $statement = null;
    $link = null;
  }

  public function getSeedbox($id) {
    $link = $this->connect();

    $statement = $link->prepare("SELECT * FROM servers WHERE uid = :uid AND id = :id");
    $statement->bindParam(':uid', $this->uid);
    $statement->bindParam(':id', $id);
    $statement->execute();

    $result = $statement->fetch(PDO::FETCH_OBJ);
    $statement = null;
    $link = null;

    return $result;
  }

  public function getSeedboxes() {
    $link = $this->connect();

    $statement = $link->prepare("SELECT * FROM servers WHERE uid = :uid");
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    $statement = null;
    $link = null;

    return $result;
  }

  public function getTransmissionServers() {
    $link = $this->connect();

    $statement = $link->prepare("SELECT * FROM servers WHERE uid = :uid AND type = 'transmission'");
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    $statement = null;
    $link = null;

    return $result;
  }

  public function getNasServers() {
    $link = $this->connect();

    $statement = $link->prepare("SELECT * FROM servers WHERE uid = :uid AND type = 'synology'");
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    $statement = null;
    $link = null;

    return $result;
  }

  public function getSeries() {
    $link = $this->connect();

    $statement = $link->prepare("SELECT * FROM autodownload WHERE uid = :uid AND current != last");
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    $statement = null;
    $link = null;

    return $result;
  }

  public function addSerie($server, $name, $season, $current, $last, $langage) {
    $link = $this->connect();

    $statement = $link->prepare("INSERT INTO autodownload(uid, server, name, season, current, last, language)
      VALUES(:uid, :server, :name, :season, :current, :last, :language)");
    $statement->bindParam(':uid', $this->uid);
    $statement->bindParam(':server', $server);
    $statement->bindParam(':name', $name);
    $statement->bindParam(':season', $season);
    $statement->bindParam(':last', $last);
    $statement->bindParam(':current', $current);
    $statement->bindParam(':language', $langage);
    $statement->execute();

    $statement = null;
    $link = null;
  }

  public function updateSerie($id, $episode) {
    $link = $this->connect();

    $statement = $link->prepare("UPDATE autodownload SET current = :current WHERE id = :id AND uid = :uid");
    $statement->bindParam(':current', $episode);
    $statement->bindParam(':id', $id);
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $statement = null;
    $link = null;
  }

  public function deleteSerie($id) {
    $link = $this->connect();

    $statement = $link->prepare("DELETE FROM autodownload WHERE id = :id AND uid = :uid");
    $statement->bindParam(':id', $id);
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $statement = null;
    $link = null;
  }

  public function getDownloadQueue() {
    $link = $this->connect();

    $statement = $link->prepare("SELECT * FROM downloadqueue");
    $statement->execute();
    $result = $statement->fetchAll(PDO::FETCH_OBJ);

    $statement = null;
    $link = null;
    return $result;
  }

  public function addDownloadQueue($idserv, $server, $hash) {
    $link = $this->connect();

    $statement = $link->prepare("INSERT INTO downloadqueue(uid, idserv, server, hash)
      VALUES(:uid, :idserv, :server, :hash)");
    $statement->bindParam(':uid', $this->uid);
    $statement->bindParam(':idserv', $idserv);
    $statement->bindParam(':server', $server);
    $statement->bindParam(':hash', $hash);
    $statement->execute();

    $statement = null;
    $link = null;
  }

  public function deleteFromQueue($id) {
    $link = $this->connect();

    $statement = $link->prepare("DELETE FROM `downloadqueue` WHERE id = :id AND uid = :uid");
    $statement->bindParam(':id', $id);
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $statement = null;
    $link = null;
  }

  public function dropDB() {
    $link = $this->connect();

    $link->query("TRUNCATE TABLE top_day");
    $link->query("TRUNCATE TABLE top_week");
    $link->query("TRUNCATE TABLE top_month");

    $link = null;
  }

  public function updateTopDB($duree, $contenu) {
    $link = $this->connect();

    foreach ($contenu as $top) {
      $statement = $link->prepare("INSERT INTO `$duree`(id, category, categoryname, name, rewritename, added, size, times_completed, seeders, leechers)
        VALUES(:id, :category, :categoryname, :name, :rewritename, :added, :size, :times_completed, :seeders, :leechers)");
      $statement->bindParam(':id', $top->id);
      $statement->bindParam(':category', $top->category);
      $statement->bindParam(':categoryname', $top->categoryname);
      $statement->bindParam(':name', $top->name);
      $statement->bindParam(':rewritename', $top->rewritename);
      $statement->bindParam(':added', $top->added);
      $statement->bindParam(':size', $top->size);
      $statement->bindParam(':times_completed', $top->times_completed);
      $statement->bindParam(':seeders', $top->seeders);
      $statement->bindParam(':leechers', $top->leechers);
      $statement->execute();
    }
    $statement = null;
    $link = null;
  }

  public function getTopFromDB($duree) {
    $link = $this->connect();

    $allowed = array('top_day', 'top_week', 'top_month');
    if (!in_array($duree, $allowed)) {
      throw new Exception("NOPE.", 1);
    }

    $statement = $link->prepare("SELECT * FROM $duree");
    $statement->execute();

    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    $statement = null;
    $link = null;

    return $result;
  }

  public function trySQLConnection() {
    try{
      $link = new PDO('mysql:host='.parent::DB_HOST.';charset=utf8', parent::DB_USER, parent::DB_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
      return true;
    } catch(PDOException $e){
      return false;
    }
  }

  public function createDB() {
    $link = new PDO('mysql:host='.parent::DB_HOST.';charset=utf8', parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("CREATE DATABASE IF NOT EXISTS ".parent::DB_NAME." DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci");
    try {
      $statement->execute();
      return true;
    } catch(PDOException $e) {
      return false;
    }
    $statement = null;
    $link = null;
  }

  public function createTables() {
    $link = $this->connect();

    $link->query("CREATE TABLE IF NOT EXISTS `identifiants` (
      `uid` int(11) NOT NULL,
      `t411username` varchar(250) DEFAULT NULL,
      `t411password` varchar(250) DEFAULT NULL,
      `email` varchar(250) DEFAULT NULL,
      UNIQUE KEY `uid` (`uid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $link->query("CREATE TABLE IF NOT EXISTS `servers` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `uid` int(11) NOT NULL,
      `name` varchar(100) NOT NULL,
      `type` enum('transmission','synology','local') NOT NULL,
      `host` varchar(200) DEFAULT NULL,
      `port` int(11) DEFAULT NULL,
      `username` varchar(200) DEFAULT NULL,
      `password` varchar(200) DEFAULT NULL,
      `folder` varchar(100) DEFAULT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $link->query("CREATE TABLE IF NOT EXISTS `top_day` (
      `id` int(11) DEFAULT NULL,
      `category` int(11) DEFAULT NULL,
      `categoryname` varchar(50) DEFAULT NULL,
      `name` varchar(500) DEFAULT NULL,
      `rewritename` varchar(250) DEFAULT NULL,
      `added` varchar(50) DEFAULT NULL,
      `size` bigint(11) DEFAULT NULL,
      `times_completed` int(11) DEFAULT NULL,
      `seeders` int(11) DEFAULT NULL,
      `leechers` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $link->query("CREATE TABLE IF NOT EXISTS `top_week` (
      `id` int(11) DEFAULT NULL,
      `category` int(11) DEFAULT NULL,
      `categoryname` varchar(50) DEFAULT NULL,
      `name` varchar(500) DEFAULT NULL,
      `rewritename` varchar(250) DEFAULT NULL,
      `added` varchar(50) DEFAULT NULL,
      `size` bigint(11) DEFAULT NULL,
      `times_completed` int(11) DEFAULT NULL,
      `seeders` int(11) DEFAULT NULL,
      `leechers` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $link->query("CREATE TABLE IF NOT EXISTS `top_month` (
      `id` int(11) DEFAULT NULL,
      `category` int(11) DEFAULT NULL,
      `categoryname` varchar(50) DEFAULT NULL,
      `name` varchar(500) DEFAULT NULL,
      `rewritename` varchar(250) DEFAULT NULL,
      `added` varchar(50) DEFAULT NULL,
      `size` bigint(11) DEFAULT NULL,
      `times_completed` int(11) DEFAULT NULL,
      `seeders` int(11) DEFAULT NULL,
      `leechers` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $link->query("CREATE TABLE IF NOT EXISTS `autodownload` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `uid` int(11) NOT NULL,
      `server` int(11) NOT NULL,
      `name` varchar(100) DEFAULT NULL,
      `season` int(11) DEFAULT NULL,
      `current` int(11) DEFAULT NULL,
      `last` int(11) DEFAULT NULL,
      `language` int(11) DEFAULT NULL,
      UNIQUE KEY `id` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

    $link->query("CREATE TABLE IF NOT EXISTS `downloadqueue` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `uid` int(11) NOT NULL,
      `idserv` int(11) NOT NULL,
      `server` int(11) NOT NULL,
      `hash` varchar(150) NOT NULL,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8");
    $link = null;
  }

  public function storeSeedbox($name, $type, $host, $port, $username, $password, $folder) {
    $link = $this->connect();

    $statement = $link->prepare("INSERT INTO `servers` (uid, name, type, host, port, username, password, folder)
      VALUES(:uid, :name, :type, :host, :port, :username, :password, :folder)");
    $statement->bindParam(':uid', $this->uid);
    $statement->bindParam(':name', $name);
    $statement->bindParam(':type', $type);
    $statement->bindParam(':host', $host);
    $statement->bindParam(':port', $port);
    $statement->bindParam(':username', $username);
    $statement->bindParam(':password', $password);
    $statement->bindParam(':folder', $folder);
    $statement->execute();

    $statement->closeCursor();
  }

  public function storeFolder($type, $folder) {
    $link = $this->connect();

    $statement = $link->prepare("INSERT INTO `servers` (uid, name, type, folder)
      VALUES(:uid, :name, :type, :folder)");
    $statement->bindParam(':uid', $this->uid);
    $statement->bindParam(':name', $name);
    $statement->bindParam(':type', $type);
    $statement->bindParam(':folder', $folder);
    $statement->execute();

    $statement->closeCursor();
  }

  public function deleteServer($id) {
    $link = $this->connect();

    $statement = $link->prepare("DELETE FROM `servers` WHERE id = :id AND uid = :uid");
    $statement->bindParam(':id', $id);
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $statement = null;
    $link = null;
  }

}

?>
