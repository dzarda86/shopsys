# Translations

Translating is a process of extracting locale-specific texts from your application and converting them into target language.

We use standard [Symfony translation](https://symfony.com/doc/current/translation.html) so you can use all standard features.
In this article we describe tools and recommendations for translations.

## Usage

1. Create code that uses translations.
   We don't want to duplicate documentation, so please find more in [Symfony translation documentation](https://symfony.com/doc/current/components/translation/usage.html).

1. Once you have a translation in your code, you have to extract translations by running `php phing dump-translations`.
   This command extracts translations into directory [src/Shopsys/ShopBundle/Resources/translations/](/project-base/src/Shopsys/ShopBundle/Resources/translations/).

1. You'll find new translations in `.po` files and you have to translate these newly extracted translations.
   `.po` files are text files so you can make translations in text editor or you can use specialized software.
   Please read more about `.po` in [documentation](https://docs.transifex.com/formats/gettext).

1. Once you create new translations in `.po` files, the application will use these translation immediately.

## Message ID

Message ID is the string you put into translation function. In case of `{{ 'Cart'|translation }}`, the message ID is `Cart`.

We use the original english form as the ID. So in case of
```twig
{% trans with {'%price%': remainingPriceWithVat|price} %}
    You still have to purchase products for <strong> %price% </strong> for <strong> free </strong> shipping and payment.
{% endtrans %}
```
the message ID is `You still have to purchase products for <strong> %price% </strong> for <strong> free </strong> shipping and payment.`.

We also replace multiple spaces in message ID to a single one. So in case of
```
{% trans %}
    Shipping and payment
    <strong>for free!</strong>
{% endtrans %}
```
the message ID is `Shipping and payment <strong>for free!</strong>`.

Never use variables in messages. Extractor is not able to guess what is in the variable. Use placeholders instead.
```diff
$translator->trans(
-    'Thanks to ' . $name
+    'Thanks to %name%',
+    ['%name%' => $name]
);
```

This results in message ID `Thanks to %name%` that can be translated even with different word order, for example `%name%, danke!`.

From time to time we use a speciality for message ID, for example `order [noun]`, `order [verb]` that are both translated as `order`.
We do this because in czech, the noun is translated as `objednávka` and the verb is translated as `objednat`.

## Extracted messages

Messages are extracted from following places.

### PHP

```php
$translator->trans('Hello')
```



* twig ...
* js ...

## change translation

* change in localizated - change .po
* change in original

## transHtml, transchoiceHtml

* only in twig
* like `|trans|raw`
* escapes parameters - prevent XSS
* don't escape translation - possible XSS

## phing dump-translations

* maybe only a link

## PO files

* where are they
* version them
