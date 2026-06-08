<?php

namespace App\Providers;

use App\Dav\Auth\BasicAuthBackend;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use LaravelSabre\LaravelSabre;
use Sabre\CalDAV\CalendarRoot;
use Sabre\CalDAV\Plugin as CalDAVPlugin;
use Sabre\CardDAV\AddressBookRoot;
use Sabre\CardDAV\Plugin as CardDAVPlugin;
use Sabre\DAV\Auth\Plugin as AuthPlugin;
use Sabre\DAV\Browser\Plugin as BrowserPlugin;
use Sabre\DAV\Sync\Plugin as SyncPlugin;
use Sabre\DAVACL\Plugin as AclPlugin;
use Sabre\CalDAV\Principal\Collection as CalDAVPrincipalCollection;
use Sabre\DAVACL\PrincipalBackend\PDO as PrincipalBackend;
use Sabre\CalDAV\Backend\PDO as CalDAVBackend;
use Sabre\CardDAV\Backend\PDO as CardDAVBackend;

class DavServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        LaravelSabre::nodes(fn (): array => $this->nodes());
        LaravelSabre::plugins(fn (): array => iterator_to_array($this->plugins(), false));
    }

    private function nodes(): array
    {
        $pdo = DB::connection()->getPdo();
        $principalBackend = new PrincipalBackend($pdo);

        return [
            new CalDAVPrincipalCollection($principalBackend),
            new AddressBookRoot($principalBackend, new CardDAVBackend($pdo)),
            new CalendarRoot($principalBackend, new CalDAVBackend($pdo)),
        ];
    }

    private function plugins(): \Generator
    {
        yield new AuthPlugin(new BasicAuthBackend);
        yield new CardDAVPlugin;
        yield new CalDAVPlugin;
        yield new AclPlugin;
        yield new SyncPlugin;

        if ($this->app->environment('local')) {
            yield new BrowserPlugin;
        }
    }
}
