# Kirby 3 mail-tester.com

![Release](https://flat.badgen.net/packagist/v/bnomei/kirby3-mailtester?color=ae81ff)
![Downloads](https://flat.badgen.net/packagist/dt/bnomei/kirby3-mailtester?color=272822)
[![Build Status](https://flat.badgen.net/travis/bnomei/kirby3-mailtester)](https://travis-ci.com/bnomei/kirby3-mailtester)
[![Coverage Status](https://flat.badgen.net/coveralls/c/github/bnomei/kirby3-mailtester)](https://coveralls.io/github/bnomei/kirby3-mailtester)
[![Maintainability](https://flat.badgen.net/codeclimate/maintainability/bnomei/kirby3-mailtester)](https://codeclimate.com/github/bnomei/kirby3-mailtester)
[![Twitter](https://flat.badgen.net/badge/twitter/bnomei?color=66d9ef)](https://twitter.com/bnomei)

## Install

Using composer:

```bash
composer require getkirby/cli bnomei/kirby3-janitor bnomei/kirby3-mailtester
```

Using git submodules:

```bash
git submodule add https://github.com/getkirby/cli.git site/plugins/cli
git submodule add https://github.com/bnomei/kirby3-janitor.git site/plugins/kirby3-janitor
git submodule add https://github.com/bnomei/kirby3-mailtester.git site/plugins/kirby3-mailtester
```

Using download & copy: download [the latest release of this plugin](https://github.com/bnomei/kirby3-mailtester/releases) and [the latest release of the kirby cli](https://github.com/getkirby/cli/releases) then unzip and copy them to `site/plugins`

## Commercial Usage

> <br>
> <b>Support open source!</b><br><br>
> This plugin is free but if you use it in a commercial project please consider to sponsor me or make a donation.<br>
> If my work helped you to make some cash it seems fair to me that I might get a little reward as well, right?<br><br>
> Be kind. Share a little. Thanks.<br><br>
> &dash; Bruno<br>
> &nbsp;

| M | O | N | E | Y |
|---|----|---|---|---|
| [Github sponsor](https://github.com/sponsors/bnomei) | [Patreon](https://patreon.com/bnomei) | [Buy Me a Coffee](https://buymeacoff.ee/bnomei) | [Paypal dontation](https://www.paypal.me/bnomei/15) | [Hire me](mailto:b@bnomei.com?subject=Kirby) |

## Usage

```php
var_dump(janitor()->command('mailtester:spam'));
```

```yml
  mailtester_spam:
    type: janitor
    command: 'mailtester:spam'
    label: Test
```

## Dependencies

- [Kirby CLI](https://github.com/getkirby/cli)
- [mail-tester.com](https://www.mail-tester.com)

## Disclaimer

This plugin is provided "as is" with no guarantee. Use it at your own risk and always test it yourself before using it in a production environment. If you find any issues, please [create a new issue](https://github.com/bnomei/kirby3-mailtester/issues/new).

## License

[MIT](https://opensource.org/licenses/MIT)

It is discouraged to use this plugin in any project that promotes racism, sexism, homophobia, animal abuse, violence or any other form of hate speech.

