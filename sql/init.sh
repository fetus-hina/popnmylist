#!/bin/bash

rm ../data/data.sqlite
for i in 00_init.sql 10_songs.sql 11_levels.sql 20_mylist_map.sql; do
    echo $i
    cat $i | sqlite3 ../data/data.sqlite
done
