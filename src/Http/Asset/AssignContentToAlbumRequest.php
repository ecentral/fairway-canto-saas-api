<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi\Http\Asset;

use Fairway\CantoSaasApi\Http\Request;

final class AssignContentToAlbumRequest extends Request
{
    private string $albumPath;
    private array $content;

    public function __construct(string $albumPath)
    {
        $this->albumPath = $albumPath;
    }

    public function addContent(string $scheme, string $id, string $displayName): self
    {
        $this->content[$id] = [
            'scheme' => $scheme,
            'id' => $id,
            'displayName' => $displayName,
        ];
        return $this;
    }

    public function jsonSerialize(): array
    {
        return [
            'albumPath' => $this->albumPath,
            'contents' => $this->content,
        ];
    }

    public function hasBody(): bool
    {
        return true;
    }

    public function getApiPath(): string
    {
        return 'batch/album';
    }

    public function getMethod(): string
    {
        return self::POST;
    }
}
