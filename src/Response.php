<?php

namespace Leaf\Http;

/**
 * Leaf HTTP Response
 * -----------
 * This is a simple abstraction over top an HTTP response. This
 * provides methods to set the HTTP status, the HTTP headers,
 * and the HTTP body.
 *
 * @author Michael Darko
 * @since 1.0.0
 */
class Response
{
    /**
     * @var array
     */
    public $headers = [];

    /**
     * @var string
     */
    protected $content = '';

    /**
     * @var int HTTP status code
     */
    protected static $status = 200;

    /**
     * Output plain text
     * 
     * @param mixed $data The data to output
     * @param int $code The response status code
     */
    public function plain($data, int $code = 200)
    {
        $this->status = $code;
        $this->headers['Content-Type'] = 'text/plain';
        $this->content = $data;

        return $this;
    }

    /**
     * Output xml text
     *
     * @param string $data The data to output
     * @param int $code The response status code
     */
    public function xml($data, int $code = 200)
    {
        $this->status = $code;
        $this->headers['Content-Type'] = 'application/xml';
        $this->content = $data;

        return $this;
    }

    /**
     * Output json encoded data with an HTTP code/message
     * 
     * @param mixed $data The data to output
     * @param int $code The response status code
     * @param bool $showCode Show response code in body?
     * @param bool $useMessage Show message instead of code
     */
    public static function json($data, int $code = 200, bool $showCode = false, bool $useMessage = false)
    {
        if ($showCode) {
            $dataToPrint = ["data" => $data, "code" => $code];

            if ($useMessage) {
                $dataToPrint = ["data" => $data, "message" => self::$messages[$code] ?? $code];
            }
        } else {
            $dataToPrint = $data;
        }

        Headers::contentJSON($code);
        echo json_encode($dataToPrint);
    }

    /**
     * Output plain text
     * 
     * @param string $file Path to the file to download
     * @param string|null $name The of the file as shown to user
     * @param int $code The response status code
     */
    public static function download(string $file, string $name = null, int $code = 200)
    {
        if (!file_exists($file)) {
            Headers::contentHtml();
            trigger_error("$file not found. Confirm your file path.");
        }
        
        if ($name === null) $name = basename($file);

        Headers::status($code);
        Headers::set([
            'Content-Length' => filesize($file),
            'Content-Disposition' => "attachment; filename=$name",
        ]);
        
        readfile($file);
        exit;
    }

    /**
     * Output some data and break the application
     * 
     * @param mixed $error The data to output
     * @param int $code The Http status code
     */
    public function exit($error, int $code = 500)
    {
        $this->status = $code;

        if (is_array($error)) {
            $this->headers['Content-Type'] = 'application/json';
            $this->content = json_encode($error);
        } else {
            $this->content = $error;
        }

        $this->send();

        exit();
    }

    public function page(string $file, int $code = null)
    {
        $this->status = $code;
        $this->headers['Content-Type'] = 'text/html';
        $this->content = require $file;

        return $this;
    }

    public function markup(String $markup, int $code = null)
    {
        $this->status = $code;
        $this->headers['Content-Type'] = 'application/xml';
        $this->content = <<<EOT
$markup
EOT;

        return $this;
    }

    /**
     * set header
     * 
     * @param string|array $name Header name
     * @param string|null $value Header value
     */
    public function withHeader($name, ?string $value = '', $replace = true, int $httpCode = 200)
    {
        Headers::set($name, $value, $replace, $httpCode);
        return $this;
    }

    /**
     * Set HTTP status code
     */
    public function status($code = null)
    {
        Headers::status($code);
        return $this;
    }

    /**
     * Shorthand method of setting a cookie + value + expire time
     *
     * @param string $name The name of the cookie
     * @param string $value The value of cookie
     * @param string $expire When the cookie expires. Default: 7 days
     */
    public function withCookie(string $name, string $value, string $expire = "7 days")
    {
        if (!class_exists('Leaf\Http\Cookie')) {
            Headers::contentHtml();
            trigger_error('Leaf cookie not found. Run `leaf install cookie` or `composer require leafs/cookie`');
        }

        Cookie::simpleCookie($name, $value, $expire);

        return $this;
    }

    /**
     * Delete cookie
     *
     * @param string $name The name of the cookie
     */
    public function withoutCookie(string $name)
    {
        if (!class_exists('Leaf\Http\Cookie')) {
            Headers::contentHtml();
            trigger_error('Leaf cookie not found. Run `leaf install cookie` or `composer require leafs/cookie`');
        }

        Cookie::unset($name);

        return $this;
    }

    /**
     * Flash a piece of data to the session.
     *
     * @param string|array $name The key of the item to set
     * @param string $value The value of flash item
     */
    public function withFlash($key, string $value)
    {
        if (!class_exists('Leaf\Http\Session')) {
            Headers::contentHtml();
            trigger_error('Leaf session not found. Run `leaf install session` or `composer require leafs/session`');
        }

        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->withFlash($k, $v);
            }
        }

        \Leaf\Flash::set($key, $value);

        return $this;
    }

    /**
     * Redirect
     *
     * This method prepares this response to return an HTTP Redirect response
     * to the HTTP client.
     *
     * @param string $url The redirect destination
     * @param int $status The redirect HTTP status code
     */
    public static function redirect(string $url, int $status = 302)
    {
        Headers::status($status);
        Headers::set('Location', $url);
    }

    /**
     * Get message for HTTP status code
     * 
     * @param int $status
     * @return string|null
     */
    public static function getMessageForCode(int $status): ?string
    {
        return self::$messages[$status] ?? null;
    }
}
