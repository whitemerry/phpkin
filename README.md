# phpkin
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square "Software License")](LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/whitemerry/phpkin.svg?style=flat-square&label=stable "Latest Stable Version")](https://packagist.org/packages/whitemerry/phpkin)

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
Next, define storage for traces - now implemented is only one, but you can implement own using our interface, FileLogger (Scroll below for more information about loggers in php):
```php
$logger = new FileLogger([
    'path' => './logs', // Zipkin traces logs location
    'fileName' => 'zipkin.log' // File name
]);
```
***Now you can initialize Tracer!***
Remember, more parameters with descriptions you will find in ***PHPDocs***! For example, if you are front-end application you can use PercentageSampler, tool for toggling tracing logs (You don't need to log everyting).

For front-end applications (Source for TraceId, SpanId and Sampled for other microservices):
```php
$tracer = new Tracer(
    'http://localhost/login', // Trace name
    $endpoint, // Your application meta-information
    $logger // Logger used to store/send traces
);
```
For back-end applications / microservices (Consumer of existing TraceId, SpanId and Sampled)
```php
$sampled = $_SERVER['HTTP_X_B3_Sampled']; // Remember to escape data :)
$traceId = $_SERVER['HTTP_X_B3_TraceId']; // Remember to escape data :)
$traceSpanId = $_SERVER['HTTP_X_B3_SpanId']; // Remember to escape data :)

$tracer = new Tracer(
    'http://localhost/login',
    $endpoint,
    $logger,
    $sampled,
    $traceId,
    $traceSpanId
);
```

All these lines must be initialized as soon as possible, in frameworks bootstrap.php is good place.

As last step just trigger trace method from $tracer, for example in shutdown event of your framework, or at the end of index.php
```php
$tracer->trace();
```
Now as you can see, requests to your website are generating new lines in logs/zipkin.log

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
Remember to set this headers to request in your client to other services.

#### Why FileLogger?
You can write your own logger. I prefer this type, because optimization.
It's better to send logs to Zipkin in background than increasing page load time by sending next request to API. 

Don't let your users wait :)

#### How can i upload logs to Zipkin?
Use Zipkin's rest API and send traces from zipkin.log.

How do i do that? Cron every 10 minutes, calling action witch sends POST.

You can read more about Zipkin's API endpoint [here](http://zipkin.io/zipkin-api/#/paths/%252Fspans/post)

## Unit tests
Code Coverage (Generated by PHPUnit):
- Lines: 70.35% (140 / 199)
- Functions and Methods: 52.08% (25 / 48)
- Classes and Traits: 58.33% (7 / 12)