<?php

namespace Tests\Unit;

use App\Dav\Support\ResolvesDavResources;
use Tests\TestCase;

class DavContentEtagTest extends TestCase
{
    public function test_content_etag_fits_mysql_varbinary_32(): void
    {
        $helper = new class
        {
            use ResolvesDavResources;

            public function etag(string $data): string
            {
                return $this->contentEtag($data);
            }
        };

        $etag = $helper->etag('BEGIN:VCARD'.PHP_EOL.'END:VCARD');

        $this->assertSame(32, strlen($etag));
        $this->assertMatchesRegularExpression('/^[a-f0-9]{32}$/', $etag);
    }
}
