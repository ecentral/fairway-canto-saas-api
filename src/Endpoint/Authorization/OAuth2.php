<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi\Endpoint\Authorization;

use Fairway\CantoSaasApi\Endpoint\AbstractEndpoint;
use Fairway\CantoSaasApi\Http\Authorization\OAuth2Request;
use Fairway\CantoSaasApi\Http\Authorization\OAuth2Response;
use Fairway\CantoSaasApi\Http\RequestInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

final class OAuth2 extends AbstractEndpoint
{
    /**
     * @throws AuthorizationFailedException
     * @throws NotAuthorizedException
     */
    public function obtainAccessToken(OAuth2Request $request): OAuth2Response
    {
        $uri = $this->buildRequestUrl($request);
        $httpRequest = new Request($request->getMethod(), $uri);

        try {
            $response = $this->sendRequest($httpRequest);
        } catch (GuzzleException $e) {
            throw new AuthorizationFailedException(
                $e->getMessage(),
                1626447895,
                $e
            );
        }

        return new OAuth2Response($response);
    }

    protected function buildRequestUrl(RequestInterface $request): Uri
    {
        $url = sprintf(
            'https://oauth.%s/oauth/api/oauth2/%s',
            $this->getClient()->getOptions()->getCantoDomain(),
            urlencode(trim($request->getApiPath(), '/'))
        );

        $queryParams = $request->getQueryParams();
        if (count($queryParams) > 0) {
            $url .= '?' . http_build_query($queryParams);
        }

        return new Uri($url);
    }
}
