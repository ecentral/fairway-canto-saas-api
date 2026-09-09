<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_canto_saas_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi\Adapter\CantoAdapter;

use DateTime;
use Fairway\FairwayFilesystemApi\File;

final class CantoFile extends File
{
    public function getFileName(): string
    {
        return $this->getMetadata()['default']['Name'];
    }

    public function getCantoIdentifier(): CantoIdentifier
    {
        return new CantoIdentifier($this->identifier);
    }

    public function getScheme(): string
    {
        return $this->getCantoIdentifier()->getScheme();
    }

    public function getIdentifier(): string
    {
        return $this->getCantoIdentifier()->getIdentifier();
    }

    public function getPublicUrl(): string
    {
        return '';
    }

    /**
     * does not have last access time, thus setting it to last modified time
     */
    public function getATime(): int
    {
        return $this->getMTime();
    }

    public function getMTime(): int
    {
        return DateTime::createFromFormat(
            'YmdHisv',
            $this->getMetadata()['default']['Date modified']
        )->getTimestamp();
    }

    public function getCTime(): int
    {
        return DateTime::createFromFormat(
            'YmdHisv',
            $this->getMetadata()['default']['Date uploaded']
        )->getTimestamp();
    }

    public function getExtension(): string
    {
        return $this->getMetadata()['metadata']['File Type Extension'];
    }
}
