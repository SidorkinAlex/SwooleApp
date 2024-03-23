<?php

namespace Sidalex\SwooleApp\Classes\Builder;

use Swoole\Http\Response;

class ErrorResponseBuilder
{
    private Response $response;

    /**
     * @param Response $response
     */
    public function __construct(Response $response)
    {
        $this->response = $response;
    }

    public function errorResponse(string $errorMessage, int $errorCode = 400, $details = null): Response
    {
        $this->response->setHeader('Content-Type', 'application/json');
        $this->response->setStatusCode($errorCode);
        $this->response->end(
            json_encode(
                [
                    "status" => "error",
                    "message" => $errorMessage,
                    "details" => $details,
                ]
            )
        );
        return $this->response;
    }

}