# Quill

CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Configuration
 * To do


INTRODUCTION
------------

The Quill module is a WYSIWYG rich text editor that uses the [QuillJS](http://quilljs.com/) library.

Quill is a free, open source WYSIWYG editor built for the modern web. With its modular architecture and expressive API you can completely customize it to fulfill your needs.


INSTALLATION
------------

Firstly, enable the module in the usual manner and the install the QuillJS library using Composer by running:

*composer install*

in the module root directory.

For more information about Composer, please visit https://getcomposer.org/doc/00-intro.md


CONFIGURATION
-------------

Enable Quill by logging in as an admin user and visiting *admin/config/content/formats*. Either create a new text format or edit an existing text format and from the *Text editor* drop down, select Quill and then click on *Save configuration*.

When editing a node, for example, select the new or updated text format in the body text area and Quill will be your text editor.


TO DO
-----

* Enable configuration of editor toolbar.
* Allow configuration of Quill modules.
