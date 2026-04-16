<?php

namespace Tests\Concerns;

use Illuminate\Http\UploadedFile;

trait CreatesPngUploads
{
    protected function fakePngUpload(string $name, int $width, int $height): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'png-upload-');
        file_put_contents($path, $this->buildPngBinary($width, $height));

        return new UploadedFile($path, $name, 'image/png', null, true);
    }

    private function buildPngBinary(int $width, int $height): string
    {
        $signature = "\x89PNG\r\n\x1a\n";
        $ihdr = pack('NNCCCCC', $width, $height, 8, 2, 0, 0, 0);
        $row = "\x00".str_repeat("\x7f\x94\xb8", $width);
        $imageData = str_repeat($row, $height);
        $compressed = gzcompress($imageData, 9);

        return $signature
            .$this->pngChunk('IHDR', $ihdr)
            .$this->pngChunk('IDAT', $compressed)
            .$this->pngChunk('IEND', '');
    }

    private function pngChunk(string $type, string $data): string
    {
        $crc = (int) sprintf('%u', crc32($type.$data));

        return pack('N', strlen($data)).$type.$data.pack('N', $crc);
    }
}
