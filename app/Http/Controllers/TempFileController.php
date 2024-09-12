<?php

namespace App\Http\Controllers;

use CURLFile;

class TempFileController extends Controller
{

    private $tempDir;
    private $fileExtension;

    public function __construct($fileExtension = '.txt', $tempDir = null)
    {
        $this->fileExtension = $fileExtension;
        $this->tempDir = $tempDir ?? sys_get_temp_dir();
    }

    private function buildFilePath($filename)
    {
        return $this->tempDir . '/' . $filename . $this->fileExtension;
    }

    public function createTempFile($filename, $content, $overwrite = false)
    {
        $tempFile = $this->buildFilePath($filename);

        // Check if file already exists
        if (file_exists($tempFile) && !$overwrite) {
            return json_encode([
                'success' => false,
                'message' => 'File already exists'
            ]);
        }

        file_put_contents($tempFile, $content);

        return json_encode([
            'success' => true,
            'message' => 'File created successfully',
            'path' => $tempFile
        ]);
    }

    public function readTempFile($filename)
    {
        $tempFile = $this->buildFilePath($filename);

        if (file_exists($tempFile)) {
            $content = file_get_contents($tempFile);

            return json_encode([
                'success' => true,
                'message' => 'File read successfully',
                'content' => $content
            ]);
        }

        return json_encode([
            'success' => false,
            'message' => 'File not found'
        ]);
    }

    public function deleteTempFile($filename)
    {
        $tempFile = $this->buildFilePath($filename);

        if (file_exists($tempFile) && unlink($tempFile)) {
            return json_encode([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
        }

        return json_encode([
            'success' => false,
            'message' => 'File deletion failed'
        ]);
    }
    public function sendViaTelegram($bot, $chat_id, $filename)
    {
        $tempFile = $this->buildFilePath($filename);
        if (!file_exists($tempFile)) {
            return json_encode([
                'success' => false,
                'message' => 'File not found'
            ]);
        }



        return json_encode([
            'success' => true,
            'message' => 'File sent',
            'result' => $bot->sendDocument([
                'chat_id' => $chat_id,
                'document' => new CURLFile($tempFile)
            ])
        ]);
    }
}
