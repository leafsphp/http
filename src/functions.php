<?php

if (!function_exists('request')) {
    /**
     * Return request or request data
     *
     * @param array|string $data — Get data from request
     */
    function request($data = null)
    {
        if ($data !== null) return \Leaf\Http\Request::get($data);

        if (class_exists('\Leaf\Config')) {
            $request = Leaf\Config::get("request")["instance"] ?? null;

            if (!$request) {
                $request = new \Leaf\Http\Request;
                Leaf\Config::set("request", ["instance" => $request]);
            }

            return $request;
        }

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
        if ($data !== null) return \Leaf\Http\Response::json($data);

        if (class_exists('\Leaf\Config')) {
            $response = Leaf\Config::get("response")["instance"] ?? null;

            if (!$response) {
                $response = new \Leaf\Http\Response;
                Leaf\Config::set("response", ["instance" => $response]);
            }

            return $response;
        }

        return new \Leaf\Http\Response();
    }
}
