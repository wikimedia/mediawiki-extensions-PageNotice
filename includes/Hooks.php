<?php
/**
 * Hooks for PageNotice extension
 *
 * @file
 * @ingroup Extensions
 * @author Daniel Kinzler, brightbyte.de
 * @copyright © 2007 Daniel Kinzler
 * @license GPL-2.0-or-later
 */

namespace MediaWiki\Extension\PageNotice;

use Article;
use Html;

class Hooks {
	/**
	 * Renders relevant header notices for the current page.
	 * @param Article $article
	 * @param bool &$outputDone
	 * @param bool &$pcache
	 */
	public static function onArticleViewHeader( Article $article, &$outputDone, &$pcache ) {
		$pageNoticeDisablePerPageNotices = $article->getContext()
			->getConfig()
			->get( 'PageNoticeDisablePerPageNotices' );

		$out = $article->getContext()->getOutput();
		$title = $out->getTitle();
		$name = $title->getPrefixedDBKey();
		$ns = $title->getNamespace();

		$header = $out->msg( "top-notice-$name" );
		$nsheader = $out->msg( "top-notice-ns-$ns" );

		$needStyles = false;

		if ( !$pageNoticeDisablePerPageNotices && !$header->isBlank() ) {
			$out->addHTML(
				Html::rawElement(
					'div',
					[ 'id' => 'top-notice' ],
					$header->parse()
				)
			);
			$needStyles = true;
		}
		if ( !$nsheader->isBlank() ) {
			$out->addHTML(
				Html::rawElement(
					'div',
					[ 'id' => 'top-notice-ns' ],
					$nsheader->parse()
				)
			);
			$needStyles = true;
		}

		if ( $needStyles ) {
			$out->addModuleStyles( 'ext.pageNotice' );
		}
	}

	/**
	 * Renders relevant footer notices for the current page.
	 * @param Article $article
	 * @param bool $patrolFooterShown
	 */
	public static function onArticleViewFooter( Article $article, $patrolFooterShown ) {
		$pageNoticeDisablePerPageNotices = $article->getContext()
			->getConfig()
			->get( 'PageNoticeDisablePerPageNotices' );

		$out = $article->getContext()->getOutput();
		$title = $out->getTitle();
		$name = $title->getPrefixedDBKey();
		$ns = $title->getNamespace();

		$footer = $out->msg( "bottom-notice-$name" );
		$nsfooter = $out->msg( "bottom-notice-ns-$ns" );

		$needStyles = false;

		if ( !$pageNoticeDisablePerPageNotices && !$footer->isBlank() ) {
			$out->addHTML( '<div id="bottom-notice">' . $footer->parse() . '</div>' );
			$needStyles = true;
		}
		if ( !$nsfooter->isBlank() ) {
			$out->addHTML( '<div id="bottom-notice-ns">' . $nsfooter->parse() . '</div>' );
			$needStyles = true;
		}

		if ( $needStyles ) {
			$out->addModuleStyles( 'ext.pageNotice' );
		}
	}
}
