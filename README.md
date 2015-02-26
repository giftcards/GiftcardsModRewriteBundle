GiftcardsFixedWidthBundle [![Build Status](https://travis-ci.org/giftcards/GiftcardsModRewriteBundle.svg?branch=master)](https://travis-ci.org/giftcards/GiftcardsModRewriteBundle)
=========================

Bundle that integrates the [fixed width library](https://github.com/giftcards/ModRewrite) into symfony

Config
------

### Default config ###

```yml
# Default configuration for extension with alias: "giftcards_mod_rewrite"
giftcards_mod_rewrite:
    rewrite_listener:
        enabled:              true
        handle_redirects:     true
        files:                []
```

the rewrite listener will take requests and set the result of the rewriter to the request 
attribute `mod_rewrite_result` if there is a match. to retrieve it call 
`$request->atributes->get('mod_rewrite_result')`.

the files config is a list fo files to load mod rewrite directives from.
handle_redirects tells the listener if it hsould just set a redirect directly on
the `GetResponseEvent` and cause a redirect immediately if the result says to redirect.
if you dont want the listener then set enabled to false.