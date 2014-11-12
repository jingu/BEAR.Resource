<?php
//
//namespace BEAR\Resource\Interceptor;
//
//use BEAR\Resource\AbstractRequest;
//use BEAR\Resource\Module\ResourceModule;
//use BEAR\Resource\Renderer\JsonRenderer;
//use BEAR\Resource\Request;
//use BEAR\Resource\ResourceObject;
//use Doctrine\Common\Annotations\AnnotationReader;
//use BEAR\Resource\NamedArgs;
//use Ray\Aop\Arguments;
//use Ray\Aop\ReflectiveMethodInvocation;
//use Ray\Di\Injector;
//use FakeVendor\Sandbox\Resource\App\Bird\Birds;
//use FakeVendor\Sandbox\Resource\App\Bird\InvalidBird;
//use FakeVendor\Sandbox\Resource\App\Bird\NotFoundBird;
//
//class EmbedInterceptorTest extends \PHPUnit_Framework_TestCase
//{
//    /**
//     * @var \BEAR\Resource\ResourceInterface
//     */
//    private $resource;
//
//    /**
//     * @var EmbedInterceptor
//     */
//    private $embedInterceptor;
//
//    protected function setUp()
//    {
//        $this->resource = (new Injector(new ResourceModule('FakeVendor\Sandbox')))->getInstance('BEAR\Resource\ResourceInterface');
//        $this->embedInterceptor = new EmbedInterceptor($this->resource, new AnnotationReader, new NamedArgs);
//    }
//
//    public function testNew()
//    {
//        $this->assertInstanceOf('BEAR\Resource\Interceptor\EmbedInterceptor', $this->embedInterceptor);
//    }
//
//    public function testInvoke()
//    {
//        $mock = new Birds;
//        $invocation = new ReflectiveMethodInvocation($mock, new \ReflectionMethod($mock,'onGet'), new Arguments(['id' => 1]), [$this->embedInterceptor]);
//        $result = $invocation->proceed();
//        $profile = $result['bird1'];
//        /** @var $profile Request */
//        $this->assertInstanceOf('BEAR\Resource\Request', $profile);
//        $this->assertSame('get app://self/bird/canary', $profile->toUriWithMethod());
//
//        return $result;
//    }
//
//    /**
//     * @depends testInvoke
//     */
//    public function testInvokeAnotherLink(ResourceObject $result)
//    {
//        $profile = $result['bird2'];
//        /** @var $profile Request */
//        $this->assertInstanceOf('BEAR\Resource\Request', $profile);
//        $this->assertSame('get app://self/bird/sparrow?id=1', $profile->toUriWithMethod());
//        return $result;
//    }
//
//    /**
//     * @depends testInvoke
//     */
//    public function testInvokeString(ResourceObject $result)
//    {
//        $result->setRenderer(new JsonRenderer);
//        $json = (string) $result;
//        $this->assertSame('{"bird1":{"name":"chill kun"},"bird2":{"sparrow_id":"1"}}', $json);
//    }
//
//    public function testEmbedAnnotation()
//    {
//        $request = $this->resource
//            ->get
//            ->uri('app://self/bird/birds')
//            ->withQuery(['id' => 1])
//            ->request();
//        /** @var $request Request */
//        $this->assertSame('app://self/bird/birds?id=1', $request->toUri());
//        $resourceObject = $request();
//        $bird1 = $resourceObject['bird1'];
//        $bird2 = $resourceObject['bird2'];
//        /** @var $bird1 Request */
//        /** @var $bird2 Request */
//        $this->assertSame('app://self/bird/canary', $bird1->toUri());
//        $this->assertSame('app://self/bird/sparrow?id=1', $bird2->toUri());
//
//        return $resourceObject['bird2'];
//    }
//
//    /**
//     * @param AbstractRequest $request
//     *
//     * @depends testEmbedAnnotation
//     */
//    public function testEmbedChangeQuery(AbstractRequest $request)
//    {
//        $request->withQuery(['id' => 100]);
//        $this->assertSame('app://self/bird/sparrow?id=100', $request->toUri());
//        $request->addQuery(['option' => 'yes']);
//        $this->assertSame('app://self/bird/sparrow?id=100&option=yes', $request->toUri());
//    }
//
//    /**
//     * @expectedException \BEAR\Resource\Exception\Embed
//     */
//    public function testNotFoundSrc()
//    {
//        $mock = new NotFoundBird;
//        $invocation = new ReflectiveMethodInvocation([$mock, 'onGet'], ['id' => 1], [$this->embedInterceptor]);
//        $invocation->proceed();
//    }
//
//    /**
//     * @expectedException \BEAR\Resource\Exception\Embed
//     */
//    public function testNotInvalidSrc()
//    {
//        $mock = new InvalidBird;
//        $invocation = new ReflectiveMethodInvocation([$mock, 'onGet'], ['id' => 1], [$this->embedInterceptor]);
//        $invocation->proceed();
//    }
//
//    public function testEmbedAnnotationResource()
//    {
//        $request = $this->resource
//            ->get
//            ->uri('app://self/bird/sparrows')
//            ->withQuery(['id_request' => 3, 'id_object' => 5, 'id_eager_request' => 7])
//            ->request();
//
//        $this->assertSame('app://self/bird/sparrows?id_request=3&id_object=5&id_eager_request=7', $request->toUri());
//
//        /** @var $request Request */
//        $resourceObject = $request();
//        $birdRequest = $resourceObject['birdRequest'];
//        $birdObject = $resourceObject['birdObject'];
//        $eagerRequestedBird = $resourceObject['eagerRequestedBird'];
//
//        $this->assertInstanceOf('BEAR\Resource\Request', $birdRequest);
//        $this->assertSame('get app://self/bird/sparrow?id=3', $birdRequest->toUriWithMethod());
//
//        $this->assertInstanceOf('FakeVendor\Sandbox\Resource\App\Bird\Sparrow', $birdObject);
//        $this->assertSame(serialize($birdObject->body), serialize(['sparrow_id' => 5]));
//
//        $this->assertInstanceOf('FakeVendor\Sandbox\Resource\App\Bird\Sparrow', $eagerRequestedBird);
//        $this->assertSame(serialize($eagerRequestedBird->body), serialize(['sparrow_id' => 7]));
//    }
//}