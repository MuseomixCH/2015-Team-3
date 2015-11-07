<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Component\HttpFoundation\Request;
use GuzzleHttp\Client;

$app = new Silex\Application();

// Enable debug mode
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/../views',
));
$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/../config/parameters.yml'));

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

$app->post('/tweet', function (Request $request) use ($app) {

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

  return $app['twig']->render('tweet/confirmation.html.twig');
});

$app->get('/', function () use ($app) {
  return $app['twig']->render('tweet/form.html.twig');
});

$app->run();
