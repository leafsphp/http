<?php

if (!function_exists('request')) {
    /**
     * Return request or request data
     *
     * @param array|string $data — Get data from request
     */
    function request($data = null)
    {
        if ($data) return \Leaf\Http\Request::get($data);
        return new \Leaf\Http\Request();
    }
}

if (!function_exists('response')) {
    /**
     * Return response or set response data
     *
     * @param array|string $data — The JSON response to set
     */
    function response($data = null)
    {
        if ($data) return \Leaf\Http\Response::json($data);
        return new \Leaf\Http\Response();
    }
}

if (!function_exists('setHeader')) {
    /**
     * Set a response header
     *
     * @param string|array $key The header key
     * @param string $value Header value
     * @param bool $replace Replace header if exists
     * @param mixed|null $code Status code
     */
    function setHeader($key, $value = "", $replace = true, $code = 200)
    {
        app()->headers()->set($key, $value, $replace, $code);
    }
}
