# Pipio

[![Build Status](https://travis-ci.org/paultheb/pipio.svg?branch=master)](https://travis-ci.org/paultheb/pipio.svg?branch=master)

Pipio is an evented interface for scalable, service oriented applications in PHP. Pipio comes pre-loaded with support for both logging and AMQP.

This library implements the ![PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) interface.

## Installation

Install the latest version with:

`$ composer require paultheb/pipio`

## About

### Requirements

* Pipio works with PHP 5.6 or above, and has been tested to work with HHVM.

### Submitting Bugs / Feature Requests

Bugs and feature requests are tracked on [GitHub](https://github.com/paultheb/pipio/issues)

### Integrations

* Frameworks / libraries using the [PSR-3](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md) can be used very easily since Pipio implements the interface.
* Custom integrations can be simply added by creating your own Pipio producers / consumers. Relevant producers and consumers have already been implemented for both logging and AMQP.

### Authors

Paul Theberge - <theberge.paul@gmail.com> - <http://twitter.com/_theberge><br/>
Jonathan Rich - <jdrich@gmail.com>
