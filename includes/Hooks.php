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

use MediaWiki\Context\IContextSource;
use MediaWiki\Html\Html;
use MediaWiki\Output\OutputPage;
use MediaWiki\Page\Article;
use MediaWiki\Page\Hook\ArticleViewFooterHook;
use MediaWiki\Page\Hook\ArticleViewHeaderHook;
use MediaWiki\Parser\Parser;
use MediaWiki\Parser\ParserFactory;
use MediaWiki\Parser\ParserOptions;
use Wikimedia\Assert\Assert;

class Hooks implements ArticleViewHeaderHook, ArticleViewFooterHook {

	private ?Parser $parser = null;

	public function __construct(
		private readonly ParserFactory $parserFactory,
	) {
	}

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
				$this->wrapPageNotice( $out, $header->plain(), "ext-pagenotice-$position-notice",
					"$position-notice" );
			}
		}

		// Messages:
		// * top-notice-ns-<namespace id>
		// * bottom-notice-ns-<namespace id>
		$nsheader = $context->msg( "$position-notice-ns-$ns" );
		if ( !$nsheader->isBlank() ) {
			$this->wrapPageNotice( $out, $nsheader->plain(), "ext-pagenotice-$position-notice-ns",
				"$position-notice-ns" );
		}

		// Messages:
		// * top-notice-global
		// * bottom-notice-global
		$globalheader = $context->msg( "$position-notice-global" );
		if ( !$globalheader->isBlank() ) {
			// The ID for the inner <div> wrapper is intentionally not provided here as the previous ones
			// were (mostly) for backwards compatibility
			$this->wrapPageNotice( $out, $globalheader->plain(), "ext-pagenotice-$position-notice-global",
				null );
		}
	}

	/**
	 * Wraps the given page notice in a <div> with the given class.
	 * Optionally adds an inner wrapper with an ID, used for backwards compatibility.
	 *
	 * @param OutputPage $out The output page to add the notice to
	 * @param string $wikitext The wikitext of the notice
	 * @param string $wrapperClass The class to add to the outer <div>
	 * @param ?string $innerWrapperId The ID to add to the inner <div> wrapper, or null if not needed
	 */
	private function wrapPageNotice(
		OutputPage $out, string $wikitext, string $wrapperClass, ?string $innerWrapperId
	): void {
		$this->parser ??= $this->parserFactory->create();
		// workaround for T392226 to properly parse tables
		$this->parser->startExternalParse( $out->getTitle(), ParserOptions::newFromContext( $out->getContext() ),
			Parser::OT_HTML );

		$result = $this->parser->recursiveTagParseFully( $wikitext );
		// remove outer <p> if present to avoid unwanted margins around the notice
		$result = Parser::stripOuterParagraph( $result );

		// add metadata to support page indicators
		$out->addParserOutputMetadata( $this->parser->getOutput() );
		if ( $innerWrapperId !== null ) {
			// add inner wrapper with an ID for backwards compatibility
			$result = Html::rawElement( 'div', [ 'id' => $innerWrapperId ], $result );
		}
		$out->addHTML( Html::rawElement( 'div', [ 'class' => $wrapperClass ], $result ) );
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
