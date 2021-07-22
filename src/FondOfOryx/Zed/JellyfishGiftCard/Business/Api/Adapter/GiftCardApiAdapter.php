<?php

namespace FondOfOryx\Zed\JellyfishGiftCard\Business\Api\Adapter;

use Exception;
use FondOfOryx\Zed\JellyfishGiftCard\Dependency\Service\JellyfishGiftCardToUtilEncodingServiceInterface;
use Generated\Shared\Transfer\JellyfishGiftCardDataWrapperTransfer;
use GuzzleHttp\ClientInterface as HttpClientInterface;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Throwable;

class GiftCardApiAdapter implements GiftCardApiAdapterInterface
{
    protected const URI = '/';
    protected const ERROR_MESSAGE = 'Could not export gift card.';
    protected const METHOD_POST = 'POST';
    protected const STATUS_CODE_NO_CONTENT = 204;

    /**
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * @var \FondOfOryx\Zed\JellyfishGiftCard\Dependency\Service\JellyfishGiftCardToUtilEncodingServiceInterface
     */
    protected $utilEncodingService;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \GuzzleHttp\ClientInterface $httpClient
     * @param \FondOfOryx\Zed\JellyfishGiftCard\Dependency\Service\JellyfishGiftCardToUtilEncodingServiceInterface $utilEncodingService
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        HttpClientInterface $httpClient,
        JellyfishGiftCardToUtilEncodingServiceInterface $utilEncodingService,
        LoggerInterface $logger
    ) {
        $this->httpClient = $httpClient;
        $this->utilEncodingService = $utilEncodingService;
        $this->logger = $logger;
    }

    /**
     * @param \Generated\Shared\Transfer\JellyfishGiftCardDataWrapperTransfer $jellyfishGiftCardDataWrapperTransfer
     *
     * @throws \Exception
     *
     * @return void
     */
    public function post(JellyfishGiftCardDataWrapperTransfer $jellyfishGiftCardDataWrapperTransfer): void
    {
        $body = $this->utilEncodingService->encodeJson($jellyfishGiftCardDataWrapperTransfer->toArray(true, true));

        try {
            $response = $this->httpClient->request(static::METHOD_POST, static::URI, [
                RequestOptions::BODY => $body,
            ]);

            if ($response->getStatusCode() !== static::STATUS_CODE_NO_CONTENT) {
                throw new Exception($response->getBody());
            }
        } catch (Throwable $exception) {
            $this->logger->error(static::ERROR_MESSAGE, [
                'exception' => $exception,
                'request' => [
                    'body' => $body,
                ],
            ]);
        }
    }
}
