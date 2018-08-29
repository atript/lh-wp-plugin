<?php

namespace LogHero\Wordpress;
use \LogHero\Client\LogTransportType;
use LogHero\Client\RedisOptions;


class LogHeroPluginSettings {
    public static $useSyncTransportOptionName = 'use_sync_transport';
    public static $apiKeyOptionName = 'api_key';
    public static $redisUrlOptionName = 'redis_url';
    public static $redisKeyPrefixOptionName = 'redis_key_prefix';

    private $settingsStorage;
    private $hasWordPress;

    private $apiKey;
    private $transportType;
    private $redisOptions;

    public function __construct($settingsStorage = null, $hasWordPress = null) {
        if ($hasWordPress === null) {
            $hasWordPress = function_exists('get_option') ? True : False;
        }
        $this->hasWordPress = $hasWordPress;
        $this->settingsStorage = $settingsStorage;
        $this->initializeSettings();
    }

    public function getApiKey() {
        return $this->apiKey;
    }

    public function getTransportType() {
        return $this->transportType;

    }

    public function getRedisOptions() {
        return $this->redisOptions;
    }

    public function flushToSettingsStorage() {
        $optionsToStore = static::getOptionsToStore();
        $jsonData = array();
        foreach($optionsToStore as $option) {
            $jsonData[$option] = get_option($option);
        }
        $this->settingsStorage->set(json_encode($jsonData));
    }

    private function initializeSettings() {
        $jsonData = null;
        $storageData = $this->settingsStorage ? $this->settingsStorage->get() : null;
        if ($storageData) {
            $jsonData = json_decode($storageData, true);
        }
        $this->apiKey = $this->getOption(static::$apiKeyOptionName, $jsonData);
        $redisUrl = $this->getOption(static::$redisUrlOptionName, $jsonData);
        if($redisUrl) {
            $redisKeyPrefix = $this->getOption(static::$redisKeyPrefixOptionName, $jsonData);
            $this->redisOptions = new RedisOptions($redisUrl, $redisKeyPrefix);
        }
        $useSyncTransport = $this->getOption(static::$useSyncTransportOptionName, $jsonData);
        if ($useSyncTransport) {
            $this->transportType = LogTransportType::SYNC;
            return;
        }
        $this->transportType = LogTransportType::ASYNC;
    }

    private function getOption($key, $jsonData) {
        if ($this->hasWordPress) {
            return get_option($key);
        }
        if ($jsonData) {
            return array_key_exists($key, $jsonData) ? $jsonData[$key] : null;
        }
        return null;
    }

    private static function getOptionsToStore() {
        return array(
            static::$useSyncTransportOptionName,
            static::$apiKeyOptionName,
            static::$redisUrlOptionName,
            static::$redisKeyPrefixOptionName
        );
    }

}