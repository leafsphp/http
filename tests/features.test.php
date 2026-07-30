<?php

// v5 additions: request()->object(), view/render status codes, and
// range-aware streaming downloads — all against the real test server.

test('request body is available as an object', function () {
    // https://github.com/leafsphp/http/issues/38
    $res = call('/object', [
        'method' => 'POST',
        'headers' => ['Content-Type: application/json'],
        'body' => json_encode(['name' => 'Mika', 'meta' => ['tag' => 'x'], 'tags' => ['a', 'b']]),
    ]);

    expect($res['isObject'])->toBeTrue()
        ->and($res['name'])->toBe('Mika')
        ->and($res['nested'])->toBe('x')
        ->and($res['list'])->toBe(['a', 'b']); // lists stay arrays
});

test('view can set an http status code', function () {
    // https://github.com/leafsphp/http/issues/39
    $res = callRaw('/view-status');

    expect($res['status'])->toBe(404)
        ->and($res['body'])->toContain('rendered:demo');
});

test('render can set an http status code', function () {
    $res = callRaw('/render-status');

    expect($res['status'])->toBe(201)
        ->and($res['body'])->toContain('rendered:demo');
});

test('downloads send the full file with range support advertised', function () {
    $res = callRaw('/download');

    expect($res['status'])->toBe(200)
        ->and($res['body'])->toBe('0123456789')
        ->and($res['headers'])->toContain('Accept-Ranges: bytes')
        ->and($res['headers'])->toContain('Content-Length: 10');
});

test('downloads honor byte ranges with 206 partial content', function () {
    $res = callRaw('/download', ['headers' => ['Range: bytes=2-5']]);

    expect($res['status'])->toBe(206)
        ->and($res['body'])->toBe('2345')
        ->and($res['headers'])->toContain('Content-Range: bytes 2-5/10')
        ->and($res['headers'])->toContain('Content-Length: 4');
});

test('downloads honor open-ended and suffix ranges', function () {
    $openEnded = callRaw('/download', ['headers' => ['Range: bytes=7-']]);
    $suffix = callRaw('/download', ['headers' => ['Range: bytes=-3']]);

    expect($openEnded['status'])->toBe(206)
        ->and($openEnded['body'])->toBe('789')
        ->and($suffix['status'])->toBe(206)
        ->and($suffix['body'])->toBe('789')
        ->and($suffix['headers'])->toContain('Content-Range: bytes 7-9/10');
});

test('unsatisfiable ranges get 416 with the total size', function () {
    $res = callRaw('/download', ['headers' => ['Range: bytes=99-']]);

    expect($res['status'])->toBe(416)
        ->and($res['headers'])->toContain('Content-Range: bytes */10');
});

test('malformed range headers fall back to a full 200 download', function () {
    $res = callRaw('/download', ['headers' => ['Range: bytes=abc']]);

    expect($res['status'])->toBe(200)
        ->and($res['body'])->toBe('0123456789');
});
