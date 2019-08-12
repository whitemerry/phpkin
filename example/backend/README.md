# Example usage of phpkin in *backend application*
Just start application using built-in php server (in this directory)
``` bash
php -S 127.0.0.1:1234
```
Now your application is available at [http://127.0.0.1:1234/](http://127.0.0.1:1234/)

Make sure your Zipkin's application is up on `127.0.0.1:9411`.

If you have other ip/port just modify index.php at line 27.

### Are you getting error?
- Fatal error: Uncaught whitemerry\phpkin\Logger\LoggerException: Trace upload failed
   * Zipkin's URL is invalid at line 27 in index.php