# Securely manage Laravel .env files

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stechstudio/laravel-env-security.svg?style=flat-square)](https://packagist.org/packages/stechstudio/laravel-env-security)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Quality Score](https://img.shields.io/scrutinizer/g/stechstudio/laravel-env-security.svg?style=flat-square)](https://scrutinizer-ci.com/g/stechstudio/laravel-env-security)
[![Build Status](https://img.shields.io/travis/stechstudio/laravel-env-security/master.svg?style=flat-square)](https://travis-ci.org/stechstudio/laravel-env-security)

This package helps you manage .env files for different deployment environments. Each .env file is securely encrypted and kept in your app's version control. When you deploy your app the appropriate environment-specific .env file is decrypted and moved into place.

This was partly inspired by [Marcel's excellent credentials package](https://github.com/beyondcode/laravel-credentials). If you want to manage your credentials in a json file, with Laravel handling the encryption/decryption, and only have one deployment environment, that package may be a better fit for your needs.

Our package is different in the following ways:

1) We wanted to work with .env files directly and have the decrypted .env file end up where Laravel already expects it. The app makes use of the .env variables just like it normally would.
2) We need to manage .env files for multiple environments (like qa, uat, production). This package allows you to manage any number of environment-specific .env files.
3) We wanted to leverage services like AWS Key Management Service to handle encryption/decryption, with the option to add other encryption drivers (like ansible) or secrets management services (like AWS Secrets Manager) in the future.

## Installation and setup

### Install the package

`composer require stechstudio/laravel-env-security`

### Add the composer hook

In your composer.json file add `php artisan env:decrypt` as a post-install hook. You likely already have a `post-install-cmd` section, add the new command so it looks like this:

```
"scripts": {
        "post-install-cmd": [
            ...
            "php artisan env:decrypt"
        ]
```

### Setup service provider (older versions of Laravel)

If you are using a version of Laravel earlier than 5.5, you will need to manually add the service provider to your `config/app.php` file:

```php
'providers' => [
    ...
    STS\EnvSecurity\EnvSecurityServiceProvider::class,
]
```

## Environment resolution
In order for this package to decrypt the correct .env file when you deploy, you need to tell it how to figure out the environment. 

By default it will look for a `APP_ENV` environment variable. However you can provide your own custom resolver with a callback function. Put this in your `AppServiceProvider`'s `boot` method:
 
```php
\EnvSecurity::resolveEnvironmentUsing(function() {
    // return the environment name
});
``` 

This way you can resolve out the environment based on hostname, an EC2 instance tag, etc. This will then decrypt the correct .env file based on the environment name you return. 

## Drivers

Currently AWS Key Management Service is the only driver. (Others are planned for the future, let us know what you'd like to see!)

### AWS Key Management Service

AWS KMS is a managed service that makes it easy for you to create and control the encryption keys used to encrypt your data, and uses FIPS 140-2 validated hardware security modules to protect the security of your keys.

In the [AWS Console](https://console.aws.amazon.com/iam/home?#/encryptionKeys) create your encryption key. Make sure your AWS IAM user has `kms:Encrypt` and `kms:Decrypt` permissions on this key.
 
Copy the Key ID and store it as `AWS_KMS_KEY` in your local .env file. As you setup environment-specific .env files, make sure to include this `AWS_KMS_KEY` in each .env file. 

## Usage

#### Create/edit a .env file
Run `php artisan env:edit [name]` where `[name]` is the environment you wish to create or edit. This will open the file in `vi` for you to edit. Modify something 
in the file, save, and quit.

#### Decrypt your .env
Now you can run `php artisan env:decrypt [name]` which will decrypt the ciphertext file you edited, and write the
plaintext to your `.env`, replacing anything that was in it. Now if you look at your `.env` you should see your edit.

If no environment `[name]` is provided, the environment will be determined by your own custom resolution callback or the `APP_ENV` environment variable.

#### Encrypt and save your current .env
Sometimes you may want to take your current .env file and encrypt exactly as-is. 

Run `php artisan env:encrypt [name]` to do this, where `name` is the name of the encrypted environment file you wish to create. If you don't provide a name, your current `APP_ENV` environment name will be used.

## First deploy

As you're reading through this, you're probably wondering how that *first initial* deploy is going to work. In order for this package to decrypt your .env config where all your sensitive credentials are stored, it needs AWS account access with permission to your KMS key. 

Yep, it's [turtles all the way down](https://en.wikipedia.org/wiki/Turtles_all_the_way_down).

There are a number of ways to handle this, all dependent on the environment and deployment process.

1. If you are using AWS EC2, you can [assign IAM roles](https://docs.aws.amazon.com/IAM/latest/UserGuide/id_roles_use_switch-role-ec2.html#roles-usingrole-ec2instance-roles) to grant the instance access to your KMS key. 
2. Regardless of your host provider, you can always put a `~/.aws/credentials` file on the server to provide necessary KMS permissions.
3. Many deployment services like [Laravel Forge](https://forge.laravel.com/) or [Laravel Envoyer](https://envoyer.io/) provide ways to specify environment variables which you can use to provide AWS/KMS credentials.
4. And of course, you can always just ssh in manually to a fresh new server and put the necessary AWS/KMS environment variables in a temporary .env file as well, which will get overwritten on the first deploy.   