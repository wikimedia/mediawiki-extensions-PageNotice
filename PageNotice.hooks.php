<?php
/**
 * Hooks for PageNotice extension
 *
 * @file
 * @ingroup Extensions
 * @author Daniel Kinzler, brightbyte.de
 * @copyright © 2007 Daniel Kinzler
 * @licence GNU General Public Licence 2.0 or later
 */

class PageNoticeHooks {
	/**
	 * Renders relevant header notices for the current page.
	 * @param Article $article
	 * @param bool $outputDone
	 * @param bool $pcache
	 * @return bool
	 */
	public static function renderHeader( Article &$article, &$outputDone, &$pcache ) {
		$out = $article->getContext()->getOutput();
		$title = $out->getTitle();
		$name = $title->getPrefixedDBKey();
		$ns = $title->getNamespace();

		$header = $out->msg( "top-notice-$name" );
		$nsheader = $out->msg( "top-notice-ns-$ns" );

		if ( !$header->isBlank() ) {
			$out->addHTML( '<div id="top-notice">' . $header->parse() . '</div>' );
		}
		if ( !$nsheader->isBlank() ) {
			$out->addHTML( '<div id="top-notice-ns">' . $nsheader->parse() . '</div>' );
		}

		return true;
	}

	/**
	 * Renders relevant footer notices for the current page.
	 * @param Article $article
	 * @return bool
	 */
	public static function renderFooter( Article $article ) {
		$out = $article->getContext()->getOutput();
		$title = $out->getTitle();
		$name = $title->getPrefixedDBKey();
		$ns = $title->getNamespace();

		$footer = $out->msg( "bottom-notice-$name" );
		$nsfooter = $out->msg( "bottom-notice-ns-$ns" );

		if ( !$footer->isBlank() ) {
			$out->addHTML( '<div id="bottom-notice">' . $footer->parse() . '</div>' );
		}
		if ( !$nsfooter->isBlank() ) {
			$out->addHTML( '<div id="bottom-notice-ns">' . $nsfooter->parse() . '</div>' );
		}

		return true;
	}
}
