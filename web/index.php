<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

// web/index.php
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../src/Museomix/Entity/Artefact.php';

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

/** @var Artefact[] $artefacts */
$artefacts = array();

$artefact0 = new Artefact();
$artefact0->setId(0);
$artefact0->setName('Esc white');
$artefact0->setColor('white');
$artefact0->setHashtag('escWhite');
$artefact0->setIcon('esc-white.png');
$artefacts[0] = $artefact0;

$artefact1 = new Artefact();
$artefact1->setId(1);
$artefact1->setName('Esc yellow');
$artefact1->setColor('yellow');
$artefact1->setHashtag('escYellow');
$artefact1->setIcon('esc-yellow.png');
$artefacts[1] = $artefact1;

$artefact2 = new Artefact();
$artefact2->setId(2);
$artefact2->setName('Esc pink');
$artefact2->setColor('pink');
$artefact2->setHashtag('escPink');
$artefact2->setIcon('esc-pink.png');
$artefacts[2] = $artefact2;

$artefact3 = new Artefact();
$artefact3->setId(3);
$artefact3->setName('Esc blue');
$artefact3->setColor('blue');
$artefact3->setHashtag('escBlue');
$artefact3->setIcon('esc-blue.png');
$artefacts[3] = $artefact3;

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
 * Returns an artefact according to
 * the hashtag contained in the given message.
 *
 * @param string $twitterMessage
 * @return Artefact||null
 */
function hashtagToArtefact($twitterMessage)
{
  preg_match_all("/(#\w+)/", $twitterMessage, $matches);
  if (in_array('#escWhite', $matches)){
    return $artefact0;
  } else if(in_array('#escYellow', $matches)){
    return $artefact1;
  } else if(in_array('#escPink', $matches)){
    return $artefact2;
  } else if(in_array('#escBlue', $matches)){
    return $artefact3;
  } else {
    return null;
  }
}

/**
 * Returns a JSON with tweets that are geolocalized, related to the given escape id.
 * That feed will be consume by GoogleMap.
 */
$app->get('/escape-map.json/{id}', function ($id) use ($app) {
  $mapSettings = array();

  $consumerKey = $app['config']['twitter']['consumer_key'];
  $consumerSecretKey = $app['config']['twitter']['consumer_secret_key'];
  $accessToken = $app['config']['twitter']['access_token'];
  $accessTokenSecret = $app['config']['twitter']['access_token_secret'];

  // TODO: Handle Twitter connection errors
  $connection = new TwitterOAuth($consumerKey, $consumerSecretKey, $accessToken, $accessTokenSecret);
  $connection->get('account/verify_credentials');

  // TODO: Handle Twitter tweets errors
  $tweets = $connection->get('statuses/user_timeline');

  foreach ($tweets as $tweet) {

    // Does not include tweets that doesn't have a location.
    if ($tweet->place === null) {
      continue;
    }

    $coordinates = array(
      'lng' => floatval($tweet->place->bounding_box->coordinates[0][0][0]),
      'lat' => floatval($tweet->place->bounding_box->coordinates[0][0][1])
    );

    $mapSettings['tweets'][] = array(
      'name' => 'test',
      'message' => $tweet->text,
      'coordinates' => $coordinates
    );
  }

  // Center the map on the MFK in Bern.
  $mapSettings['centerCoordinates'] = array(
    'lat' => 46.941772,
    'lng' => 7.449993
  );
  $mapSettings['zoom'] = 13;

  return new JsonResponse($mapSettings);
});

/**
 * Posts a tweet for an escape.
 */
$app->post('/escape', function (Request $request) use ($app, $artefacts) {

  $tweetText = $request->request->get('tweet-body');
  $id = intval($request->get('escape-id'));

  // Detect the artefact or returns 404 if not found.
  if (!isset($artefacts[$id])) {
    $app->abort(404, 'Sorry, the resource you are looking for could not be found.');
  }

  $coordinates = convertIpToCoordinates($request->getClientIp());

  $consumerKey = $app['config']['twitter']['consumer_key'];
  $consumerSecretKey = $app['config']['twitter']['consumer_secret_key'];
  $accessToken = $app['config']['twitter']['access_token'];
  $accessTokenSecret = $app['config']['twitter']['access_token_secret'];

  // TODO: Handle Twitter connection errors
  $connection = new TwitterOAuth($consumerKey, $consumerSecretKey, $accessToken, $accessTokenSecret);
  $connection->get('account/verify_credentials');

  // Add main hashtag (MFK, ...) and the artefact hashtag.
  $tweetText .= ' #' . $app['config']['gres']['main_hashtag'];
  $tweetText .= ' #' . $artefacts[$id]->getHashtag();

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
