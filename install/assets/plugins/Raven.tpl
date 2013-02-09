/**
 * Raven
 * 
 * Raven client for Sentry
 *
 * @category    plugin
 * @version     0.1.0
 * @license     MIT
 * @internal    @properties &raven_dsn=Sentry DSN;text;
 * @internal    @events OnWebPageInit, OnManagerPageInit
 * @internal    @modx_category Manager and Admin
 * @internal    @installset base, sample
 *
 * Written By Victor Safronovich
 * 09.02.2013
 */

// Run the main code
$asset_path = $modx->config['base_path'] . 'assets/plugins/raven/raven.php';
include $asset_path;
