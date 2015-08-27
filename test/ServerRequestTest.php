<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @see       http://github.com/zendframework/zend-diactoros for the canonical source repository
 * @copyright Copyright (c) 2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md New BSD License
 */

namespace ZendTest\Diactoros;

use PHPUnit_Framework_TestCase as TestCase;
use ReflectionProperty;
use Zend\Diactoros\ServerRequest;
use Zend\Diactoros\UploadedFile;
use Zend\Diactoros\Uri;

class ServerRequestTest extends TestCase
{
    /**
     * @var ServerRequest
     */
    protected $request;

    public function setUp()
    {
        $this->request = new ServerRequest();
    }

    public function testServerParamsAreEmptyByDefault()
    {
        $this->assertEmpty($this->request->getServerParams());
    }

    public function testQueryParamsAreEmptyByDefault()
    {
        $this->assertEmpty($this->request->getQueryParams());
    }

    public function testQueryParamsMutatorReturnsCloneWithChanges()
    {
        $value = array('foo' => 'bar');
        $request = $this->request->withQueryParams($value);
        $this->assertNotSame($this->request, $request);
        $this->assertEquals($value, $request->getQueryParams());
    }

    public function testCookiesAreEmptyByDefault()
    {
        $this->assertEmpty($this->request->getCookieParams());
    }

    public function testCookiesMutatorReturnsCloneWithChanges()
    {
        $value = array('foo' => 'bar');
        $request = $this->request->withCookieParams($value);
        $this->assertNotSame($this->request, $request);
        $this->assertEquals($value, $request->getCookieParams());
    }

    public function testUploadedFilesAreEmptyByDefault()
    {
        $this->assertEmpty($this->request->getUploadedFiles());
    }

    public function testParsedBodyIsEmptyByDefault()
    {
        $this->assertEmpty($this->request->getParsedBody());
    }

    public function testParsedBodyMutatorReturnsCloneWithChanges()
    {
        $value = array('foo' => 'bar');
        $request = $this->request->withParsedBody($value);
        $this->assertNotSame($this->request, $request);
        $this->assertEquals($value, $request->getParsedBody());
    }

    public function testAttributesAreEmptyByDefault()
    {
        $this->assertEmpty($this->request->getAttributes());
    }

    public function testSingleAttributesWhenEmptyByDefault()
    {
        $this->assertEmpty($this->request->getAttribute('does-not-exist'));
    }
    /**
     * @depends testAttributesAreEmptyByDefault
     */
    public function testAttributeMutatorReturnsCloneWithChanges()
    {
        $request = $this->request->withAttribute('foo', 'bar');
        $this->assertNotSame($this->request, $request);
        $this->assertEquals('bar', $request->getAttribute('foo'));
        return $request;
    }

    /**
     * @depends testAttributeMutatorReturnsCloneWithChanges
     */
    public function testRemovingAttributeReturnsCloneWithoutAttribute($request)
    {
        $new = $request->withoutAttribute('foo');
        $this->assertNotSame($request, $new);
        $this->assertNull($new->getAttribute('foo', null));
    }

    public function provideMethods()
    {
        return array(
            'post' => array('POST', 'POST'),
            'get'  => array('GET', 'GET'),
            'null' => array(null, 'GET'),
        );
    }

    /**
     * @dataProvider provideMethods
     */
    public function testUsesProvidedConstructorArguments($parameterMethod, $methodReturned)
    {
        $server = array(
            'foo' => 'bar',
            'baz' => 'bat',
        );

        $server['server'] = true;

        $files = array(
            'files' => new UploadedFile('php://temp', 0, 0),
        );

        $uri = new Uri('http://example.com');
        $headers = array(
            'host' => array('example.com'),
        );

        $request = new ServerRequest(
            $server,
            $files,
            $uri,
            $parameterMethod,
            'php://memory',
            $headers
        );

        $this->assertEquals($server, $request->getServerParams());
        $this->assertEquals($files, $request->getUploadedFiles());

        $this->assertSame($uri, $request->getUri());
        $this->assertEquals($methodReturned, $request->getMethod());
        $this->assertEquals($headers, $request->getHeaders());

        $body = $request->getBody();
        $r = new ReflectionProperty($body, 'stream');
        $r->setAccessible(true);
        $stream = $r->getValue($body);
        $this->assertEquals('php://memory', $stream);
    }

    /**
     * @group 46
     */
    public function testCookieParamsAreAnEmptyArrayAtInitialization()
    {
        $request = new ServerRequest();
        $this->assertInternalType('array', $request->getCookieParams());
        $this->assertCount(0, $request->getCookieParams());
    }

    /**
     * @group 46
     */
    public function testQueryParamsAreAnEmptyArrayAtInitialization()
    {
        $request = new ServerRequest();
        $this->assertInternalType('array', $request->getQueryParams());
        $this->assertCount(0, $request->getQueryParams());
    }

    /**
     * @group 46
     */
    public function testParsedBodyIsNullAtInitialization()
    {
        $request = new ServerRequest();
        $this->assertNull($request->getParsedBody());
    }
}
