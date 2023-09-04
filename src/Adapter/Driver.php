<?php

declare(strict_types=1);

/*
 * This file is part of the "fairway_api" library by eCentral GmbH.
 *
 * For the full copyright and license information, please read the
 * LICENSE file that was distributed with this source code.
 */

namespace Fairway\CantoSaasApi\Adapter\CantoAdapter;

use Exception;
use Fairway\CantoSaasApi\Client;
use Fairway\CantoSaasApi\DTO\Status;
use Fairway\CantoSaasApi\Http\Asset\AssignContentToAlbumRequest;
use Fairway\CantoSaasApi\Http\Asset\BatchDeleteContentRequest;
use Fairway\CantoSaasApi\Http\Asset\GetContentDetailsRequest;
use Fairway\CantoSaasApi\Http\Asset\RemoveContentFromAlbumRequest;
use Fairway\CantoSaasApi\Http\Asset\RenameContentRequest;
use Fairway\CantoSaasApi\Http\LibraryTree\CreateAlbumFolderRequest;
use Fairway\CantoSaasApi\Http\LibraryTree\DeleteFolderOrAlbumRequest;
use Fairway\CantoSaasApi\Http\LibraryTree\GetDetailsRequest;
use Fairway\CantoSaasApi\Http\LibraryTree\GetTreeRequest;
use Fairway\CantoSaasApi\Http\LibraryTree\ListAlbumContentRequest;
use Fairway\CantoSaasApi\Http\Upload\GetUploadSettingRequest;
use Fairway\CantoSaasApi\Http\Upload\QueryUploadStatusRequest;
use Fairway\CantoSaasApi\Http\Upload\UploadFileRequest;
use Fairway\FairwayFilesystemApi\Directory;
use Fairway\FairwayFilesystemApi\DirectoryIterator;
use Fairway\FairwayFilesystemApi\DriverClient;
use Fairway\FairwayFilesystemApi\Exceptions\NotImplementedException;
use Fairway\FairwayFilesystemApi\FileType;
use Fairway\FairwayFilesystemApi\Permission;

final class Driver implements DriverClient
{
    private Client $client;

    public function __construct(Client $client, string $userId = '')
    {
        $this->client = $client;
        if ($this->client->getAccessToken() === null) {
            $this->client->authorizeWithClientCredentials($userId);
        }
    }

    public function hasAssetPicker(): bool
    {
        return true;
    }

    public function getAssetPicker(): string
    {
        return '';
    }

    public function getFile(string $identifier): CantoFile
    {
        return new CantoFile($this, $identifier);
    }

    public function getMetadata(string $identifier): array
    {
        if ($this->getType($identifier) === FileType::DIRECTORY) {
            // todo: Exception type needs to be defined
            throw new \Exception('Not supported');
        }
        $parsedIdentifier = $this->parseIdentifier($identifier);
        return $this->client->asset()->getContentDetails(
            (new GetContentDetailsRequest($parsedIdentifier->getIdentifier(), $parsedIdentifier->getScheme()))
        )->getResponseData();
    }

    public function exists(string $identifier, string $type): bool
    {
        try {
            $parsedIdentifier = $this->parseIdentifier($identifier);
            $this->client->asset()->getContentDetails(
                (new GetContentDetailsRequest($parsedIdentifier->getIdentifier(), $parsedIdentifier->getScheme()))
            );
        } catch (Exception $exception) {
            if (str_contains($exception->getMessage(), '404')) {
                return false;
            }
            throw $exception;
        }
        return true;
    }

    public function getType(string $identifier): string
    {
        $scheme = $this->parseIdentifier($identifier)->getScheme();
        if ($scheme === GetDetailsRequest::TYPE_ALBUM || $scheme === GetDetailsRequest::TYPE_FOLDER) {
            return FileType::DIRECTORY;
        }
        return FileType::FILE;
    }

