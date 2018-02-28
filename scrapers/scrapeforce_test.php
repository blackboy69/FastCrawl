<?
include_once("config.inc");
include_once("scrapeforce.inc");



$scraper = 'inbound_test';
$sf = new ScrapeForce();
$sf->uploadCsv($scraper);
print_r($sf->error);