<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi\Http\Asset;

use Fairway\CantoSaasApi\Http\Response;
use Psr\Http\Message\ResponseInterface;

class GetContentDetailsResponse extends Response
{
    public const APPROVAL_STATUS_PENDING = 'pending';
    public const APPROVAL_STATUS_RESTRICTED = 'restricted';
    public const APPROVAL_STATUS_APPROVED = 'approved';

    protected array $responseData;

    public function __construct(ResponseInterface $response)
    {
        $this->responseData = $this->parseResponse($response);
    }

    public function getResponseData(): array
    {
        return $this->responseData;
    }
}
