# Securely manage Laravel .env files

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stechstudio/laravel-env-security.svg?style=flat-square)](https://packagist.org/packages/stechstudio/laravel-env-security)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Quality Score](https://img.shields.io/scrutinizer/g/stechstudio/laravel-env-security.svg?style=flat-square)](https://scrutinizer-ci.com/g/stechstudio/laravel-env-security)
[![Total Downloads](https://img.shields.io/packagist/dt/stechstudio/laravel-env-security.svg?style=flat-square)](https://packagist.org/packages/stechstudio/laravel-env-security)

This package helps you manage .env files for different deployment environments. Each .env file is securely encrypted and kept in your app's version control. When you deploy your app the appropriate environment-specific .env file is decrypted and moved into place.

This was partly inspired by [Marcel's excellent credentials package](https://github.com/beyondcode/laravel-credentials). If you want to manage your credentials in a json file, with Laravel handling the encryption/decryption, and only have one deployment environment, that package may be a better fit for your needs.

Our package is different in the following ways:

1) We wanted to work with .env files directly and have the decrypted .env file end up where Laravel already expects it. The app makes use of the .env variables just like it normally would.
2) We need to manage .env files for multiple environments (like qa, uat, production). This package allows you to manage any number of environment-specific .env files.
3) We wanted to leverage services like AWS Key Management Service to handle encryption/decryption, with the option to add other encryption drivers (like ansible) or secrets management services (like AWS Secrets Manager) in the future.

> ### Note  
> Laravel v9.32.0 introduced new `env:encrypt` and `env:decrypt` commands, which conflicted with the commands in this package. We have moved our commands in v2 to `env:store` and `env:fetch`.

## Installation and Setup
### Prerequisites
If you intend to enable compression you must have the [Zlib Compression Extension](https://www.php.net/manual/en/book.zlib.php) installed and enabled.

### Install the package

`composer require stechstudio/laravel-env-security`

### Add the composer hook

In your composer.json file add `php artisan env:fetch` as a post-install hook. You likely already have a `post-install-cmd` section, add the new command so it looks like this:

```
"scripts": {
        "post-install-cmd": [
            ...
            "php artisan env:fetch"
        ]
```

### Generate configuration (optional)

Default configuration is based on environment variables (e.g. driver, store env, destination file, aws, gcp). If you need to customize it you can publish the config by running:

`php artisan vendor:publish --provider="STS\EnvSecurity\EnvSecurityServiceProvider" --tag="config"`

### Setup service provider (older versions of Laravel)

If you are using a version of Laravel earlier than 5.5, you will need to manually add the service provider to your `config/app.php` file:

```php
'providers' => [
    ...
    STS\EnvSecurity\EnvSecurityServiceProvider::class,
]
```
### Configuration Settings
| Configuration Key                          | Environment Variable                             | Default        | Description                                                 |
|--------------------------------------------|--------------------------------------------------|----------------|-------------------------------------------------------------|
| `env-security.default`                     | **ENV_DRIVER**                                   | `kms`          | The default driver.                                         |
| `env-security.editor`                      | **EDITOR**                                       | `vi`           | Preferred text editor.                                      |
| `env-security.store`                       | **ENV_STORAGE_PATH**                             | `env`          | The directory where should we keep the encrypted .env files |
| `env-security.destination`                 | **ENV_DESTINATION_FILE**                         | `.env`         | This is where we will put the decrypted .env file           |
| `env-security.enable_compression`          | **ENV_COMPRESSION**                              | `false`        | Should data be compressed prior to encrypting it?           |
| `env-security.divers.kms.key_id`           | **AWS_KMS_KEY**                                  | `null`         | An AWS Key ID or Alias                                      |
| `env-security.drivers.kms.region`          | **AWS_KMS_REGION**                               | `null`         | An AWS Region the key is in.                                |
| `env-security.drivers.google_kms.project`  | **GOOGLE_KMS_PROJECT**, **GOOGLE_CLOUD_PROJECT** | `null`, `null` | A Google CLoud Project Identifier                           |
| `env-security.drivers.google_kms.location` | **GOOGLE_KMS_LOCATION**                          | `global`       | A Google Cloud KMS Location                                 |
| `env-security.drivers.google_kms.key_ring` | **GOOGLE_KMS_KEY_RING**                          | `null`         | A Google Cloud KMS Key Ring                                 |
| `env-security.drivers.google_kms.key_id`   | **GOOGLE_KMS_KEY**                               | `null`         | A Google CLoud KMS Key                                      |

## Environment resolutionss
In order for this package to decrypt the correct .env file when you deploy, you need to tell it how to figure out the environment.

By default it will look for a `APP_ENV` environment variable. However you can provide your own custom resolver with a callback function. Put this in your `AppServiceProvider`'s `boot` method:

```php
\EnvSecurity::resolveEnvironmentUsing(function() {
    // return the environment name
});
```

This way you can resolve out the environment based on hostname, an EC2 instance tag, etc. This will then decrypt the correct .env file based on the environment name you return.

## Key name resolution

Normally we expect your key name to be specified in the .env file. However you may want to specify _different_ keys depending on the environment. If you have, say, different restricted AWS IAM credentials setup for your environments, this would allow you to ensure each .env file can only be decrypted in the appropriate environment.

Of course your own local IAM credentials would still need full access to all keys, so that you can edit each .env file locally.

To resolve the key name, provide a callback function like this:

```php
\EnvSecurity::resolveKeyUsing(function($environment) {
    return "myapp-$environment";
});
```

Notice that your resolver will receive the already-resolved environment name, so you can use this to help figure out which key name to return. 

## Drivers

### AWS Key Management Service

AWS KMS is a managed service that makes it easy for you to create and control the encryption keys used to encrypt your data, and uses FIPS 140-2 validated hardware security modules to protect the security of your keys.

To use this driver set `ENV_DRIVER=kms` in your .env file.

In the [AWS Console](https://console.aws.amazon.com/iam/home?#/encryptionKeys) create your encryption key. Make sure your AWS IAM user has `kms:Encrypt` and `kms:Decrypt` permissions on this key.

Copy the Key ID and store it as `AWS_KMS_KEY` in your local .env file. As you setup environment-specific .env files, make sure to include this `AWS_KMS_KEY` in each .env file.

### Google Cloud Key Management Service

Google KMS securely manages encryption keys and secrets on Google Cloud Platform. The Google KMS integration with Google HSM makes it simple to create a key protected by a FIPS 140-2 Level 3 device.

To use this driver set `ENV_DRIVER=google_kms` in your .env file.

In the [Google Cloud Console](https://console.cloud.google.com/security/kms) create your key ring and key. Make sure your Google IAM user has the `Cloud KMS CryptoKey Encrypter/Decrypter` role for this key.

Copy the Project, Key Ring and Key storing them as `GOOGLE_KMS_PROJECT`, `GOOGLE_KMS_KEY_RING` and `GOOGLE_KMS_KEY` in your local .env file. As you setup environment-specific .env files, make sure to include these keys in each .env file.

## Usage

#### Create/edit a .env file
Run `php artisan env:edit [name]` where `[name]` is the environment you wish to create or edit. This will open the file in `vi` for you to edit. Modify something
in the file, save, and quit.

_Use the `EDITOR` environment variable to set your preferred editor._

#### Decrypt your .env
Now you can run `php artisan env:fetch [name]` which will decrypt the ciphertext file you edited, and write the
plaintext to your `.env`, replacing anything that was in it. Now if you look at your `.env` you should see your edit.

If no environment `[name]` is provided, the environment will be determined by your own custom resolution callback or the `APP_ENV` environment variable.

#### Encrypt and save your current .env
Sometimes you may want to take your current .env file and encrypt exactly as-is. 

Run `php artisan env:store [name]` to do this, where `name` is the name of the encrypted environment file you wish to create. If you don't provide a name, your current `APP_ENV` environment name will be used.

## First deploy

As you're reading through this, you're probably wondering how that *first initial* deploy is going to work. In order for this package to decrypt your .env config where all your sensitive credentials are stored, it needs account credentials with permission to your KMS key.

Yep, it's [turtles all the way down](https://en.wikipedia.org/wiki/Turtles_all_the_way_down).

There are a number of ways to handle this, all dependent on the environment and deployment process.

1. If you are using AWS EC2, you can [assign IAM roles](https://docs.aws.amazon.com/IAM/latest/UserGuide/id_roles_use_switch-role-ec2.html#roles-usingrole-ec2instance-roles) to grant the instance access to your KMS key.
2. For AWS, you can always put a `~/.aws/credentials` file on the server to provide necessary AWS permissions, regardless of your host.
3. For GCP your project ID and credentials are [discovered automatically](https://github.com/googleapis/google-cloud-php/blob/master/AUTHENTICATION.md#google-cloud-platform-environments). 
3. Many deployment services like [Laravel Forge](https://forge.laravel.com/) or [Laravel Envoyer](https://envoyer.io/) provide ways to specify environment variables which you can use to provide credentials.
4. And of course, you can always just ssh in manually to a fresh new server and put the necessary environment variables in a temporary .env file as well, which will get overwritten on the first deploy.
