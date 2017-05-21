# phpkin
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square "Software License")](LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/whitemerry/phpkin.svg?style=flat-square&label=stable "Latest Stable Version")](https://packagist.org/packages/whitemerry/phpkin) [![OpenTracing Badge](https://img.shields.io/badge/OpenTracing-enabled-blue.svg)](http://opentracing.io)

First ***production ready***, simple and full Zipkin implementation without dependencies.

Compatible with both front and back-end applications and respects B3 Propagation.

## Installing via Composer
```bash
$ composer require whitemerry/phpkin
```

## Documentation

#### Short implementation information
In this project BinaryAnnotations are Metadata and annotations are replaced by AnnotationBlock witch allow you to create Annotations for Spans faster, and cleaner.
All of these methods have more parameters than used here, read PHPDocs and remember, you can change everything by implementing interfaces or extending classes.

#### Let's get started
First, very important step is defining your service meta-information for tracer:
```php
$endpoint = new Endpoint(
    'My application', // Application name
    '127.0.0.1', // Current application IP address
    '80' // Current application port (default 80)
);
```
Next, define storage for traces - currently two types are supported - SimpleHttpLogger witch automatically sends trace data to Zipkin's service and
FileLogger (you can read more about this below):
```php
$logger = new SimpleHttpLogger([
    'host' => 'http://192.168.33.11:9411' // Zipkin's API host with schema (http://) and without trailing slash
]);
```
***Now you can initialize Tracer!***

For front-end applications (Source for TraceId, SpanId and Sampled for other microservices):
```php
$tracer = new Tracer(
    'http://localhost/login', // Trace name
    $endpoint, // Your application meta-information
    $logger // Logger used to store/send traces
);
$tracer->setProfile(Tracer::FRONTEND);
```
For back-end applications / microservices (Consumer of existing TraceId, SpanId and Sampled)
```php
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

$tracer = new Tracer(
    'http://localhost/login',
    $endpoint,
    $logger,
    $sampled,
    $traceId,
    $traceSpanId
);
$tracer->setProfile(Tracer::BACKEND);
```

All these lines must be initialized as soon as possible, in frameworks bootstrap.php is good place.

There are more parameters with descriptions in ***PHPDocs***! 
For example, if you are front-end application you can use PercentageSampler, tool for toggling tracing logs (You don't need to log everything).


As last step just trigger trace method from $tracer, for example in shutdown event of your framework, or at the end of index.php
```php
$tracer->trace();
```
Now as you can see, you have new entries in the Zipkin's UI! :)

#### Adding spans to trace
As you already now, in Zipkin, you can store and visualize communication between 2 services (for example databases, microservices). 
So, you need to create Span (Zipkin's block of information about request):
```php
// Before request - read current timestamp in zipkin format
$requestStartTimestamp = zipkin_timestamp();
$spanIdentifier = new SpanIdentifier();

/* 
...
Request logic
Remember, you need to add B3 headers to your request:
X-B3-TraceId = TracerInfo::getTraceId();
X-B3-SpanId = $spanIdentifier;
X-B3-Sampled = TracerInfo::isSampled();
*/

$endpoint = new Endpoint(
    'Accounts microservice', // Name of service you're connecting with
    '127.0.1.1', // This service Ip
    '8000' // And port
);

$annotationBlock = new AnnotationBlock(
    $endpoint,
    $requestStartTimestamp
);

$span = new Span(
    $spanIdentifier,
    'Authorize user',
    $annotationBlock
);
```
And add to tracer
```php
$tracer->addSpan($span);
```

#### Calling tracer statically
You can get access to tracer statically, in every place of your project, just init TracerProxy:
```php
$tracer = new Tracer(...); // Your tracer instance
TracerProxy::init($tracer);
```
Now you have access to methods like:
```php
TracerProxy::addSpan($span);
TracerProxy::trace();
```

#### Where do i have information about this trace?
All meta information are in static class TracerInfo
```php
TracerInfo::getTraceId(); // TraceId - X-B3-TraceId
TracerInfo::getTraceSpanId(); // ParentId - X-B3-ParentId
TracerInfo::isSampled(); // Sampled - X-B3-Sampled
```

#### Making requests to other service
Take a look at our [examples](https://github.com/whitemerry/phpkin/tree/master/example). You need to set B3 header by your own in yours rest/api/guzzle client.

#### Differences between loggers
SimpleHttpLogger - Allows you to try zipkin right away, by uploading logs at the end of user request to webiste. 
However, it will delay the response back to the user.

FileLogger - Allows you to setup asynchronous reporting to zipkin. While this is a synchronous write to disk, in practice latency impact to callers is minimal, but you need to write *upload to zipkin* tool by your own.

For more info read [this ticket](https://github.com/whitemerry/phpkin/issues/2)!

#### Are logs automatically uploaded to Zipkin?
For SimpleHttpLogger, short answer, ***yes***

For FileLogger, bit logner answer, you need to upload logs from *zipkin.log* to Zipkin by your own, for example by cron working in background making POST's to the [Zipkin (API)](http://zipkin.io/zipkin-api/#/paths/%252Fspans/post)

## Unit tests
Code Coverage (Generated by PHPUnit):
- Lines: 70.35% (140 / 199)
- Functions and Methods: 52.08% (25 / 48)
- Classes and Traits: 58.33% (7 / 12)

## TODO
- AsyncHttpLogger (Based on CURL)
- *Upload to zipkin* cron for FileLogger
---
Inspired by [Tolerance](https://github.com/Tolerance/Tolerance)
