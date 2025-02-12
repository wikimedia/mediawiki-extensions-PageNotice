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
use MediaWiki\Context\IContextSource;
use MediaWiki\Page\Hook\ArticleViewFooterHook;
use MediaWiki\Page\Hook\ArticleViewHeaderHook;
use Wikimedia\Assert\Assert;

class Hooks implements ArticleViewHeaderHook, ArticleViewFooterHook {

	private function addNotice( IContextSource $context, string $position ): void {
		Assert::parameter(
			in_array( $position, [ 'top', 'bottom' ], true ),
			'position',
			'must be "top" or "bottom"',
		);

		$out = $context->getOutput();
		$title = $context->getTitle();
		$name = $title->getPrefixedDBKey();
		$ns = $title->getNamespace();

		if ( !$context->getConfig()->get( 'PageNoticeDisablePerPageNotices' ) ) {
			// Messages:
			// * top-notice-<page name with ucfirst and spaces to underscores>
			// * bottom-notice-<page name with ucfirst and spaces to underscores>
			$header = $context->msg( "$position-notice-$name" );
			if ( !$header->isBlank() ) {
				$wikitext = "<div id='$position-notice'>{$header->plain()}</div>";
				$out->wrapWikiTextAsInterface( "ext-pagenotice-$position-notice", $wikitext );
				$out->addModuleStyles( 'ext.pageNotice' );
			}
		}

		// Messages:
		// * top-notice-ns-<namespace id>
		// * bottom-notice-ns-<namespace id>
		$nsheader = $context->msg( "$position-notice-ns-$ns" );
		if ( !$nsheader->isBlank() ) {
			$wikitext = "<div id='$position-notice-ns'>{$nsheader->plain()}</div>";
			$out->wrapWikiTextAsInterface( "ext-pagenotice-$position-notice-ns", $wikitext );
			$out->addModuleStyles( 'ext.pageNotice' );
		}

		// Messages:
		// * top-notice-global
		// * bottom-notice-global
		$globalheader = $context->msg( "$position-notice-global" );
		if ( !$globalheader->isBlank() ) {
			// The <div> ID wrapper is intentionally omitted here as the previous ones
			// were (mostly) for backwards compatibility
			$out->wrapWikiTextAsInterface( "ext-pagenotice-$position-notice-global", $globalheader->plain() );
		}
	}

	/**
	 * Renders relevant header notices for the current page.
	 * @param Article $article
	 * @param bool &$outputDone
	 * @param bool &$pcache
	 */
	public function onArticleViewHeader( $article, &$outputDone, &$pcache ) {
		$this->addNotice( $article->getContext(), 'top' );
	}

	/**
	 * Renders relevant footer notices for the current page.
	 * @param Article $article
	 * @param bool $patrolFooterShown
	 */
	public function onArticleViewFooter( $article, $patrolFooterShown ) {
		$this->addNotice( $article->getContext(), 'bottom' );
	}
}
