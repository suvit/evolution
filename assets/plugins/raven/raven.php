<?php
// Handle event
$e = &$modx->Event;
switch ($e->name)
{
  case "OnWebPageInit":
  case "OnManagerPageInit":
    // Set sentry error handler
    if (isset($raven_dsn) && $raven_dsn && in_array('curl', get_loaded_extensions())) {
      require_once('Raven/Autoloader.php');

      Raven_Autoloader::register();

      $client = new Raven_Client($raven_dsn, array(
        'tags' => array(
          'php_version' => phpversion(),
        )
      ));

      // Install error handlers
      $error_handler = new Raven_ErrorHandler($client);
      $error_handler->registerExceptionHandler();
      $error_handler->registerErrorHandler(true, IN_MANAGER_MODE ? error_reporting(): null);
    }

    break;
  default:
    return;
}
