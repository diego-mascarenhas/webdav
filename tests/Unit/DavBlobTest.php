<?php

namespace Tests\Unit;

use App\Dav\Support\DavBlob;
use Tests\TestCase;

class DavBlobTest extends TestCase
{
    public function test_to_string_reads_stream_resources(): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'BEGIN:VCALENDAR'.PHP_EOL.'END:VCALENDAR');
        rewind($stream);

        $this->assertSame(
            'BEGIN:VCALENDAR'.PHP_EOL.'END:VCALENDAR',
            DavBlob::toString($stream)
        );
    }

    public function test_for_write_returns_string_on_sqlite(): void
    {
        $data = 'BEGIN:VCARD'.PHP_EOL.'END:VCARD';

        $this->assertSame($data, DavBlob::forWrite($data));
    }
}
