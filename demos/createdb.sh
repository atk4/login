#!/bin/bash
echo 'create table if not exists user(id integer primary key autoincrement, name varchar, email varchar, password varchar, is_admin bool);' | sqlite3 db.sqlite3
