<?php
namespace LogHero\Wordpress;
use \LogHero\Client\APISettingsDefault;


class LogHeroAPISettings extends APISettingsDefault {
    private $apiLogPackageEndpoint;

    public function __construct($apiDevSettingsFile = null) {
        if (!$apiDevSettingsFile) {
            $apiDevSettingsFile = __DIR__ . '/logs/dev.loghero.io.json';
        }
        $this->apiDevSettingsFile = $apiDevSettingsFile;
        $this->initializeFromDevSettingsFile();
    }

    public function setAPILogPackageEndpoint($apiLogPackageEndpoint) {
        $this->apiLogPackageEndpoint = $apiLogPackageEndpoint;
        $this->refreshDevSettingsFile();
    }

    public function getAPILogPackageEndpoint() {
        if ($this->apiLogPackageEndpoint) {
            return $this->apiLogPackageEndpoint;
        }
        return parent::getAPILogPackageEndpoint();
    }

    private function refreshDevSettingsFile() {
        $jsonData = array(
            'apiLogPackageEndpoint' => $this->apiLogPackageEndpoint
        );
        file_put_contents($this->apiDevSettingsFile, json_encode($jsonData));
    }

    private function initializeFromDevSettingsFile() {
        if (!file_exists($this->apiDevSettingsFile)) {
            return;
        }
        $jsonString = file_get_contents($this->apiDevSettingsFile);
        chmod($this->apiDevSettingsFile, 0666);
        $json = json_decode($jsonString, true);
        if (!$json) {
            return;
        }
        if (array_key_exists('apiLogPackageEndpoint', $json)) {
            $this->apiLogPackageEndpoint = $json['apiLogPackageEndpoint'];
        }
    }
}