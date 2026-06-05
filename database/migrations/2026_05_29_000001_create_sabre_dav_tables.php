<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('addressbooks')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();

        match ($driver) {
            'sqlite' => $this->upSqlite(),
            'pgsql' => $this->upPgsql(),
            default => $this->upMysql(),
        };
    }

    public function down(): void
    {
        $tables = [
            'schedulingobjects',
            'calendarsubscriptions',
            'calendarchanges',
            'calendarinstances',
            'calendarobjects',
            'calendars',
            'addressbookchanges',
            'cards',
            'addressbooks',
            'groupmembers',
            'principals',
            'locks',
            'propertystorage',
        ];

        foreach ($tables as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function upSqlite(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE principals (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    uri TEXT NOT NULL,
    email TEXT,
    displayname TEXT,
    UNIQUE(uri)
);

CREATE TABLE groupmembers (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    principal_id INTEGER NOT NULL,
    member_id INTEGER NOT NULL,
    UNIQUE(principal_id, member_id)
);

CREATE TABLE addressbooks (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    principaluri TEXT NOT NULL,
    displayname TEXT,
    uri TEXT NOT NULL,
    description TEXT,
    synctoken INTEGER DEFAULT 1 NOT NULL,
    UNIQUE(principaluri, uri)
);

CREATE TABLE cards (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    addressbookid INTEGER NOT NULL,
    carddata BLOB,
    uri TEXT NOT NULL,
    lastmodified INTEGER,
    etag TEXT,
    size INTEGER,
    UNIQUE(addressbookid, uri)
);

CREATE TABLE addressbookchanges (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    uri TEXT,
    synctoken INTEGER NOT NULL,
    addressbookid INTEGER NOT NULL,
    operation INTEGER NOT NULL
);

CREATE INDEX addressbookid_synctoken ON addressbookchanges (addressbookid, synctoken);

CREATE TABLE calendars (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    synctoken INTEGER DEFAULT 1 NOT NULL,
    components TEXT NOT NULL
);

CREATE TABLE calendarinstances (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    calendarid INTEGER NOT NULL,
    principaluri TEXT,
    access INTEGER NOT NULL DEFAULT 1,
    displayname TEXT,
    uri TEXT NOT NULL,
    description TEXT,
    calendarorder INTEGER,
    calendarcolor TEXT,
    timezone TEXT,
    transparent INTEGER,
    share_href TEXT,
    share_displayname TEXT,
    share_invitestatus INTEGER DEFAULT 2,
    UNIQUE (principaluri, uri),
    UNIQUE (calendarid, principaluri),
    UNIQUE (calendarid, share_href)
);

CREATE TABLE calendarobjects (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    calendardata BLOB NOT NULL,
    uri TEXT NOT NULL,
    calendarid INTEGER NOT NULL,
    lastmodified INTEGER NOT NULL,
    etag TEXT NOT NULL,
    size INTEGER NOT NULL,
    componenttype TEXT,
    firstoccurence INTEGER,
    lastoccurence INTEGER,
    uid TEXT,
    UNIQUE(calendarid, uri)
);

CREATE INDEX calendarid_time ON calendarobjects (calendarid, firstoccurence);

CREATE TABLE calendarchanges (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    uri TEXT,
    synctoken INTEGER NOT NULL,
    calendarid INTEGER NOT NULL,
    operation INTEGER NOT NULL
);

CREATE INDEX calendarid_synctoken ON calendarchanges (calendarid, synctoken);

CREATE TABLE calendarsubscriptions (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    uri TEXT NOT NULL,
    principaluri TEXT NOT NULL,
    source TEXT NOT NULL,
    displayname TEXT,
    refreshrate TEXT,
    calendarorder INTEGER,
    calendarcolor TEXT,
    striptodos INTEGER,
    stripalarms INTEGER,
    stripattachments INTEGER,
    lastmodified INTEGER,
    UNIQUE(principaluri, uri)
);

CREATE TABLE schedulingobjects (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    principaluri TEXT NOT NULL,
    calendardata BLOB,
    uri TEXT NOT NULL,
    lastmodified INTEGER,
    etag TEXT NOT NULL,
    size INTEGER NOT NULL,
    UNIQUE(principaluri, uri)
);

CREATE TABLE locks (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    owner TEXT,
    timeout INTEGER,
    created INTEGER,
    token TEXT,
    scope INTEGER,
    depth INTEGER,
    uri TEXT
);

CREATE INDEX idx_locks_uri ON locks (uri);

CREATE TABLE propertystorage (
    id INTEGER PRIMARY KEY ASC NOT NULL,
    path TEXT NOT NULL,
    name TEXT NOT NULL,
    valuetype INTEGER NOT NULL,
    value TEXT
);

CREATE UNIQUE INDEX path_property ON propertystorage (path, name);
SQL);
    }

    private function upMysql(): void
    {
        // Sabre 4.x no longer ships examples/sql in the Composer package.
        DB::unprepared(<<<'SQL'
CREATE TABLE principals (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    email VARBINARY(80),
    displayname VARCHAR(80),
    UNIQUE(uri)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE groupmembers (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principal_id INTEGER UNSIGNED NOT NULL,
    member_id INTEGER UNSIGNED NOT NULL,
    UNIQUE(principal_id, member_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE addressbooks (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principaluri VARBINARY(255),
    displayname VARCHAR(255),
    uri VARBINARY(200),
    description TEXT,
    synctoken INT(11) UNSIGNED NOT NULL DEFAULT '1',
    UNIQUE(principaluri(100), uri(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE cards (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    addressbookid INT(11) UNSIGNED NOT NULL,
    carddata MEDIUMBLOB,
    uri VARBINARY(200),
    lastmodified INT(11) UNSIGNED,
    etag VARBINARY(32),
    size INT(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE addressbookchanges (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    synctoken INT(11) UNSIGNED NOT NULL,
    addressbookid INT(11) UNSIGNED NOT NULL,
    operation TINYINT(1) NOT NULL,
    INDEX addressbookid_synctoken (addressbookid, synctoken)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE calendars (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    synctoken INTEGER UNSIGNED NOT NULL DEFAULT '1',
    components VARBINARY(21)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE calendarinstances (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    calendarid INTEGER UNSIGNED NOT NULL,
    principaluri VARBINARY(100),
    access TINYINT(1) NOT NULL DEFAULT '1',
    displayname VARCHAR(100),
    uri VARBINARY(200),
    description TEXT,
    calendarorder INT(11) UNSIGNED NOT NULL DEFAULT '0',
    calendarcolor VARBINARY(10),
    timezone TEXT,
    transparent TINYINT(1) NOT NULL DEFAULT '0',
    share_href VARBINARY(100),
    share_displayname VARCHAR(100),
    share_invitestatus TINYINT(1) NOT NULL DEFAULT '2',
    UNIQUE(principaluri, uri),
    UNIQUE(calendarid, principaluri),
    UNIQUE(calendarid, share_href)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE calendarobjects (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    calendardata MEDIUMBLOB,
    uri VARBINARY(200),
    calendarid INTEGER UNSIGNED NOT NULL,
    lastmodified INT(11) UNSIGNED,
    etag VARBINARY(32),
    size INT(11) UNSIGNED NOT NULL,
    componenttype VARBINARY(8),
    firstoccurence INT(11) UNSIGNED,
    lastoccurence INT(11) UNSIGNED,
    uid VARBINARY(200),
    UNIQUE(calendarid, uri),
    INDEX calendarid_time (calendarid, firstoccurence)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE calendarchanges (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    synctoken INT(11) UNSIGNED NOT NULL,
    calendarid INT(11) UNSIGNED NOT NULL,
    operation TINYINT(1) NOT NULL,
    INDEX calendarid_synctoken (calendarid, synctoken)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE calendarsubscriptions (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    uri VARBINARY(200) NOT NULL,
    principaluri VARBINARY(100) NOT NULL,
    source TEXT,
    displayname VARCHAR(100),
    refreshrate VARCHAR(10),
    calendarorder INT(11) UNSIGNED NOT NULL DEFAULT '0',
    calendarcolor VARBINARY(10),
    striptodos TINYINT(1) NULL,
    stripalarms TINYINT(1) NULL,
    stripattachments TINYINT(1) NULL,
    lastmodified INT(11) UNSIGNED,
    UNIQUE(principaluri, uri)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE schedulingobjects (
    id INT(11) UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    principaluri VARBINARY(255),
    calendardata MEDIUMBLOB,
    uri VARBINARY(200),
    lastmodified INT(11) UNSIGNED,
    etag VARBINARY(32),
    size INT(11) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE locks (
    id INTEGER UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    owner VARCHAR(100),
    timeout INTEGER UNSIGNED,
    created INTEGER,
    token VARBINARY(100),
    scope TINYINT,
    depth TINYINT,
    uri VARBINARY(1000),
    INDEX(token),
    INDEX(uri(100))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE propertystorage (
    id INT UNSIGNED NOT NULL PRIMARY KEY AUTO_INCREMENT,
    path VARBINARY(1024) NOT NULL,
    name VARBINARY(100) NOT NULL,
    valuetype INT UNSIGNED,
    value MEDIUMBLOB
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE UNIQUE INDEX path_property ON propertystorage (path(600), name(100));
SQL);
    }

    private function upPgsql(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE principals (
    id SERIAL NOT NULL,
    uri VARCHAR(200) NOT NULL,
    email VARCHAR(80),
    displayname VARCHAR(80)
);
ALTER TABLE ONLY principals ADD CONSTRAINT principals_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX principals_ukey ON principals USING btree (uri);

CREATE TABLE groupmembers (
    id SERIAL NOT NULL,
    principal_id INTEGER NOT NULL,
    member_id INTEGER NOT NULL
);
ALTER TABLE ONLY groupmembers ADD CONSTRAINT groupmembers_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX groupmembers_ukey ON groupmembers USING btree (principal_id, member_id);

CREATE TABLE addressbooks (
    id SERIAL NOT NULL,
    principaluri VARCHAR(255),
    displayname VARCHAR(255),
    uri VARCHAR(200),
    description TEXT,
    synctoken INTEGER NOT NULL DEFAULT 1
);
ALTER TABLE ONLY addressbooks ADD CONSTRAINT addressbooks_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX addressbooks_ukey ON addressbooks USING btree (principaluri, uri);

CREATE TABLE cards (
    id SERIAL NOT NULL,
    addressbookid INTEGER NOT NULL,
    carddata BYTEA,
    uri VARCHAR(200),
    lastmodified INTEGER,
    etag VARCHAR(32),
    size INTEGER NOT NULL
);
ALTER TABLE ONLY cards ADD CONSTRAINT cards_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX cards_ukey ON cards USING btree (addressbookid, uri);

CREATE TABLE addressbookchanges (
    id SERIAL NOT NULL,
    uri VARCHAR(200) NOT NULL,
    synctoken INTEGER NOT NULL,
    addressbookid INTEGER NOT NULL,
    operation SMALLINT NOT NULL
);
ALTER TABLE ONLY addressbookchanges ADD CONSTRAINT addressbookchanges_pkey PRIMARY KEY (id);
CREATE INDEX addressbookchanges_addressbookid_synctoken_ix ON addressbookchanges USING btree (addressbookid, synctoken);

CREATE TABLE calendars (
    id SERIAL NOT NULL,
    synctoken INTEGER NOT NULL DEFAULT 1,
    components VARCHAR(21)
);
ALTER TABLE ONLY calendars ADD CONSTRAINT calendars_pkey PRIMARY KEY (id);

CREATE TABLE calendarinstances (
    id SERIAL NOT NULL,
    calendarid INTEGER NOT NULL,
    principaluri VARCHAR(100),
    access SMALLINT NOT NULL DEFAULT 1,
    displayname VARCHAR(100),
    uri VARCHAR(200),
    description TEXT,
    calendarorder INTEGER NOT NULL DEFAULT 0,
    calendarcolor VARCHAR(10),
    timezone TEXT,
    transparent SMALLINT NOT NULL DEFAULT 0,
    share_href VARCHAR(100),
    share_displayname VARCHAR(100),
    share_invitestatus SMALLINT NOT NULL DEFAULT 2
);
ALTER TABLE ONLY calendarinstances ADD CONSTRAINT calendarinstances_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX calendarinstances_principaluri_uri ON calendarinstances USING btree (principaluri, uri);
CREATE UNIQUE INDEX calendarinstances_principaluri_calendarid ON calendarinstances USING btree (principaluri, calendarid);
CREATE UNIQUE INDEX calendarinstances_principaluri_share_href ON calendarinstances USING btree (principaluri, share_href);

CREATE TABLE calendarobjects (
    id SERIAL NOT NULL,
    calendardata BYTEA,
    uri VARCHAR(200),
    calendarid INTEGER NOT NULL,
    lastmodified INTEGER,
    etag VARCHAR(32),
    size INTEGER NOT NULL,
    componenttype VARCHAR(8),
    firstoccurence INTEGER,
    lastoccurence INTEGER,
    uid VARCHAR(200)
);
ALTER TABLE ONLY calendarobjects ADD CONSTRAINT calendarobjects_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX calendarobjects_ukey ON calendarobjects USING btree (calendarid, uri);

CREATE TABLE calendarchanges (
    id SERIAL NOT NULL,
    uri VARCHAR(200) NOT NULL,
    synctoken INTEGER NOT NULL,
    calendarid INTEGER NOT NULL,
    operation SMALLINT NOT NULL DEFAULT 0
);
ALTER TABLE ONLY calendarchanges ADD CONSTRAINT calendarchanges_pkey PRIMARY KEY (id);
CREATE INDEX calendarchanges_calendarid_synctoken_ix ON calendarchanges USING btree (calendarid, synctoken);

CREATE TABLE calendarsubscriptions (
    id SERIAL NOT NULL,
    uri VARCHAR(200) NOT NULL,
    principaluri VARCHAR(100) NOT NULL,
    source TEXT,
    displayname VARCHAR(100),
    refreshrate VARCHAR(10),
    calendarorder INTEGER NOT NULL DEFAULT 0,
    calendarcolor VARCHAR(10),
    striptodos SMALLINT NULL,
    stripalarms SMALLINT NULL,
    stripattachments SMALLINT NULL,
    lastmodified INTEGER
);
ALTER TABLE ONLY calendarsubscriptions ADD CONSTRAINT calendarsubscriptions_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX calendarsubscriptions_ukey ON calendarsubscriptions USING btree (principaluri, uri);

CREATE TABLE schedulingobjects (
    id SERIAL NOT NULL,
    principaluri VARCHAR(255),
    calendardata BYTEA,
    uri VARCHAR(200),
    lastmodified INTEGER,
    etag VARCHAR(32),
    size INTEGER NOT NULL
);
ALTER TABLE ONLY schedulingobjects ADD CONSTRAINT schedulingobjects_pkey PRIMARY KEY (id);

CREATE TABLE locks (
    id SERIAL NOT NULL,
    owner VARCHAR(100),
    timeout INTEGER,
    created INTEGER,
    token VARCHAR(100),
    scope SMALLINT,
    depth SMALLINT,
    uri TEXT
);
ALTER TABLE ONLY locks ADD CONSTRAINT locks_pkey PRIMARY KEY (id);
CREATE INDEX locks_token_ix ON locks USING btree (token);
CREATE INDEX locks_uri_ix ON locks USING btree (uri);

CREATE TABLE propertystorage (
    id SERIAL NOT NULL,
    path VARCHAR(1024) NOT NULL,
    name VARCHAR(100) NOT NULL,
    valuetype INT,
    value BYTEA
);
ALTER TABLE ONLY propertystorage ADD CONSTRAINT propertystorage_pkey PRIMARY KEY (id);
CREATE UNIQUE INDEX propertystorage_ukey ON propertystorage (path, name);
SQL);
    }
};
