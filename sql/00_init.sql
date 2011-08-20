CREATE TABLE "difficulties" (
    "id"    INTEGER     NOT NULL    PRIMARY KEY,
    "name"  TEXT        NOT NULL
);
INSERT INTO "difficulties" ( "id", "name" ) VALUES ( 1, '5' );
INSERT INTO "difficulties" ( "id", "name" ) VALUES ( 2, 'N' );
INSERT INTO "difficulties" ( "id", "name" ) VALUES ( 3, 'H' );
INSERT INTO "difficulties" ( "id", "name" ) VALUES ( 4, 'EX' );

CREATE TABLE "types" (
    "id"    INTEGER     NOT NULL    PRIMARY KEY,
    "name"  TEXT        NOT NULL
);
INSERT INTO "types" ( "id", "name" ) VALUES ( 1, 'チャレンジ' );
INSERT INTO "types" ( "id", "name" ) VALUES ( 2, '超チャレンジ' );

CREATE TABLE "medals" (
    "id"    INTEGER     NOT NULL    PRIMARY KEY,
    "name"  TEXT        NOT NULL
);
INSERT INTO "medals" ( "id", "name" ) VALUES ( 1, 'NO PLAY' );
INSERT INTO "medals" ( "id", "name" ) VALUES ( 2, 'NO CLEAR' );
INSERT INTO "medals" ( "id", "name" ) VALUES ( 3, 'CLEAR' );
INSERT INTO "medals" ( "id", "name" ) VALUES ( 4, 'NO BAD' );
INSERT INTO "medals" ( "id", "name" ) VALUES ( 5, 'PERFECT' );

CREATE TABLE "songs" (
    "id"        TEXT        NOT NULL    PRIMARY KEY,
    "genre"     TEXT        NOT NULL
);

CREATE TABLE "songs_difficulties" (
    "song_id"       TEXT    NOT NULL    REFERENCES "songs" ( "id" ) ON DELETE RESTRICT,
    "difficulty_id" INTEGER NOT NULL    REFERENCES "difficulties" ( "id" ) ON DELETE RESTRICT,
    "level"         INTEGER NOT NULL,
    PRIMARY KEY ( "song_id", "difficulty_id" )
);

CREATE TABLE "score_medals" (
    "song_id"       TEXT    NOT NULL    REFERENCES "songs" ( "id" ) ON DELETE RESTRICT,
    "difficulty_id" INTEGER NOT NULL    REFERENCES "difficulties" ( "id" ) ON DELETE RESTRICT,
    "medal_id"      INTEGER NOT NULL    REFERENCES "medals" ( "id" ) ON DELETE RESTRICT,
    PRIMARY KEY ( "song_id", "difficulty_id" )
);

CREATE TABLE "score_scores" (
    "song_id"       TEXT    NOT NULL    REFERENCES "songs" ( "id" ) ON DELETE RESTRICT,
    "difficulty_id" INTEGER NOT NULL    REFERENCES "difficulties" ( "id" ) ON DELETE RESTRICT,
    "type_id"       INTEGER NOT NULL    REFERENCES "types" ( "id" ) ON DELETE RESTRICT,
    "score"         INTEGER NOT NULL,
    PRIMARY KEY ( "song_id", "difficulty_id", "type_id" )
);

CREATE TABLE "mylist_map" (
    "song_id"       TEXT    NOT NULL    REFERENCES "songs" ( "id" ) ON DELETE RESTRICT,
    "difficulty_id" INTEGER NOT NULL    REFERENCES "difficulties" ( "id" ) ON DELETE RESTRICT,
    "mylist_id"     TEXT    NOT NULL    UNIQUE,
    PRIMARY KEY ( "song_id", "difficulty_id" )
);
