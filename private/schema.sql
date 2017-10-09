create table users(id integer primary key autoincrement, username text not null unique, password text not null, admin integer default 0, disable integer default 0);
create table stages(id integer primary key, key text not null unique, name text not null, qtext text, genreid integer not null, flag text not null, modeid integer default 1);
create table stagemode(id integer primary key autoincrement, name text not null, handler text not null);
create table genres(id integer primary key, name text not null);
create table urlsubmissions(userid integer, stageid integer, url text not null, t timestamp default (datetime('now', 'localtime')), primary key(userid, stageid, t));
create table flagsubmissions(userid integer, stageid integer, flag text not null, t timestamp default (datetime('now', 'localtime')), pf integer default 0, primary key(userid, stageid, t));

insert into users(username, password, admin) values('admin', '$2a$10$3jYNCdDRoR8LwHbNXH8HJeO5VyyIaSBHDRZAOIaDOgjT4SL4o//Fa', 1);

insert into genres(name) values('Tutorial');
insert into genres(name) values('XSS');

insert into stagemode(name, handler) values('view', 'BrowserForView');
insert into stagemode(name, handler) values('click', 'BrowserForClick');
