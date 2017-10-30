<?php
/**
 * PageNotice extension - lets you define a fixed header or footer message for each page or namespace.
 *
 * Page notices (headers and footers) are maintained as MediaWiki-messages.
 * For page Foo, MediaWiki:top-notice-Foo and MediaWiki:bottom-notice-Foo can be used to defined a header
 * or footer respectively. For namespace 6, MediaWiki:top-notice-ns-6 and MediaWiki:bottom-notice-ns-6 can
 * be used to defined a header or footer respectively. Mind the capitalization.
 *
 * For more info see http://mediawiki.org/wiki/Extension:PageNotice
 *
 * @file
 * @ingroup Extensions
 * @author Daniel Kinzler, brightbyte.de
 * @copyright © 2007 Daniel Kinzler
 * @licence GNU General Public Licence 2.0 or later
 */

if( !defined( 'MEDIAWIKI' ) ) {
	echo( "This file is an extension to the MediaWiki software and cannot be used standalone.\n" );
	die( 1 );
}

$wgExtensionCredits['other'][] = array(
	'path'           => __FILE__,
	'name'           => 'PageNotice',
	'author'         => 'Daniel Kinzler',
	'url'            => 'https://mediawiki.org/wiki/Extension:PageNotice',
	'descriptionmsg' => 'pagenotice-desc',
);

// Disable notices for individual pages, and only allow namespace-wide notices?
$wgPageNoticeDisablePerPageNotices = false;

$dir = __DIR__;
$wgAutoloadClasses['PageNoticeHooks'] = $dir . '/PageNotice.hooks.php';
$wgMessagesDirs['PageNotice'] = __DIR__ . '/i18n';
$wgHooks['ArticleViewHeader'][] = 'PageNoticeHooks::renderHeader';
$wgHooks['ArticleViewFooter'][] = 'PageNoticeHooks::renderFooter';

$wgResourceModules['ext.pageNotice'] = array(
   'styles' => array(
       'modules/ext.pageNotice.css',
   ),
   'localBasePath' => $dir,
   'remoteExtPath' => 'extensions/PageNotice',
);
