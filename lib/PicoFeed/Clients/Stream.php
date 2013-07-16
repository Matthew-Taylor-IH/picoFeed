<?php

namespace PicoFeed\Clients;

use \PicoFeed\Logging;

class Stream extends \PicoFeed\Client
{
    public function doRequest()
    {
        // Prepare HTTP headers for the request
        $headers = array(
            'Connection: close',
            'User-Agent: '.$this->user_agent,
        );

        if ($this->etag) $headers[] = 'If-None-Match: '.$this->etag;
        if ($this->last_modified) $headers[] = 'If-Modified-Since: '.$this->last_modified;

        // Create context
        $context_options = array(
            'http' => array(
                'method' => 'GET',
                'protocol_version' => 1.1,
                'timeout' => $this->timeout,
                'max_redirects' => $this->max_redirects,
                'header' => implode("\r\n", $headers)
            )
        );

        $context = stream_context_create($context_options);

        // Make HTTP request, TODO: more robust data fetching
        $stream = @fopen($this->url, 'r', false, $context);
        if (! is_resource($stream)) return false;

        // Get the entire body
        // TODO: Fetch until max_body_size...
        $body = stream_get_contents($stream);

        // Get HTTP headers response
        $metadata = stream_get_meta_data($stream);

        list($status, $headers) = $this->parseHeaders($metadata['wrapper_data']);

        fclose($stream);

        return array(
            'status' => $status,
            'body' => $body,
            'headers' => $headers
        );
    }
}