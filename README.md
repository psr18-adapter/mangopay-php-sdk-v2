#  psr18-adapter/mangopay-php-sdk-v2

## Install

Via [Composer](https://getcomposer.org/doc/00-intro.md)

```bash
composer require psr18-adapter/mangopay-php-sdk-v2
```
## Usage

```php
$api = new \MangoPay\MangoPayApi();
$api->setHttpClient(
    new \Psr18Adapter\Mangopay\MangopayPsr18Client(
        $api, 
        $psr18Client,
        $psrRequestFactory,
        $psrUriFactory, 
        $psrStreamFactory
    )
);
```

### Usage with `teknoo/mango-pay-bundle`
```yaml
services:
    mangopay.sdk.http_client:
      class: Psr18Adapter\Mangopay\MangopayPsr18Client
      arguments:
          $root: '@mangopay.sdk.mango_pay_api.service'
    # This is not technically required. Define only if you are logging all requests in your http client, 
    # so don't need mangopay client's stock logging. We are redefining original service to avoid setLogger call.
    mangopay.sdk.mango_pay_api.service:
      class: '%mangopay.sdk.mango_pay_api.class%'
      calls:
        - [setHttpClient, ['@mangopay.sdk.http_client']] 
```

## Licensing

MIT license. Please see [License File](LICENSE.md) for more information.
