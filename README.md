GiftcardsModRewriteBundle [![Build Status](https://travis-ci.org/giftcards/GiftcardsModRewriteBundle.svg?branch=master)](https://travis-ci.org/giftcards/GiftcardsModRewriteBundle)
=========================

Bundle that integrates the [mod rewrite library](https://github.com/giftcards/ModRewrite) into symfony

Config
------

### Default config ###

```yml
# Default configuration for extension with alias: "giftcards_mod_rewrite"
giftcards_mod_rewrite:
    files:                []
    rewrite_listener:
        enabled:              true
        handle_redirects:     true
        files:                []
    router:
        enabled:              false
        priority:             0
        controller:           'GiftcardsModRewriteBundle:Rewrite:rewrite'

```

## New in version 1.1.0 ##
You can configure the files to read in the `files` key at the root of the config
instead of under rewrite_listener key and it will be used for both the rewrite listener
as well as the router. The `rewrite_listener.files` key has been deprecated.

## The Rewrite Listener ##
the rewrite listener will take requests and set the result of the rewriter to the request 
attribute `mod_rewrite_result` if there is a match. to retrieve it call 
`$request->atributes->get('mod_rewrite_result')`.

the files config is a list fo files to load mod rewrite directives from.
handle_redirects tells the listener if it hsould just set a redirect directly on
the `GetResponseEvent` and cause a redirect immediately if the result says to redirect.
if you dont want the listener then set enabled to false.

## The Router ##
Now you can also enable the mod rewrite router. This is really only useful generally in addition 
to other routers but you can use it however you want like any symfony router after its enabled.
 By default its been setup to be easily integrated with the
  [CMF Routing Bundle](https://github.com/symfony-cmf/routing-bundle)'s chain routing.
   You can configure its priority right in the config. When it is enabled it will route
 mod rewrite matches to the `GiftcardsModRewriteBundle:Rewrite:rewrite` controller action. This action
 will return responses immediately for matches that are configured to do so (ex. R, G, F flags). For others
 that in apache would trigger an internal redirect in apache a sub request is generated and sent back
 through the request system. This should be a pretty good mirror of what would happen in an apache setup.
 
You can enable the router using the `router.enabled` key, configure what controller action is gets routed
 to using the `router.controller` key and configure the routers prirority in the chain router using
  the `router.priority` key.