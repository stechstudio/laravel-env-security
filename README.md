# Manage AWS KMS Encrypted .env Files
Manage the editing, encryption, and decryption of .env files for your project
leveraging the AWS Key Management Service (KMS). AWS Key Management Service 
(KMS) is a managed service that makes it easy for you to create and control 
the encryption keys used to encrypt your data, and uses FIPS 140-2 validated 
hardware security modules to protect the security of your keys.

## Prerequisites
- AWS Account
- AWS KMS Key

## Installation
`composer require stechstudio/kmsdotenv`

## Configuration
In your `.env` file.

#### Required
 - **AWS_KMS_KEY**: Alias or URN to the KMS Key to use

#### Optional
This library provides sane defaults so you can hopefully just jump in for most of your use cases.
 - **AWS_KMS_REGION**: The region the key is in. *(Defaults to `us-east-1`)*
 - **KMS_NAME_TEMPLATE**: Template for generating ciphertext file names. *(Default: `'%s.env.enc'`)*
 - **KMS_DIRECTORY**:  Directory for storing ciphertext files. *(Default: `dotenv`)*
 - **KMS_EDITOR**:  Editor to use for editing ciphertext files *(Default: `vi`)*

## Quickstart

#### Encrypt your .env
After adding **AWS_KMS_KEY** to your current `.env` simply run `php artisan dotenv:encrypt-dotfile`. Assuming your
local `.env` defines `APP_ENV=local` then you should find `dotenv/local.env.enc` in your project directory.

#### Edit the encrypted file
Edit that file with `php artisan dotenv:edit local` which will open the file in `vi` for you to edit. Modify something 
in the file, save, and quit.

#### Decrypt your .env from the encrypted file
Now you can run `php artisan dotenv:decrypt-dotfile` which will decrypt the ciphertext file you edited, and write the
plaintext to your `.env`, replacing anything that was in it. Now if you look at your `.env` you should see your edit.

#### Copy one encrypted .env to another.
At this point, perhaps you want to create a new environment file. Simply `php artisan dotenv:copy local qa` and this 
will open the contents of your `dotenv/local.env.enc` in the editor. When you close the editor, the results will be 
saved to `dotenv/qa.env.enc`. Create as many as you like.


