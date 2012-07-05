<?php

include("../../app/config.php");


if ( @$config['upgrade'] !== @$_REQUEST['upgrade'] ) die('wrong upgrade key');


if ( ! is_dir("${path}app") ) mkdir("${path}app");

if ( ! is_dir("${path}app/controllers") ) mkdir("${path}app/controllers");

if ( ! is_dir("${path}app/controllers/public") ) mkdir("${path}app/controllers/public");

if ( ! is_dir("${path}app/models") ) mkdir("${path}app/models");

if ( ! is_dir("${path}app/views") ) mkdir("${path}app/views");

if ( ! is_dir("${path}app/cache") ) mkdir("${path}app/cache");


if ( ! is_dir("${path}files") ) mkdir("${path}files");

if ( ! is_dir("${path}files/css") ) mkdir("${path}files/css");
if ( ! is_dir("${path}files/js") ) mkdir("${path}files/js");
if ( ! is_dir("${path}files/fonts") ) mkdir("${path}files/fonts");
if ( ! is_dir("${path}files/uploads") ) mkdir("${path}files/uploads");

?>