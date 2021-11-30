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

final class RemoveKeywordToContentRequest extends Request
{
    private string $scheme;
    private string $contentId;
    private string $keyword;

    public function __construct(string $scheme, string $contentId, string $keyword)
    {
        $this->scheme = $scheme;
        $this->contentId = $contentId;
        $this->keyword = $keyword;
    }

    public function getPathVariables(): ?array
    {
        return [
            $this->scheme,
            $this->contentId,
            'keyword',
            $this->keyword,
        ];
    }

    public function getApiPath(): string
    {
        return '';
    }

    public function getMethod(): string
    {
        return self::DELETE;
    }
}