    public function read(string $identifier): string
    {
        $parsedIdentifier = $this->parseIdentifier($identifier);
        $download2 = sprintf(
            'https://%s.%s/api_binary/v1/%s/%s',
            $this->client->getOptions()->getCantoName(),
            $this->client->getOptions()->getCantoDomain(),
            $parsedIdentifier->getScheme(),
            $parsedIdentifier->getIdentifier()
        );
        return $this->client->asset()->getAuthorizedUrlContent($download2)->getBody()->getContents();
    }

    public function listDirectory(string $identifier = null): DirectoryIterator
    {
        if ($identifier === null) {
            throw new NotImplementedException('The root folder needs to be implemented');
        }
        $id = $this->parseIdentifier($identifier);
        if ($id->getScheme() === GetDetailsRequest::TYPE_FOLDER) {
            $request = new GetTreeRequest($id->getIdentifier());
            $response = $this->client->libraryTree()->getTree($request);
            return new DirectoryIterator($response->getResults());
        }
        $result = [];
        $request = new ListAlbumContentRequest($id->getIdentifier());
        $request->setLimit(100);
        $count = 0;
        do {
            $request->setStart($count);
            $response = $this->client->libraryTree()->listAlbumContent($request);
            $found = $response->getFound();
            $count += count($response->getResults());
            $result = [...$result, ...$response->getResults()];
        } while ($found > $count);
        return new DirectoryIterator($result);
    }

    public function lastModified(string $identifier): int
    {
        return (int)$this->getMetadata($identifier)['default']['Date modified'];
    }

    public function size(string $identifier): int
    {
        return $this->getMetadata($identifier)['default']['Size'];
    }

    public function count(string $identifier): int
    {
        $id = $this->parseIdentifier($identifier);
        if ($id->getScheme() === GetDetailsRequest::TYPE_FOLDER) {
            $request = new GetTreeRequest($id->getIdentifier());
            $response = $this->client->libraryTree()->getTree($request);
            return count($response->getResults());
        }
        $request = new ListAlbumContentRequest($id->getIdentifier());
        $response = $this->client->libraryTree()->listAlbumContent($request);
        return $response->getFound();
    }

    public function mimeType(string $identifier): string
    {
        return $this->getMetadata($identifier)['default']['Content Type'];
    }

    public function visibility(string $identifier): string
    {
        // todo: this needs to be refined, how are we going to implement visibility
        return '';
    }

    public function getPermission(string $identifier): Permission
    {
        return new CantoPermission($identifier, true, true);
    }

    /**
     * After Uploading the file, the returned string is *not* the new identifier, it's the file name.
     * @see Driver::getUploadStatus() the status of the current upload can be retrieved here.
     */
    public function write(string $identifier, string $parentIdentifier, string $filePath, array $config = []): string
    {
        $request = new UploadFileRequest(
            $filePath,
            $this->client->upload()->getUploadSetting(new GetUploadSettingRequest(false))
        );
        // todo, check folder write permission
        $request->setAlbumId($this->parseIdentifier($parentIdentifier)->getIdentifier());
        $request->setFileName((string)$identifier);
        $this->client->upload()->uploadFile($request);
        if (($config['remove_original'] ?? false) === true) {
            unlink($filePath);
        }
        return $request->getFileName();
    }

    public function getUploadStatus(string $fileName): ?Status
    {
        $status = $this->client->upload()->queryUploadStatus(new QueryUploadStatusRequest());
        foreach ($status->getStatusItems() as $item) {
            if ($item->name === $fileName) {
                return $item;
            }
        }
        return null;
    }

    public function getIdentifierFromStatusObject(Status $status): ?CantoIdentifier
    {
        if ($status->status === Status::STATUS_DONE) {
            return CantoIdentifier::buildIdentifier($status->scheme, $status->id);
        }
        return null;
    }

    public function setVisibility(string $identifier): void
    {
        // TODO: Implement setVisibility() method.
    }

    public function delete(string $identifier): void
    {
        $cantoId = $this->parseIdentifier($identifier);
        if ($this->getType($identifier) === FileType::DIRECTORY) {
            $request = new DeleteFolderOrAlbumRequest();
            $request->addFolder($cantoId->getIdentifier(), $cantoId->getScheme());
            $this->client->libraryTree()->deleteFolderOrAlbum($request)->isSuccessful();
            return;
        }
        $request = new BatchDeleteContentRequest();
        $request->addContent($cantoId->getScheme(), $cantoId->getIdentifier());
        $this->client->asset()->batchDeleteContent($request);
    }

