<?php
require_once __DIR__ . '/' . 'torrent.class.php';

class User extends Torrent {

  public function storeCredentials($uid, $user, $password) {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("INSERT INTO identifiants(uid, t411user, t411pass)
      VALUES(:uid, :user, :pass)
      ON DUPLICATE KEY UPDATE t411user=VALUES(t411user), t411pass=VALUES(t411pass)");
    $statement->bindParam(':uid', $uid);
    $statement->bindParam(':user', $user);
    $statement->bindParam(':pass', $password);
    $statement->execute();

    $statement->closeCursor();
  }

  public function getCredentials() {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("SELECT * FROM identifiants WHERE uid = :uid");
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $result = $statement->fetch(PDO::FETCH_OBJ);
    $statement->closeCursor();

    return $result;
  }

  public function addSerie($name, $saison, $current, $last, $langage) {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("INSERT INTO autodownload(uid, name, saison, current, last, language)
      VALUES(:uid, :name, :saison, :current, :last, :language)
      ON DUPLICATE KEY UPDATE uid=VALUES(uid), name=VALUES(name), saison=VALUES(saison), current=VALUES(current), last=VALUES(last), language=VALUES(language)");
    $statement->bindParam(':uid', $this->uid);
    $statement->bindParam(':name', $name);
    $statement->bindParam(':saison', $saison);
    $statement->bindParam(':last', $last);
    $statement->bindParam(':current', $current);
    $statement->bindParam(':language', $langage);
    $statement->execute();

    $statement->closeCursor();
  }

  public function getSeries() {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("SELECT * FROM autodownload WHERE uid = :uid");
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    $statement->closeCursor();

    return $result;
  }

  public function isNotSelectable($uid) {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("SELECT * FROM autodownload WHERE uid = :uid");
    $statement->bindParam(':uid', $uid);
    $statement->execute();

    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    $statement->closeCursor();

    return empty($result) ? true : false;
  }

  function getLogins() {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("SELECT * FROM identifiants");
    $statement->execute();

    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    $statement->closeCursor();

    return $result;
  }

  public function updateSerie($id, $episode) {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("UPDATE autodownload SET current = :current WHERE id = :id AND uid = :uid");
    $statement->bindParam(':current', $episode);
    $statement->bindParam(':id', $id);
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $statement->closeCursor();
  }

  public function dropDB($duree) {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("TRUNCATE TABLE `$duree`");
    $statement->execute();

    $statement->closeCursor();
  }

  public function updateTopDB($duree, $contenu) {
    $this->dropDB($duree);
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    foreach($contenu as $top) {
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
    $statement->closeCursor();
  }

  public function getTopFromDB($duree) {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("SELECT * FROM $duree");
    $statement->execute();

    $result = $statement->fetchAll(PDO::FETCH_OBJ);
    $statement->closeCursor();
    return $result;
  }

  public function deleteSerie($id) {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("DELETE FROM autodownload WHERE id = :id AND uid = :uid");
    $statement->bindParam(':id', $id);
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $statement->closeCursor();
  }

  public function trySQLConnection() {
    try{
      $link = new pdo('mysql:host='.parent::DB_HOST.';charset=utf8', parent::DB_USER, parent::DB_PASS, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
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
    $statement->closeCursor();
  }

  public function createTables() {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME.';charset=utf8', parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("CREATE TABLE IF NOT EXISTS `identifiants` (
      `uid` int(11) NOT NULL,
      `t411user` varchar(250) DEFAULT NULL,
      `t411pass` varchar(250) DEFAULT NULL,
      `host` varchar(100) DEFAULT NULL,
      `port` int(11) DEFAULT NULL,
      `user` varchar(50) DEFAULT NULL,
      `pass` varchar(250) DEFAULT NULL,
      `email` varchar(250) DEFAULT NULL,
      UNIQUE KEY `uid` (`uid`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    $statement->execute();

    $statement = $link->prepare("CREATE TABLE IF NOT EXISTS `dailytop` (
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
    $statement->execute();

    $statement = $link->prepare("CREATE TABLE IF NOT EXISTS `weeklytop` (
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
    $statement->execute();

    $statement = $link->prepare("CREATE TABLE IF NOT EXISTS `monthlytop` (
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
    $statement->execute();

    $statement = $link->prepare("CREATE TABLE IF NOT EXISTS `autodownload` (
      `uid` int(11) NOT NULL,
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) DEFAULT NULL,
      `saison` int(11) DEFAULT NULL,
      `current` int(11) DEFAULT NULL,
      `last` int(11) DEFAULT NULL,
      `language` int(11) DEFAULT NULL,
      UNIQUE KEY `id` (`id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8");
    $statement->execute();
    $statement->closeCursor();
  }

}

?>
