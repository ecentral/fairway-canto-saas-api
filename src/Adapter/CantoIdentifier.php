<?php

declare(strict_types = 1);

/*
 * This file is part of the "fairway_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi\Adapter\CantoAdapter;

final class CantoIdentifier
{
    private const SPLIT_IDENTIFIER = '<>';
    private string $identifier;

    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    public static function buildIdentifier(string $scheme, string $identifier): self
    {
        return new self(sprintf('%s%s%s', $scheme, self::SPLIT_IDENTIFIER, $identifier));
    }

    public function getScheme(): string
    {
        return $this->split()['scheme'];
    }

    public function getIdentifier(): string
    {
        return $this->split()['identifier'];
    }

    #[ArrayShape(['scheme' => 'string', 'identifier' => 'string'])]
    private function split(): array
    {
        [$scheme, $identifier] = explode(self::SPLIT_IDENTIFIER, $this->identifier);
        return [
            'scheme' => $scheme,
            'identifier' => $identifier,
        ];
    }

    public function __toString(): string
    {
        return $this->identifier;
    }
}
