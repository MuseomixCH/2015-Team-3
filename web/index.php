<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;
use Museomix\Entity\Artefact;

$app = new Silex\Application();

// Enable debug mode
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/../views',
));
$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/../config/parameters.yml'));

$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
  $twig->addGlobal('google_map_api_key', $app['config']['google']['map_api_key']);

  return $twig;
}));

$artefact0 = new Artefact();
$artefact0->setId(0);
$artefact0->setName('Esc white');
$artefact0->setColor('white');
$artefact0->setHashtag('esc-white');
$artefact0->setIcon('esc-white.png');

$artefact1 = new Artefact();
$artefact1->setId(1);
$artefact1->setName('Esc yellow');
$artefact1->setColor('yellow');
$artefact1->setHashtag('esc-yellow');
$artefact1->setIcon('esc-yellow.png');

$artefact2 = new Artefact();
$artefact2->setId(2);
$artefact2->setName('Esc pink');
$artefact2->setColor('pink');
$artefact2->setHashtag('esc-pink');
$artefact2->setIcon('esc-pink.png');

$artefact3 = new Artefact();
$artefact3->setId(3);
$artefact3->setName('Esc blue');
$artefact3->setColor('blue');
$artefact3->setHashtag('esc-blue');
$artefact3->setIcon('esc-blue.png');

/**
 * Converts IP address to coordinates.
 * Returns an object with lat and long attributes.
 *
 * @param string $ipAddress The IP address to geolocalize.
 * @return \stdClass
 */
function convertIpToCoordinates($ipAddress)
{
  $coordinates = new \stdClass();
  $coordinates->long = '';
  $coordinates->lat = '';

  // TODO: Handle freegeoip webservice errors.
  $client = new Client();
  $results = $client->request('GET', 'https://freegeoip.net/json/' . $ipAddress);

  $geocode = json_decode($results->getBody()->getContents());
  $coordinates->long = $geocode->longitude;
  $coordinates->lat = $geocode->latitude;

  return $coordinates;
}

/**
 * Shows the escape information in JSON (used for the map).
 */
$app->get('/escape.json/{id}', function ($id) use ($app) {
  $mapSettings = array();

  $consumerKey = $app['config']['twitter']['consumer_key'];
  $consumerSecretKey = $app['config']['twitter']['consumer_secret_key'];
  $accessToken = $app['config']['twitter']['access_token'];
  $accessTokenSecret = $app['config']['twitter']['access_token_secret'];

  // TODO: Handle Twitter connection errors
  $connection = new TwitterOAuth($consumerKey, $consumerSecretKey, $accessToken, $accessTokenSecret);
  $connection->get('account/verify_credentials');

  // TODO: Handle Twitter tweets errors
  $results = $connection->get('statuses/user_timeline');

  $mapSettings['centerCoordinates'] = array(
    'lat' => 46.941772,
    'lng' => 7.449993
  );
  $mapSettings['zoom'] = 5;

  return new JsonResponse($mapSettings);
});

/**
 * Posts a tweet for an escape.
 */
$app->post('/escape', function (Request $request) use ($app) {

  $tweetText = $request->request->get('tweet-body');
  $id = $request->request->get('escape-id');
  $coordinates = convertIpToCoordinates($request->getClientIp());

  $consumerKey = $app['config']['twitter']['consumer_key'];
  $consumerSecretKey = $app['config']['twitter']['consumer_secret_key'];
  $accessToken = $app['config']['twitter']['access_token'];
  $accessTokenSecret = $app['config']['twitter']['access_token_secret'];

  // TODO: Handle Twitter connection errors
  $connection = new TwitterOAuth($consumerKey, $consumerSecretKey, $accessToken, $accessTokenSecret);
  $connection->get('account/verify_credentials');

  // TODO: Handle Twitter tweets errors
  $connection->post('statuses/update', array(
    'status' => $tweetText,
    'lat' => $coordinates->lat,
    'long' => $coordinates->long
  ));

  return $app['twig']->render('escape/show.html.twig', array('escapeId' => $id));
});

/**
 * Shows the escape.
 */
$app->get('/escape/{id}', function ($id) use ($app) {
  return $app['twig']->render('escape/show.html.twig', array('escapeId' => $id));
});

$app->run();
