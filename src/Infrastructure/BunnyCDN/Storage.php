<?php

namespace App\Infrastructure\BunnyCDN;

use App\Util\FilePathUtil;
use AsyncAws\Core\Stream\ResponseBodyStream;
use AsyncAws\Core\Stream\ResultStream;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

final class Storage
{
    public function __construct(
        private readonly HttpClientInterface $client,
        private readonly string $storageZoneName,
        private readonly string $apiAccessKey,
        private readonly string $storageZoneRegion,
    ) {
    }

    public function getBaseUrl(): string
    {
        return 'de' === $this->storageZoneRegion || !$this->storageZoneRegion
            ? 'https://storage.bunnycdn.com/'
            : 'https://'.$this->storageZoneRegion.'.storage.bunnycdn.com/';
    }

    public function getStorageObjects(string $path): Reply
    {
        return $this->makeResponse($this->doRequest(FilePathUtil::getParentDirectory($path), RequestMethod::GET, isDirectory: true));
    }

    public function getObject(string $path): FileObject
    {
        $fileName = FilePathUtil::getFileName($path);

        $response = $this->makeResponse($this->doRequest(FilePathUtil::getParentDirectory($path), RequestMethod::GET, isDirectory: true));

        if (200 !== $response->getStatusCode()) {
            throw new FileNotFoundException('File not found for path :'.$path);
        }
        /** @phpstan-ignore-next-line */
        $object = array_filter($response->getContent(), static fn (FileObject $item): bool => $item->getFilename() === $fileName);

        /** @var FileObject|null $res */
        $res = array_shift($object);
        if (null === $res) {
            throw new FileNotFoundException('File not found for path :'.$path);
        }

        return $res;
    }

    public function fileExists(string $path): bool
    {
        $fileName = FilePathUtil::getFileName($path);

        $response = $this->makeResponse($this->doRequest(FilePathUtil::getParentDirectory($path), RequestMethod::GET, isDirectory: true));

        return 200 === $response->getStatusCode()
            /* @phpstan-ignore-next-line */
            && !empty(array_filter($response->getContent(), static fn (FileObject $item): bool => $item->getFilename() === $fileName))
        ;
    }

    public function deleteObject(string $path): Reply
    {
        return $this->makeResponse($this->doRequest($path, RequestMethod::DELETE));
    }

    public function uploadFile(string $path, mixed $content): Reply
    {
        return $this->makeResponse($this->doRequest($path, RequestMethod::PUT, $content));
    }

    public function downloadFile(string $path): ResultStream
    {
        $response = $this->doRequest($path, RequestMethod::GET, downloadFile: true);

        return new ResponseBodyStream($this->client->stream($response));
    }

    private function doRequest(string $path, RequestMethod $method, mixed $content = null, bool $isDirectory = false, bool $downloadFile = false): ResponseInterface
    {
        $normalizedPath = $this->normalizePath($path, $isDirectory);

        return $this->client->request(
            $method->value,
            $this->getBaseUrl().$normalizedPath,
            $this->buildRequestData($method, $content),
        );
    }

    private function makeResponse(ResponseInterface $response): Reply
    {
        try {
            $statusCode = $response->getStatusCode();

            if (201 === $statusCode) {
                return new Reply($statusCode, $response->toArray());
            }

            $responseArray = $response->toArray();

            if (array_key_exists('Message', $responseArray)) {
                return new Reply($statusCode, $responseArray);
            }

            $data = array_map(static fn ($i) => FileObject::fromArray($i), $responseArray);

            return new Reply($statusCode, $data);
        } catch (\Exception $e) {
            if (404 === $e->getCode()) {
                return new Reply(404, ['Message' => $e->getMessage()]);
            }
        }

        return new Reply($response->getStatusCode(), []);
    }

    private function normalizePath(string $path, bool $isDirectory = false): string
    {
        $path = str_replace('\\', '/', $path);
        if ($isDirectory && !FilePathUtil::endsWith($path, '/')) {
            $path .= '/';
        }

        // Remove double slashes
        while (str_contains($path, '//')) {
            $path = str_replace('//', '/', $path);
        }

        // Remove the starting slash
        if (FilePathUtil::startsWith($path, '/')) {
            $path = substr($path, 1);
        }

        return sprintf('%s/%s', $this->storageZoneName, $path);
    }

    /** @return array<string, mixed> */
    private function buildRequestData(RequestMethod $method, mixed $content = null): array
    {
        $data = [
            'headers' => [
                'AccessKey' => $this->apiAccessKey,
            ],
        ];

        if (null !== $content) {
            $data['body'] = $content;
        }

        match ($method) {
            RequestMethod::GET => $data['headers']['Accept'] = '*/*',
            RequestMethod::PUT => $data['headers']['Accept'] = 'application/octet-stream',
            RequestMethod::DELETE => $data,
        };

        return $data;
    }
}
