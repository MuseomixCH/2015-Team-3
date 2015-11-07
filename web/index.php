<?php

error_reporting(E_ALL);
ini_set('display_errors','On');

// web/index.php
require_once __DIR__.'/../vendor/autoload.php';

use Abraham\TwitterOAuth\TwitterOAuth;
use Symfony\Component\HttpFoundation\Request;

$app = new Silex\Application();

// Enable debug mode
$app['debug'] = true;

$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/../views',
));
$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/../config/parameters.yml'));


$app->post('/tweet', function (Request $request) use ($app) {

  $tweetText = $request->request->get('tweet-body');
  $request->getClientIp();

  $consumerKey = $app['config']['twitter']['consumer_key'];
  $consumerSecretKey = $app['config']['twitter']['consumer_secret_key'];
  $accessToken = $app['config']['twitter']['access_token'];
  $accessTokenSecret = $app['config']['twitter']['access_token_secret'];

  $connection = new TwitterOAuth($consumerKey, $consumerSecretKey, $accessToken, $accessTokenSecret);
  $content = $connection->get('account/verify_credentials');


  $connection = new TwitterOAuth($consumerKey, $consumerSecretKey, $accessToken, $accessTokenSecret);
  $connection->get('account/verify_credentials');

  $connection->post('statuses/update', array(
    'status' => $tweetText
  ));

  return $app['twig']->render('tweet/confirmation.html.twig');
});

$app->get('/', function () use ($app) {
  return $app['twig']->render('tweet/form.html.twig');
});

$app->run();
