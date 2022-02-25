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

final class BatchDeleteContentRequest extends Request
{
    private array $content;

    public function addContent(string $scheme, string $id): self
    {
        $this->content[] = [
            'scheme' => $scheme,
            'id' => $id,
        ];
        return $this;
    }

    public function jsonSerialize(): array
    {
        return $this->content;
    }

    public function hasBody(): bool
    {
        return true;
    }

    public function getApiPath(): string
    {
        return 'batch/content';
    }

    public function getMethod(): string
    {
        return self::DELETE;
    }
}
