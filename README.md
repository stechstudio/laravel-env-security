# Securely manage Laravel .env files
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
Now you can run `php artisan end:decrypt [name]` which will decrypt the ciphertext file you edited, and write the
plaintext to your `.env`, replacing anything that was in it. Now if you look at your `.env` you should see your edit.

If no environment `[name]` is provided, the environment will be determined by your own custom resolution callback or the `APP_ENV` environment variable.