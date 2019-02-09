<?php

namespace alsvanzelf\jsonapiTests;

use alsvanzelf\jsonapi\ErrorsDocument;
use alsvanzelf\jsonapi\exceptions\InputException;
use PHPUnit\Framework\TestCase;

class ErrorsDocumentTest extends TestCase {
	public function testFromException_HappyPath() {
		$document = ErrorsDocument::fromException(new \Exception('foo', 42));
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('errors', $array);
		$this->assertCount(1, $array['errors']);
		$this->assertArrayHasKey('code', $array['errors'][0]);
		$this->assertSame('42', $array['errors'][0]['code']);
	}
	
	/**
	 * @group non-php5
	 */
	public function testFromException_AllowsThrowable() {
		if (PHP_MAJOR_VERSION < 7) {
			$this->markTestSkipped('can not run in php5');
			return;
		}
		
		$document = ErrorsDocument::fromException(new \Error('foo', 42));
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('errors', $array);
		$this->assertCount(1, $array['errors']);
		$this->assertArrayHasKey('code', $array['errors'][0]);
		$this->assertSame('42', $array['errors'][0]['code']);
	}
	
	public function testFromException_BlocksNonException() {
		$this->expectException(InputException::class);
		
		ErrorsDocument::fromException(new \stdClass());
	}
	
	public function testAddException_WithPrevious() {
		$exception = new \Exception('foo', 1, new \Exception('bar', 2));
		
		$document = new ErrorsDocument();
		$document->addException($exception);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('errors', $array);
		$this->assertCount(2, $array['errors']);
		$this->assertArrayHasKey('code', $array['errors'][0]);
		$this->assertArrayHasKey('code', $array['errors'][1]);
		$this->assertSame('1', $array['errors'][0]['code']);
		$this->assertSame('2', $array['errors'][1]['code']);
	}
	
	public function testAddException_SkipPrevious() {
		$exception = new \Exception('foo', 1, new \Exception('bar', 2));
		$options   = ['exceptionSkipPrevious' => true];
		
		$document = new ErrorsDocument();
		$document->addException($exception, $options);
		
		$array = $document->toArray();
		
		$this->assertArrayHasKey('errors', $array);
		$this->assertCount(1, $array['errors']);
		$this->assertArrayHasKey('code', $array['errors'][0]);
		$this->assertSame('1', $array['errors'][0]['code']);
	}
	
	public function testAddException_BlocksNonException() {
		$document = new ErrorsDocument();
		
		$this->expectException(InputException::class);
		
		$document->addException(new \stdClass());
	}
	
	/**
	 * @dataProvider dataProviderDetermineHttpStatusCode_HappyPath
	 */
	public function testDetermineHttpStatusCode_HappyPath($expectedAdvisedErrorCode, $allErrorCodes) {
		$document = new ErrorsDocument();
		
		$method = new \ReflectionMethod($document, 'determineHttpStatusCode');
		$method->setAccessible(true);
		
		foreach ($allErrorCodes as $errorCode) {
			$advisedErrorCode = $method->invoke($document, $errorCode);
		}
		
		$this->assertSame($expectedAdvisedErrorCode, $advisedErrorCode);
	}
	
	public function dataProviderDetermineHttpStatusCode_HappyPath() {
		return [
			[422, [422]],
			[422, [422, 422]],
			[400, [422, 404]],
			[400, [400]],
			[501, [501]],
			[501, [501, 501]],
			[500, [501, 503]],
			[500, [422, 404, 501, 503]],
			[500, [500]],
			[302, [302]],
		];
	}
}
