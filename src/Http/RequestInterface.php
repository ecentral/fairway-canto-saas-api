<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Ecentral\CantoSaasApiClient\Http;

interface RequestInterface
{
    /**
     * @return array<string|int, scalar>|null Null if no query params exists
     */
    public function getQueryParams(): ?array;

    /**
     * @return array<string|int, scalar>|null Null if no path variable exists.
     */
    public function getPathVariables(): ?array;
}
