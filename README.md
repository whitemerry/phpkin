# phpkin
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square "Software License")](LICENSE)
[![Latest Stable Version](https://img.shields.io/packagist/v/whitemerry/phpkin.svg?style=flat-square&label=stable "Latest Stable Version")](https://packagist.org/packages/whitemerry/phpkin) [![OpenTracing Badge](https://img.shields.io/badge/OpenTracing-enabled-blue.svg)](http://opentracing.io)
[![Maintainability](https://api.codeclimate.com/v1/badges/a785fa78ec069394b21d/maintainability)](https://codeclimate.com/github/whitemerry/phpkin/maintainability)
[![Tests](https://github.com/whitemerry/phpkin/actions/workflows/tests.yml/badge.svg)](https://github.com/whitemerry/phpkin/actions/workflows/tests.yml)

First ***production ready***, simple and full Zipkin implementation without dependencies.

Compatible with both front and back-end applications and respects B3 Propagation.

## Installing via Composer
```bash
$ composer require whitemerry/phpkin
```

## Upgrading to 2.0
`Tracer` used to serialize each `Span` the moment it was added. It now keeps the `Span` objects and serializes them once, in `trace()`, which is what makes `getSpans()` possible. What Zipkin receives is unchanged, but five things behave differently.

#### The trace span is parented to its caller
A back-end application used to give its trace span its own SpanId as the ParentId, so the span came out as its own parent and the caller propagated in `X-B3-ParentSpanId` was ignored. That header is now the seventh `Tracer` argument and links the span to its caller:
```php
$tracer = new Tracer($name, $endpoint, $logger, $isSampled, $traceId, $traceSpanId, $parentSpanId);
```
Leave it `null` and the span stays a root, which is what a front-end application wants.

`Tracer::setProfile()`, `Tracer::FRONTEND` and `Tracer::BACKEND` are gone, because whether the span has a parent now follows from `$parentSpanId` alone. Drop the call; it fails at bootstrap rather than quietly doing nothing, which is the point - an application that kept calling it without passing `$parentSpanId` would go from self-parented spans to root spans with nothing to show anything had changed.

#### Identifiers reject an invalid string
`SpanIdentifier` and `TraceIdentifier` used to generate a fresh random identifier when handed something that was not a valid one, so a malformed B3 header produced a span attached to an id that had never existed. They now throw `InvalidArgumentException`:
```php
new SpanIdentifier('garbage');            // throws in 2.0, was a random identifier in 1.x
new SpanIdentifier();                     // still generates one
new SpanIdentifier('');                   // still generates one - an absent header reads as ''
```
Guard anything coming from a header with `is_zipkin_span_identifier()` / `is_zipkin_trace_identifier()`, as the back-end example below does.

#### `addSpan()` only accepts `Span`
Passing anything else throws `InvalidArgumentException`. Serialization used to happen inside `addSpan()`, so any object with a `toArray()` method quietly worked:
```php
// Worked in 1.x by accident, throws in 2.0
$tracer->addSpan($myOwnSpanClass);
```
If you have such a class, extend `Span` or build a real `Span` from it.

#### Spans are serialized at `trace()` time
Anything you change on a `Span` after adding it now ends up in the trace. Sharing one `Metadata` instance between spans is the case that bites, because `Metadata::set()` appends:
```php
$metadata = new Metadata();

$metadata->set(Metadata::HTTP_PATH, '/first');
$tracer->addSpan(new Span($firstId, 'First', $firstAnnotations, $metadata));

$metadata->set(Metadata::HTTP_PATH, '/second');
$tracer->addSpan(new Span($secondId, 'Second', $secondAnnotations, $metadata));
```
In 1.x the first span reported one annotation and the second reported two. In 2.0 both report both. Give each `Span` its own `Metadata` to keep the old result.

#### `Tracer::$spans` holds `Span` objects
Only relevant if you extend `Tracer` and read the protected property - it used to hold arrays. Use `getSpans()` where you can, and call `toArray()` yourself if you need the serialized form.

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

For front-end applications (Source for TraceId, SpanId and Sampled for other microservices) there is nothing to
consume, so the tracer generates the identifiers itself and starts the trace:
```php
$tracer = new Tracer(
    'http://localhost/login', // Trace name
    $endpoint, // Your application meta-information
    $logger // Logger used to store/send traces
);
```
For back-end applications / microservices (Consumer of existing TraceId, SpanId, ParentSpanId and Sampled) read the
B3 headers the caller sent you and hand them to the tracer:
```php
$traceId = null;
if (!empty($_SERVER['HTTP_X_B3_TRACEID']) && is_zipkin_trace_identifier($_SERVER['HTTP_X_B3_TRACEID'])) {
    $traceId = new TraceIdentifier($_SERVER['HTTP_X_B3_TRACEID']);
}

$traceSpanId = null;
if (!empty($_SERVER['HTTP_X_B3_SPANID']) && is_zipkin_span_identifier($_SERVER['HTTP_X_B3_SPANID'])) {
    $traceSpanId = new SpanIdentifier($_SERVER['HTTP_X_B3_SPANID']);
}

$parentSpanId = null;
if (!empty($_SERVER['HTTP_X_B3_PARENTSPANID']) && is_zipkin_span_identifier($_SERVER['HTTP_X_B3_PARENTSPANID'])) {
    $parentSpanId = new SpanIdentifier($_SERVER['HTTP_X_B3_PARENTSPANID']);
}

$isSampled = null;
if (!empty($_SERVER['HTTP_X_B3_SAMPLED'])) {
    $isSampled = (bool) $_SERVER['HTTP_X_B3_SAMPLED'];
}

$tracer = new Tracer(
    'http://localhost/login',
    $endpoint,
    $logger,
    $isSampled,
    $traceId,
    $traceSpanId,
    $parentSpanId
);
```

The three identifiers play different roles, and mixing them up is the easiest way to end up with a broken trace:

| Argument | Header | Meaning |
| --- | --- | --- |
| `$traceId` | `X-B3-TraceId` | The whole trace, shared by every service taking part in it |
| `$traceSpanId` | `X-B3-SpanId` | This request's own span |
| `$parentSpanId` | `X-B3-ParentSpanId` | The caller's span, the one this request hangs under |

***Keep the validity checks.*** Headers arrive from outside your application, and `SpanIdentifier` and
`TraceIdentifier` throw on anything that is not a valid identifier. Guarding with `is_zipkin_span_identifier()` /
`is_zipkin_trace_identifier()` means a broken - or hostile - caller costs you the link to that caller rather than the
request itself.

Leave `$parentSpanId` as `null` whenever no traced caller propagated one, and the trace starts at your application.
A front-end taking a request straight from a browser is the usual case - something called you, but it was not part of
the trace and sent no B3 headers. A cron job or a queue consumer works the same way.

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
X-B3-SpanId = $spanIdentifier;              // The span you just created for this call
X-B3-ParentSpanId = TracerInfo::getTraceSpanId(); // Your own span, the parent of that call
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

#### Reading spans back
Spans stay on the tracer, so you can inspect them - handy in tests or when you need to report them somewhere else:
```php
foreach ($tracer->getSpans() as $span) {
    $data = $span->toArray();
}
```
The span describing the trace itself is built by `trace()` (its end timestamp is not known before that), so call `getSpans()` after `trace()` to get the complete set.
When the trace is not sampled nothing is collected and `getSpans()` stays empty.

Spans are serialized once, by `trace()`. Anything you change on a `Span` after `addSpan()` ends up in the trace - including a `Metadata` instance shared between spans, which will report every annotation set on it, not only the ones set before that span was added. Give each `Span` its own `Metadata` if you don't want that.

#### Calling tracer statically
You can get access to tracer statically, in every place of your project, just init TracerProxy:
```php
$tracer = new Tracer(...); // Your tracer instance
TracerProxy::init($tracer);
```
Now you have access to methods like:
```php
TracerProxy::addSpan($span);
TracerProxy::getSpans();
TracerProxy::trace();
```

#### Where do i have information about this trace?
All meta information are in static class TracerInfo
```php
TracerInfo::getTraceId(); // TraceId - X-B3-TraceId
TracerInfo::getTraceSpanId(); // This request's own SpanId - X-B3-SpanId
TracerInfo::isSampled(); // Sampled - X-B3-Sampled
```
`getTraceSpanId()` returns the span of *this* request, not its parent. When you call another service it becomes that
service's `X-B3-ParentSpanId`, which is where the name confusion tends to come from.

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

## TODO
- AsyncHttpLogger (Based on CURL)
- *Upload to zipkin* cron for FileLogger
---
Inspired by [Tolerance](https://github.com/Tolerance/Tolerance)
