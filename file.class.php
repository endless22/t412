<?php
/**
 * Classe utilisée pour écrire un torrent sur un serveur, en local.
 * Uniquement accessible pour le détenteur du serveur.
 * Utile pour ceux qui utilisent rTorrent avec un dossier watch.
 */

class File {
  public $dir;
  public $filename;

  public function __construct($dir, $filename = null, $username = null) {
    $this->dir = $dir;
    $this->filename = $filename;
    $this->checkUser($username);
    $this->checkdir();
    $this->checkperm();
  }

  /**
   * Vérifie que l'utilisateur est bien le "propriétaire" du serveur
   */
  public function checkUser($username) {
    if (php_sapi_name() != 'cli' && (empty($username) || $_COOKIE['username'] != $username)) {
      throw new Exception('Utilisateur non autorisé', 1);
    }
  }

  /**
   * Vérifie que le dossier existe
   */
  public function checkdir() {
    if (!is_dir($this->dir)) {
      throw new Exception("Dossier inexistant", 1);
    }
    return true;
  }

  /**
   * Vérifie que le dossier est accessible en écriture
   */
  public function checkperm() {
    if (!is_writable($this->dir)) {
      throw new Exception("Impossible d'écrire dans le dossier", 1);
    }
    return true;
  }


  /**
   * Vérifie que le fichier a été écrit et que son contenu
   * corresponde aux données du torrent
   */
  public function validate() {
    if (!is_file($this->dir . $this->filename)) {
      throw new Exception("Fichier non créé", 1);
    } elseif (sha1($this->data) != sha1_file($this->dir . $this->filename)) {
      throw new Exception("Contenu du fichier non conforme", 1);
    }
    return true;
  }

  /**
   * Écrit tout simplement le contenu du torrent dans un fichier
   */
  public function writeFile($base64) {
    $this->data = base64_decode($base64);
    file_put_contents($this->dir . $this->filename, $this->data);
    $this->validate();
  }
}
?>