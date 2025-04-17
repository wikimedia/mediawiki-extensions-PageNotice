<?php

namespace MediaWiki\Extension\PageNotice\Tests;

use MediaWiki\Config\Config;
use MediaWiki\Config\HashConfig;
use MediaWiki\Context\IContextSource;
use MediaWiki\Context\RequestContext;
use MediaWiki\Extension\PageNotice\Hooks;
use MediaWiki\Language\RawMessage;
use MediaWiki\Title\Title;
use MediaWikiIntegrationTestCase;
use Wikimedia\TestingAccessWrapper;

/**
 * @covers \MediaWiki\Extension\PageNotice\Hooks
 */
class HooksTest extends MediaWikiIntegrationTestCase {

	private function getContext( Title $title, Config $config, array $messages ): IContextSource {
		// force content model to prevent database access
		$title->setContentModel( 'wikitext' );

		$output = RequestContext::newExtraneousContext( $title )->getOutput();

		// We construct a mock context instead of using an actual RequestContext
		// since we need to return fake system messages, and this is (seemingly)
		// the only way to do so without requiring database access
		$context = $this->createMock( IContextSource::class );
		$context->method( 'getConfig' )->willReturn( $config );
		$context->method( 'getOutput' )->willReturn( $output );
		$context->method( 'getTitle' )->willReturn( $title );
		$context->method( 'msg' )->willReturnCallback( static function ( string $key, ...$params ) use ( $messages ) {
			$message = $messages[$key] ?? new RawMessage( '' );
			$message->params( ...$params );
			return $message;
		} );

		return $context;
	}

	private function addNotice( IContextSource $context, string $position ): void {
		TestingAccessWrapper::newFromObject( new Hooks( $this->getServiceContainer()->getParserFactory() ) )
			->addNotice( $context, $position );
	}

	public function testAddNoticePerPage(): void {
		$context = $this->getContext(
			Title::makeTitle( NS_MAIN, 'Ity' ),
			new HashConfig( [ 'PageNoticeDisablePerPageNotices' => false ] ),
			[
				'top-notice-Ity' => new RawMessage( 'Fumo Ity plushie' ),
				'bottom-notice-Ity' => new RawMessage( 'for sale!' ),
			],
		);

		$this->addNotice( $context, 'top' );
		$this->addNotice( $context, 'bottom' );

		$output = $context->getOutput()->getHTML();
		$this->assertStringContainsString( 'Fumo Ity plushie', $output );
		$this->assertStringContainsString( 'for sale!', $output );
	}

	public function testAddNoticePerNamespace(): void {
		$context = $this->getContext(
			Title::makeTitle( NS_MAIN, 'Sharkgirls' ),
			new HashConfig( [ 'PageNoticeDisablePerPageNotices' => false ] ),
			[
				'top-notice-ns-0' => new RawMessage( 'Trans rights are human rights' ),
				'bottom-notice-ns-0' => new RawMessage( '<nowiki>:3</nowiki>' ),
			],
		);

		$this->addNotice( $context, 'top' );
		$this->addNotice( $context, 'bottom' );

		$output = $context->getOutput()->getHTML();
		$this->assertStringContainsString( 'Trans rights are human rights', $output );
		$this->assertStringContainsString( ':3', $output );
	}

	public function testAddNoticeGlobally(): void {
		$context = $this->getContext(
			Title::makeTitle( NS_MAIN, 'Sleep is important!' ),
			new HashConfig( [ 'PageNoticeDisablePerPageNotices' => false ] ),
			[
				'top-notice-global' => new RawMessage( 'Love is love' ),
				'bottom-notice-global' => new RawMessage( 'Bisexuality is not a phase' ),
			],
		);

		$this->addNotice( $context, 'top' );
		$this->addNotice( $context, 'bottom' );

		$output = $context->getOutput()->getHTML();
		$this->assertStringContainsString( 'Love is love', $output );
		$this->assertStringContainsString( 'Bisexuality is not a phase', $output );
	}

	public function testAddNoticeDisabledPerPage(): void {
		$context = $this->getContext(
			Title::makeTitle( NS_MAIN, 'Catboys are cute' ),
			new HashConfig( [ 'PageNoticeDisablePerPageNotices' => true ] ),
			[
				'top-notice-Catboys_are_cute' => new RawMessage( 'Uh oh!' ),
				'bottom-notice-Catboys_are_cute' => new RawMessage( 'I should not exist!' ),
			],
		);

		$this->addNotice( $context, 'top' );
		$this->addNotice( $context, 'bottom' );

		$output = $context->getOutput()->getHTML();
		$this->assertSame( '', $output );
	}

	public function testNoticeAddsIndicator(): void {
		$context = $this->getContext(
			Title::makeTitle( NS_MAIN, 'Be gay do crime' ),
			new HashConfig( [ 'PageNoticeDisablePerPageNotices' => false ] ),
			[
				'top-notice-ns-0' => new RawMessage( '<indicator name="fox">Approved by the fox cabal</indicator>' ),
			],
		);

		$this->addNotice( $context, 'top' );

		$indicators = $context->getOutput()->getIndicators();
		$this->assertArrayHasKey( 'fox', $indicators );
	}

	public function testWikitextTablesAreParsedInNotices(): void {
		$context = $this->getContext(
			Title::makeTitle( NS_MAIN, 'WikitextTables' ),
			new HashConfig( [ 'PageNoticeDisablePerPageNotices' => false ] ),
			[
				'top-notice-WikitextTables' => new RawMessage( <<<EOT
{| class=\"wikitable\"
! Header 1 !! Header 2
|-
| Row 1 || Row 2
|}
EOT
				),
			],
		);

		$this->addNotice( $context, 'top' );

		$output = $context->getOutput()->getHTML();
		$this->assertStringContainsString( '<table', $output );
		$this->assertStringContainsString( '<td>Row 1</td>', $output );
	}
}
