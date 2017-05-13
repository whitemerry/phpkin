<?php
/**
 * Load composer's autoload
 */
require_once '../../vendor/autoload.php';

use whitemerry\phpkin\Tracer;
use whitemerry\phpkin\Endpoint;
use whitemerry\phpkin\Span;
use whitemerry\phpkin\Identifier\SpanIdentifier;
use whitemerry\phpkin\Identifier\TraceIdentifier;
use whitemerry\phpkin\AnnotationBlock;
use whitemerry\phpkin\Logger\SimpleHttpLogger;
use whitemerry\phpkin\TracerInfo;

/**
 * Initialize tracer, and setup info about you application
 */
$endpoint = new Endpoint('Example backend app', '127.0.0.1', '1234');

/**
 * Create logger to Zipkin, host is Zipkin's ip
 * Read more about loggers https://github.com/whitemerry/phpkin#why-do-i-prefer-filelogger
 * 
 * Make sure host is available with http:// and port because SimpleHttpLogger does not throw error on failure
 * For debug purposes you can disable muteErrors
 */
$logger = new SimpleHttpLogger(['host' => 'http://127.0.0.1:9411', 'muteErrors' => false]);

/**
 * Read headers
 */
$traceId = null;
if (!empty($_SERVER['HTTP_X_B3_TRACEID'])) {
    $traceId = new TraceIdentifier($_SERVER['HTTP_X_B3_TRACEID']);
}

$traceSpanId = null;
if (!empty($_SERVER['HTTP_X_B3_SPANID'])) {
    $traceSpanId = new SpanIdentifier($_SERVER['HTTP_X_B3_SPANID']);
}

$isSampled = null;
if (!empty($_SERVER['HTTP_X_B3_SAMPLED'])) {
    $isSampled = (bool) $_SERVER['HTTP_X_B3_SAMPLED'];
}

/**
 * And create tracer object, if you want to have statically access just initialize TracerProxy
 * TracerProxy::init($tracer);
 */
$tracer = new Tracer('get /index.php', $endpoint, $logger, $isSampled, $traceId, $traceSpanId);
$tracer->setProfile(Tracer::BACKEND);

/**
 * Here is place for your application logic, we are making request to example REST API
 */
$url = 'https://jsonplaceholder.typicode.com/posts/1/comments';
$requestStart = zipkin_timestamp();
$spanId = new SpanIdentifier();

// Context for file_get_contents for passing headers (B3 propagation)
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' =>
            'X-B3-TraceId: ' . TracerInfo::getTraceId() . "\r\n" .
            'X-B3-SpanId: ' . ((string) $spanId) . "\r\n" .
            'X-B3-ParentSpanId: ' . TracerInfo::getTraceSpanId() . "\r\n" .
            'X-B3-Sampled: ' . ((int) TracerInfo::isSampled()) . "\r\n"
    ]
]);

$request = file_get_contents($url, false, $context);

// Setup zipkin data for this request
$endpoint = new Endpoint('jsonplaceholder API', '104.31.87.157', '80');
$annotationBlock = new AnnotationBlock($endpoint, $requestStart);
$span = new Span($spanId, 'get /posts/1/comments', $annotationBlock);

// Add span to Zipkin
$tracer->addSpan($span);

// Sleep as a placeholder for logic
sleep(1);

// Again your application logic
echo 'Example data from rest api: <br>' . $request;

/**
 * Send data to Zipkin! :)
 * You're done
 */
$tracer->trace();
