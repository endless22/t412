<?php
require_once __DIR__ . '/' . 'file.class.php';
require_once __DIR__ . '/' . 'user.class.php';
require_once __DIR__ . '/' . 'syno.class.php';
require_once __DIR__ . '/' . 'vendor/autoload.php';
/**
 * Classe utilisée pour gérer les actions vers les différents serveurs
 * (transmission, synology, local)
 *
 * @author Matthias BOSC <matthias@bosc.io
 */

class Servers extends User {

  /**
   * Fonction globale pour ajouter un téléchargement sur un serveur distant
   * 
   * @param $type Le type d'ajout (torrent, link, hash)
   * @param $id L'ID du torrent
   * @param $idserv L'ID du serveur destination
   * @param $data Pour passer des données (ie. un nom, un lien ou un hash)
   */
  public function addTorrent($type, $id, $idserv, $data = null) {
    $credentials = $this->getSeedbox($idserv);
    $this->getDetails($id);
    if ($id !== null) {
      $base64 = $this->getBase64Torrent($id);
    }

    if ($credentials->type == 'transmission') {

      $client = new Transmission\Client($this->decrypt($credentials->host), $credentials->port);
      $client->authenticate($this->decrypt($credentials->username), $this->decrypt($credentials->password));
      $transmission = new Transmission\Transmission();
      $transmission->setClient($client);
      $torrent = $transmission->add($base64, true, $credentials->folder);

    } elseif ($credentials->type == 'synology') {

      $syno = new Syno($this->decrypt($credentials->host), $credentials->port);
      $syno->setClient($this->decrypt($credentials->username), $this->decrypt($credentials->password));
      $syno->folder = $credentials->folder;

      if ($type == 'torrent') {
        $data = $base64;
        $syno->name = $this->details->name;
      } elseif ($type == 'link') {
        $data = parent::DL_PREFIX . $syno->encode($data);
        $syno->name = urldecode(basename($data));
      } elseif ($type == 'hash') {
        $reponse = $this->listTorrent($id, $data);
        if ($reponse->isFinished() !== true) {
          throw new Exception("Le téléchargement du torrent n'est pas terminé", 1);
        }
        $syno->name = $reponse->getName();
        $files = $reponse->getFiles();
        foreach ($files as $key => $value) {
          $filenames[] = parent::DL_PREFIX . $syno->encode($value->getName());
        }
        $data = implode(',', $filenames);
      }
      $syno->addTask($type, $data);

    } elseif ($credentials->type == 'local') {

      $file = new File($credentials->folder, $this->details->name . '.torrent', parent::T411USER);
      $file->writeFile($base64);
    }
  }

  public function listTorrents($idserv) {
    $credentials = $this->getSeedbox($idserv);

    $client = new Transmission\Client($this->decrypt($credentials->host), $credentials->port);
    $client->authenticate($this->decrypt($credentials->username), $this->decrypt($credentials->password));
    $transmission = new Transmission\Transmission();
    $transmission->setClient($client);

    return $transmission->all();
  }

  public function listTorrent($idserv, $hash) {
    $credentials = $this->getSeedbox($idserv);

    $client = new Transmission\Client($this->decrypt($credentials->host), $credentials->port);
    $client->authenticate($this->decrypt($credentials->username), $this->decrypt($credentials->password));
    $transmission = new Transmission\Transmission();
    $transmission->setClient($client);

    try {
      $torrent = $transmission->get($hash);
      return $torrent;
    } catch (Exception $e) {
      return false;
    }
  }

  public function directDownload($id, $name) {
    $base64 = $this->getBase64Torrent($id);
    $torrentfile = tempnam(sys_get_temp_dir(), $id);
    $handle = fopen($torrentfile, 'w');
    fwrite($handle, base64_decode($base64));
    fclose($handle);

    header('Content-Description: File Transfer');
    header('Content-Type: application/x-bittorent');
    header('Content-disposition: attachment; filename="' . $name . '.torrent"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    readfile($torrentfile);

    unlink($torrentfile);
  }

  public function testConnection2($type, $address, $port, $user, $password, $folder = null) {
    if ($type == 'transmission') {

      $client = new Transmission\Client($address, $port);
      $client->authenticate($user, $password);
      $transmission = new Transmission\Transmission();
      $transmission->setClient($client);
      $transmission->getSession();

    } elseif ($type == 'synology') {

      $client = new Syno($address, $port);
      $client->setClient($user, $password);
      $client->getSid();

    } elseif ($type == 'local') {

      new File($folder);

    }
    return false;
  }

  public function testConnection($type, $address, $port, $user, $password, $folder = null) {
    if ($type == 'transmission') {
      $client = new Transmission\Client($address, $port);
      $client->authenticate($user, $password);
      $transmission = new Transmission\Transmission();
      $transmission->setClient($client);

      try {
        $transmission->getSession();
        return true;
      } catch (Exception $e) {
        return $e->getMessage();
      }
    } elseif ($type == 'synology') {
      $client = new Syno($address, $port);
      $client->setClient($user, $password);

      try {
        $client->getSid();
        return true;
      } catch (Exception $e) {
        return $e->getMessage();
      }
    } elseif ($type == 'local') {
      try {
        new File($folder, null, parent::T411USER);
        return true;
      } catch (Exception $e) {
        return $e->getMessage();
      }
    }
    return false;
  }
}
?>
