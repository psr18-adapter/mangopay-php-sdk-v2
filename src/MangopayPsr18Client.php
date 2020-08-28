<?php

declare(strict_types=1);

namespace Psr18Adapter\Mangopay;

use MangoPay\Libraries\HttpBase;
use MangoPay\Libraries\HttpResponse;
use MangoPay\Libraries\RestTool;
use MangoPay\MangoPayApi;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class MangopayPsr18Client extends HttpBase
{
    /**
     * @var ClientInterface
     */
    private $httpClient;

    /**
     * @var RequestFactoryInterface
     */
    private $requestFactory;

    /**
     * @var UriFactoryInterface
     */
    private $uriFactory;

    /**
     * @var StreamFactoryInterface
     */
    private $streamFactory;

    public function __construct(
        MangoPayApi $root,
        ClientInterface $httpClient,
        RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory,
        StreamFactoryInterface $streamFactory
    ) {
        parent::__construct($root);
        $this->httpClient = $httpClient;
        $this->requestFactory = $requestFactory;
        $this->uriFactory = $uriFactory;
        $this->streamFactory = $streamFactory;
    }

    public function request(RestTool $restTool): HttpResponse
    {
        $body = $restTool->GetRequestData();

        $httpRequest = $this->requestFactory->createRequest(
            $restTool->GetRequestType(),
            $this->uriFactory->createUri($restTool->GetRequestUrl())
        );
        $httpRequest = $this->withNormalizedHeaders($httpRequest, $restTool->GetRequestHeaders())
            ->withBody($this->streamFactory->createStream(is_array($body) ? json_encode($body) : $body))
        ;

        $httpResponse = $this->httpClient->sendRequest($httpRequest);

        $response = new HttpResponse();
        $response->ResponseCode = $httpResponse->getStatusCode();
        $response->Headers = $this->denormalizeHeaders($httpResponse->getHeaders());
        $response->Body = $httpResponse->getBody()->getContents();

        return $response;
    }

    /**
     * @param array<int, string> $rawHeaders
     */
    private function withNormalizedHeaders(RequestInterface $request, array $rawHeaders): RequestInterface
    {
        foreach ($rawHeaders as $rawHeader) {
            $request = $request->withHeader($key = strstr($rawHeader, ':', true), substr($rawHeader, strlen($key) + 2));
        }

        return $request;
    }

    /**
     * @param array<string, string|array<string>> $psrHeaders
     *
     * @return array<int, string>
     */
    private function denormalizeHeaders(array $psrHeaders): array
    {
        $headers = [];

        foreach ($psrHeaders as $key => $value) {
            $headers[] = "$key: " . (is_array($value) ? reset($value) : $value);
        }

        return $headers;
    }
}