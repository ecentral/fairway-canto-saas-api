<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi\Http\Upload;

use Fairway\CantoSaasApi\Http\InvalidResponseException;
use Fairway\CantoSaasApi\Http\Response;
use Psr\Http\Message\ResponseInterface;

final class GetUploadSettingResponse extends Response
{
    private string $policy;
    private string $xAmzMetaScheme;
    private string $signature;
    private string $awsAccessKeyId;
    private string $xAmzMetaAlbumId;
    private string $acl;
    private string $xAmzMetaTag;
    private string $xAmzMetaId;
    private string $xAmzMetaFileName;
    private string $url;
    private string $key;

    private bool $isDeEnvironment;
    private ?string $xAmzDate = null;
    private ?string $xAmzAlgorithm = null;
    private ?string $xAmzCredential = null;

    /**
     * @param ResponseInterface $response
     * @param bool $isDeEnvironment Uploading a File to canto.de Environment
     * @throws InvalidResponseException
     */
    public function __construct(ResponseInterface $response, bool $isDeEnvironment = false)
    {
        $data = $this->parseResponse($response);
        $this->policy = $data['Policy'] ?? '';
        $this->xAmzMetaScheme = $data['x-amz-meta-scheme'] ?? '';
        $signature = $data['Signature'] ?? '';
        if ($isDeEnvironment) {
            $signature = $data['x-amz-Signature'] ?? '';
            $this->xAmzDate = $data['x-amz-date'] ?? '';
            $this->xAmzAlgorithm = $data['x-amz-algorithm'] ?? '';
            $this->xAmzCredential = $data['x-amz-credential'] ?? '';
        }
        $this->signature = $signature;
        $this->awsAccessKeyId = $data['AWSAccessKeyId'] ?? '';
        $this->xAmzMetaAlbumId = $data['x-amz-meta-album_id'] ?? '';
        $this->acl = $data['acl'] ?? '';
        $this->xAmzMetaTag = $data['x-amz-meta-tag'] ?? '';
        $this->xAmzMetaFileName = $data['x-amz-meta-id'] ?? '';
        $this->xAmzMetaId = $data['x-amz-meta-file_name'] ?? '';
        $this->url = $data['url'] ?? '';
        $this->key = $data['key'] ?? '';
        $this->isDeEnvironment = $isDeEnvironment;
    }

    public function isDeEnvironment(): bool
    {
        return $this->isDeEnvironment;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getPolicy(): string
    {
        return $this->policy;
    }

    public function getXAmzMetaScheme(): string
    {
        return $this->xAmzMetaScheme;
    }

    public function getSignature(): string
    {
        return $this->signature;
    }

    public function getAwsAccessKeyId(): string
    {
        return $this->awsAccessKeyId;
    }

    public function getXAmzMetaAlbumId(): string
    {
        return $this->xAmzMetaAlbumId;
    }

    public function getAcl(): string
    {
        return $this->acl;
    }

    public function getXAmzMetaTag(): string
    {
        return $this->xAmzMetaTag;
    }

    public function getXAmzMetaId(): string
    {
        return $this->xAmzMetaId;
    }

    public function getXAmzMetaFileName(): string
    {
        return $this->xAmzMetaFileName;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getXAmzDate(): ?string
    {
        return $this->xAmzDate;
    }

    public function getXAmzAlgorithm(): ?string
    {
        return $this->xAmzAlgorithm;
    }

    public function getXAmzCredential(): ?string
    {
        return $this->xAmzCredential;
    }
}
