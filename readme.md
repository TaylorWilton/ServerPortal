# Server Portal
A dashboard/portal that runs on my personal server.

## Requirements
- PHP 7+
- [speedtest-cli](https://launchpad.net/ubuntu/xenial/+package/speedtest-cli)

## Instructions

1. Run `composer install`
2. Create a `config.yml` file (see `config.example.yml` for the required fields and data types)
3. Start your webserver

# Notes:
This has only been tested on Ubuntu Server 16.04, specifically my PC. Use at your own peril.
Also, ensure that config.yml cannot be accessed via http (i.e, restrict access using Apache/Nginx/?)


## TODO:
- Ensure caching works if no file exists (i.e, create files that don't exist)
- Ensure proper typechecking and documentation for all functions
- Write a test suite
- Increase modularity
- Add disks to config


