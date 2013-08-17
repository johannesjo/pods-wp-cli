`wp pods` and `wp pods-api`
===========

This is a package that implements the `wp pods` and `wp pods-api` commands for [WP-CLI](http://wp-cli.org).

It provides access to add/save/duplicate/delete in the Pods class and add_pod/save_pod/duplicate_pod/reset_pod/delete_pod in the PodsAPI class in the [Pods Framework](http://pods.io).

Importing and Exporting methods are still in development.

### Requirements

* PHP 5.3 or newer
* WordPress 3.5 or newer

### Installation

First, make sure you have the [package index](http://wp-cli.org/package-index/) configured:

```
cd ~/.wp-cli/
php composer.phar config repositories.wp-cli composer http://wp-cli.org/package-index/
```

Then, just install the package:

```
php composer.phar require pods-framework/pods-wp-cli=dev-master
```

### Usage

Just run it:

#### `wp pods`

```bash
$ wp pods add --pod=my_pod --field1=Value --field2="Another Value"
Pod item added
ID: 123
$ wp pods save --pod=my_pod --item=123 --field1=Value2 --field2="Another Value2"
Pod item saved
ID: 123
$ wp pods duplicate --pod=my_pod --item=123
Pod item duplicated
New ID: 124
$ wp pods delete --pod=my_pod --item=123
Pod item deleted
```

See http://pods.io/docs/code/pods/ for more information about the various methods.

#### `wp pods-api`

```bash
$ wp pods-api add-pod --name=apple --type=post_type --label=Apples --singular_label=Apple
Pod added
ID: 125
$ wp pods-api save-pod --name=apple --label="Apple Types" --singular_label="Apple Type"
Pod saved
ID: 125
$ wp pods-api duplicate --name=apple --new_name=apple2
Pod duplicated
New ID: 126
$ wp pods-api reset --name=apple
Pod content reset
$ wp pods-api delete --name=apple
Pod deleted
```

Field operations for `wp pods-api` is still in development.

See http://pods.io/docs/code/pods-api/ for more information about the various methods.