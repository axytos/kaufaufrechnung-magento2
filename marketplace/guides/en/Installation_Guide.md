---
author: axytos GmbH
title: "Installation Guide"
subtitle: "axytos BNPL, Magento2"
header-right: "axytos BNPL, Magento2"
lang: "de"
titlepage: true
titlepage-rule-height: 2
toc-own-page: true
linkcolor: blue
---

# Installation Guide

The plugin provides the payment method __purchase on account__ for shopping in your Magento shop.

Purchases made with this payment method may be accepted by axytos up to receivables management.

All relevant changes to orders with this payment method are automatically sent to axytos.

Adjustments beyond the installation, e.g. of invoice and email templates, are not necessary.

For more information, see [https://www.axytos.com/](https://www.axytos.com/).


## Requirements

1. Contractual relationship with [https://www.axytos.com/](https://www.axytos.com/).

2. Connection data to connect the plugin to [https://portal.axytos.com/](https://portal.axytos.com/) zu verbinden.

In order to be able to use this plugin, you first need a contractual relationship with [https://www.axytos.com/](https://www.axytos.com/).

During onboarding you will receive the necessary connection data to connect the plugin to.


## Plugin installation

### Via Marketplace

1. Buy and add the plugin ["axytos Kauf auf Rechnung"](https://marketplace.magento.com/catalogsearch/result/?q=axytos%20Kauf%20auf%20Rechnung) in Magento Marketplace for free.

2. Follow the instructions in the Marketplace.

### Via Composer

Install the [Composer](https://getcomposer.org/).

Run the following commands in your console in the root of your Magento distribution.

```
composer require axytos/kaufaufrechnung-magento2
php bin/magento module:enable Axytos_KaufAufRechnung
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento cache:clean
```

The plugin is now installed and can be configured and activated.

In order to be able to use the plugin, you need valid connection data for [https://portal.axytos.com/](https://portal.axytos.com/) (see requirements).


## Payment method configuration in Magento

1. Switch to the administration of your Magento distribution. The axytos purchase on account payment method can be found under STORES > Configuration > SALES > Payment Methods > OTHER PAYMENT METHODS.

2. Change __Enabled__ to "Yes".

3. Enter __API Host__. Either [https://api.axytos.com/](https://api.axytos.com/) or [https://api-sandbox.axytos.com/](https://api-sandbox.axytos .com/), the correct values will be communicated to you by axytos during onboarding (see requirements)

4. Enter __API Key__. You will be informed of the correct value during the onboarding of axytos (see requirements).

5. Enter __Client Secret__. You will also be informed of the correct value during onboarding (see requirements).

6. Run __Save Config__.

For configuration, you must save valid connection data to [https://portal.axytos.com/](https://portal.axytos.com/) (see requirements), i.e. __API Host__, __API Key__ and __Client Secret__ for the payment method.

## Payment method can't be selected for purchases?

Check the following points:

1. The plugin __axytos purchase on account__ is installed.

2. The payment method __axytos purchase on account__ is activated.

3. The payment method __axytos purchase on account__ is configured with correct connection data (__API Host__ & __API Key__).

Incorrect connection data means that the plugin cannot be selected for purchases.