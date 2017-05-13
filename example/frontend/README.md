# Example usage of phpkin in *frontend application*
Just start application using built-in php server (in this directory)
``` bash
php -S 127.0.0.1:1234
```
And make sure your Zipkin's application is up on `127.0.0.1:9411`. Otherwise start it and modify index.php with your Zipkin's instance ip address.

### Are you getting error?
- Fatal error: Uncaught whitemerry\phpkin\Logger\LoggerException: Trace upload failed
   * Zipkin's URL is invalid at line 27 in index.php