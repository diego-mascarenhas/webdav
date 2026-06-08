<?php

namespace App\Dav\Support;

use Illuminate\Support\Facades\DB;

final class DavBlob
{
    /**
     * PostgreSQL BYTEA columns require a stream binding; other drivers accept strings.
     */
    public static function forWrite(string $data): mixed
    {
        if (DB::connection()->getDriverName() !== 'pgsql') {
            return $data;
        }

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $data);
        rewind($stream);

        return $stream;
    }

    /**
     * PostgreSQL may return BYTEA columns as stream resources.
     */
    public static function toString(mixed $value): string
    {
        if (is_resource($value)) {
            return stream_get_contents($value) ?: '';
        }

        return is_string($value) ? $value : '';
    }
}
