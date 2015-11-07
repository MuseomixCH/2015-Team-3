<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use GuzzleHttp\Client;

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

  return $app['twig']->render('escape/show.html.twig');
});

/**
 * Shows the escape.
 */
$app->get('/escape/{id}', function ($id) use ($app) {
  return $app['twig']->render('escape/show.html.twig', array('escapeId' => $id));
});

$app->run();
