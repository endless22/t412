<?php
/**
 * Classe utilisée pour gérer les actions vers un serveur transmission
 * Librairie utilisée: transmission-php
 *
 * @author Matthias BOSC <matthias@bosc.io
 */
require_once __DIR__ . '/' . 'user.class.php';
require_once __DIR__ . '/' . 'vendor/autoload.php';

class Transmission2 extends User {

  public function addTorrent($id) {
    $credentials = $this->getCredentials();
    $this->getBase64Torrent();

    $client = new Transmission\Client($this->dechiffrer($credentials->host), $credentials->port);
    $client->authenticate($this->dechiffrer($credentials->user), $this->dechiffrer($credentials->pass));
    $transmission = new Transmission\Transmission();
    $transmission->setClient($client);

    try {
      $torrent = $transmission->add($this->base64, true);
      return true;
    } catch (Exception $e) {
      return false;
    }
  }

  public function listTorrents() {
    $credentials = $this->getCredentials();

    $client = new Transmission\Client($this->dechiffrer($credentials->host), $credentials->port);
    $client->authenticate($this->dechiffrer($credentials->user), $this->dechiffrer($credentials->pass));
    $transmission = new Transmission\Transmission();
    $transmission->setClient($client);

    return $transmission->all();
  }

  public function listTorrent($hash) {
    $credentials = $this->getCredentials();

    $client = new Transmission\Client($this->dechiffrer($credentials->host), $credentials->port);
    $client->authenticate($this->dechiffrer($credentials->user), $this->dechiffrer($credentials->pass));
    $transmission = new Transmission\Transmission();
    $transmission->setClient($client);

    try {
      $torrent = $transmission->get($hash);
      return json_decode(json_encode($torrent));
    } catch (Exception $e) {
      return false;
    }
  }

  public function tryConnection($address, $port, $user, $password) {
    $client = new Transmission\Client($address, $port);
    $client->authenticate($user, $password);
    $transmission = new Transmission\Transmission();
    $transmission->setClient($client);

    try {
      $transmission->getSession();
      return true;
    } catch (Exception $e) {
      return false;
    }
  }

  public function storeSeedbox($address, $port, $user, $password, $email) {
    $link = new PDO('mysql:host='.parent::DB_HOST.';dbname='.parent::DB_NAME, parent::DB_USER, parent::DB_PASS);

    $link->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
    $link->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $statement = $link->prepare("UPDATE identifiants SET host = :host, port = :port, user = :user, pass = :pass, email = :email WHERE uid = :uid");
    $statement->bindParam(':host', $address);
    $statement->bindParam(':port', $port);
    $statement->bindParam(':user', $user);
    $statement->bindParam(':pass', $password);
    $statement->bindParam(':email', $email);
    $statement->bindParam(':uid', $this->uid);
    $statement->execute();

    $statement->closeCursor();
  }
}
?>