    public function create(string $identifier, string $parentIdentifier, array $config = []): string
    {
        $cantoId = $this->parseIdentifier($identifier);
        if ($cantoId->getScheme() === GetDetailsRequest::TYPE_FOLDER) {
            return $this->createFolder($cantoId->getIdentifier(), $parentIdentifier, $config);
        }
        if ($cantoId->getScheme() === GetDetailsRequest::TYPE_ALBUM) {
            return $this->createAlbum($cantoId->getIdentifier(), $parentIdentifier, $config);
        }
        return $this->createFile($identifier, $parentIdentifier, $config);
    }

    private function createAlbum(string $identifier, string $parentIdentifier, array $config = []): string
    {
        $request = new CreateAlbumFolderRequest($identifier);
        $request->setParentFolder($this->parseIdentifier($parentIdentifier)->getIdentifier());
        return $this->client->libraryTree()->createAlbum($request)->getId();
    }

    private function createFolder(string $identifier, string $parentIdentifier, array $config = []): string
    {
        $request = new CreateAlbumFolderRequest($identifier);
        $request->setParentFolder($this->parseIdentifier($parentIdentifier)->getIdentifier());
        return $this->client->libraryTree()->createFolder($request)->getId();
    }

    public function createFile(string $identifier, string $parentIdentifier, array $config = []): string
    {
        $path = '/tmp/' . $identifier;
        touch($path);
        $config = array_replace($config, ['remove_original' => true]);
        return $this->write($identifier, $parentIdentifier, $path, $config);
    }

    public function move(string $identifier, string $oldDestination, string $destination, array $config = []): void
    {
        if ($this->getType($identifier) === FileType::FILE) {
            $this->copy($identifier, $destination, $config);
            $this->client->asset()->removeContentsFromAlbum(
                new RemoveContentFromAlbumRequest($this->parseIdentifier($oldDestination)->getIdentifier())
            );
        }
        // todo: move directory
    }

    public function copy(string $identifier, string $destination, array $config = []): void
    {
        if ($this->getType($identifier) === FileType::FILE) {
            $file = $this->getFile($identifier);
            $request = new AssignContentToAlbumRequest($this->parseIdentifier($destination)->getIdentifier());
            $request->addContent($file->getScheme(), $file->getIdentifier(), $file->getFileName());
            $this->client->asset()->assignContentToAlbum($request);
        }
        // todo: copy directory
    }

    public function rename(string $identifier, string $newName, array $config = []): void
    {
        $cantoId = $this->parseIdentifier($identifier);
        $request = new RenameContentRequest($cantoId->getScheme(), $cantoId->getIdentifier(), $newName);
        $this->client->asset()->renameContent($request);
    }

    public function replace(string $identifier, string $filePath, array $config = []): string
    {
        $cantoId = $this->parseIdentifier($identifier);
        $request = new UploadFileRequest(
            $filePath,
            $this->client->upload()->getUploadSetting(new GetUploadSettingRequest(false))
        );
        $request->setScheme($cantoId->getScheme());
        $request->setMetaId($cantoId->getIdentifier());
        $this->client->upload()->uploadFile($request);
        if (($config['remove_original'] ?? false) === true) {
            unlink($filePath);
        }
        return $identifier;
    }

    private function parseIdentifier(string $identifier): CantoIdentifier
    {
        return new CantoIdentifier($identifier);
    }

    public function parentOfIdentifier(string $identifier): Directory
    {
        throw new \Exception('Not supported yet');
//        return new Directory();
    }

    public function getClient(): Client
    {
        return $this->client;
    }

    public function getDriver(): DriverClient
    {
        throw new \Exception('Not supported yet');
    }

    public function getPublicUrl(string $identifier): string
    {
        throw new \Exception('Not supported yet');
    }
}
