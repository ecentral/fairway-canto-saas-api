<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi\Http;

use Fairway\CantoSaasApi\Client;
use GuzzleHttp\Psr7\Request as HttpRequest;
use GuzzleHttp\Psr7\Utils;

abstract class UploadRequest implements RequestInterface
{
    abstract public function getFormData(): array;
    abstract public function uploadedFilePath(): string;

    public function getQueryParams(): ?array
    {
        return null;
    }

    public function getPathVariables(): ?array
    {
        return null;
    }

    public function getMethod(): string
    {
        return self::POST;
    }

    public function toHttpRequest(Client $client, array $withHeaders = []): HttpRequest
    {
        $formData = $this->getFormData();
        $formData['contents'] = Utils::tryFopen($this->uploadedFilePath(), 'r');
        $formData['file'] = $formData['contents'];
        return new HttpRequest(
            $this->getMethod(),
            $this->getApiPath(),
            $withHeaders,
            json_encode([
                'multipart' => [$formData],
            ], JSON_THROW_ON_ERROR),
        );
    }
}
