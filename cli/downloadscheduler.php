<?php
require_once __DIR__ . '/' . '../utils.class.php';
require_once __DIR__ . '/' . '../syno.class.php';

class DownloadScheduler extends Utils {
  private $scheduledlist;

  public function __construct() {
    if(php_sapi_name() != 'cli') { exit; }
    $this->scheduledlist = $this->getDownloadQueue();
    $this->AddTasks();
  }

  public function AddTasks() {
    if (!empty($this->scheduledlist)) {
      foreach ($this->scheduledlist as $value) {
        try {
          $this->uid = $value->uid;
          $this->addTorrent('hash', $value->idserv, $value->server, $value->hash);
          $this->deleteFromQueue($value->id);
        } catch (Exception $e) {
          echo 'erreur: ' . $e->getMessage() . "\n";
        }
      }
    }
  }
}

new DownloadScheduler;
?>
