<?php

namespace Integrad\DbDeeper\Services;

use RuntimeException;

class BackupEncryptionService
{
    private const MAGIC = "DBDEEPER\x01";

    /**
     * @param  resource  $output
     */
    public function writeEncryptedDump($input, $output, string $base64Key, int $chunkSize): int
    {
        $key = base64_decode($base64Key, true);

        if ($key === false || strlen($key) !== 32) {
            throw new RuntimeException('A chave de criptografia deve ser Base64 e conter exatamente 32 bytes.');
        }

        if ($chunkSize < 65536 || $chunkSize > 8388608) {
            throw new RuntimeException('O tamanho do bloco deve ficar entre 64 KiB e 8 MiB.');
        }

        $this->writeAll($output, self::MAGIC);
        $context = deflate_init(ZLIB_ENCODING_GZIP, ['level' => 6]);

        if ($context === false) {
            throw new RuntimeException('Não foi possível iniciar a compressão do backup.');
        }

        $index = 0;

        while (! feof($input)) {
            $plainChunk = fread($input, $chunkSize);

            if ($plainChunk === false) {
                throw new RuntimeException('Não foi possível ler o dump do banco.');
            }

            if ($plainChunk === '') {
                continue;
            }

            $compressedChunk = deflate_add($context, $plainChunk, ZLIB_SYNC_FLUSH);

            if ($compressedChunk === false) {
                throw new RuntimeException('Não foi possível comprimir o backup.');
            }

            $this->writeEncryptedChunk($output, $compressedChunk, $key, $index++);
        }

        $finalChunk = deflate_add($context, '', ZLIB_FINISH);

        if ($finalChunk === false) {
            throw new RuntimeException('Não foi possível finalizar a compressão do backup.');
        }

        if ($finalChunk !== '') {
            $this->writeEncryptedChunk($output, $finalChunk, $key, $index++);
        }

        $this->writeAll($output, pack('N', 0));

        return $index;
    }

    /**
     * @param  resource  $output
     */
    private function writeEncryptedChunk($output, string $compressed, string $key, int $index): void
    {
        $nonce = random_bytes(12);
        $tag = '';
        $ciphertext = openssl_encrypt(
            $compressed,
            'aes-256-gcm',
            $key,
            OPENSSL_RAW_DATA,
            $nonce,
            $tag,
            self::MAGIC.pack('N', $index),
        );

        if ($ciphertext === false || strlen($tag) !== 16) {
            throw new RuntimeException('Não foi possível cifrar um bloco do backup.');
        }

        $this->writeAll($output, pack('N', strlen($ciphertext)).$nonce.$tag.$ciphertext);
    }

    /**
     * @param  resource  $stream
     */
    private function writeAll($stream, string $contents): void
    {
        $offset = 0;
        $length = strlen($contents);

        while ($offset < $length) {
            $written = fwrite($stream, substr($contents, $offset));

            if ($written === false || $written === 0) {
                throw new RuntimeException('Não foi possível gravar o arquivo temporário do backup.');
            }

            $offset += $written;
        }
    }
}
