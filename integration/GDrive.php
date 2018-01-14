<?php

namespace app\integration;

class GDrive
{
    protected $client;
    protected $service;

    protected $applicationName = "WalletDriveApi";
    protected $clientSecretPath = __DIR__ . '/../../keys/client_secret.json';
    protected $credentialsPath = __DIR__ . '/../../creds/WalletDriveApi.json';

    public function __construct()
    {
        $scopes = [
            \Google_Service_Drive::DRIVE_METADATA,
            \Google_Service_Drive::DRIVE_FILE,
            \Google_Service_Drive::DRIVE,
            \Google_Service_Drive::DRIVE_APPDATA,
        ];

        $client = new \Google_Client();
        $client->setApplicationName($this->applicationName);
        $client->setScopes($scopes);
        $client->setAuthConfig($this->clientSecretPath);
        $client->setAccessType('offline');

        // Load previously authorized credentials from a file.
        if (file_exists($this->credentialsPath)) {
            $accessToken = json_decode(file_get_contents($this->credentialsPath), true);
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

            // Store the credentials to disk.
            if(!file_exists(dirname($this->credentialsPath))) {
                mkdir(dirname($this->credentialsPath), 0700, true);
            }
            file_put_contents($this->credentialsPath, json_encode($accessToken));
            printf("Credentials saved to %s\n", $this->credentialsPath);
        }
        $client->setAccessToken($accessToken);

        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            file_put_contents($this->credentialsPath, json_encode($client->getAccessToken()));
        }

        $this->client = $client;

        $this->service = new \Google_Service_Drive($client);
    }

    public function createFolder() {
        $fileMetadata = new \Google_Service_Drive_DriveFile(array(
            'name' => 'Receipts',
            'mimeType' => 'application/vnd.google-apps.folder'));
        $file = $this->service->files->create($fileMetadata, array(
            'fields' => 'id'));
        printf("Folder ID: %s\n", $file->id);
    }

    public function lsFiles() {
        $folderId = '1MOeqHXLoXBdZT3qY4PQ8mJcmvX4uDrv-';
        $optParams = array(
            'pageSize' => 100,
            'fields' => 'nextPageToken, files(id, name)',
            'q' => "'{$folderId}' in parents",
        );
        $results = $this->service->files->listFiles($optParams);

        if (count($results->getFiles()) == 0) {
            return [];
        } else {
          foreach ($results->getFiles() as $file) {
              yield $file->getId();
          }
        }
    }

    public function getFile($fileId) {
        return $this->service->files->get($fileId, ['alt' => 'media'])->getBody()->getContents();
    }

    public function rmFile($fileId) {
        return $this->service->files->delete($fileId);
    }

    public function backupFile($fileId) {
        $folderId = '1MOeqHXLoXBdZT3qY4PQ8mJcmvX4uDrv-';
        $backupFolderId = '1lqixPr4KLCNzbJ77T8vOK-3qZVLtAOiV';

        $emptyFileMetadata = new \Google_Service_Drive_DriveFile();
        $emptyFileMetadata->setName(time() . "-" . $fileId);

        // Retrieve the existing parents to remove
        $file = $this->service->files->get($fileId, array('fields' => 'name, parents'));

        $previousParents = join(',', $file->parents);

        // Move the file to the new folder
        $file = $this->service->files->update(
            $fileId,
            $emptyFileMetadata,
            [
                'addParents' => $backupFolderId,
                'removeParents' => $previousParents,
                'fields' => 'id, parents'
            ]
        );
//
//
//        try {
//            $file = new \Google_Service_Drive_DriveFile();
//            $file->setName(time() . "-" . $fileId);
//
//            $updatedFile = $this->service->files->patch($fileId, $file, array(
//                'fields' => 'title'
//            ));
//
//            return $updatedFile;
//        } catch (\Exception $e) {
//            print "An error occurred: " . $e->getMessage();
//        }
    }
}