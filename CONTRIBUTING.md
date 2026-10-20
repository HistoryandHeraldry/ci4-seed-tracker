# Contributing

Contributions are welcome through issues and pull requests.

## Development setup

```shell
composer install
composer quality
```

`composer quality` validates the Composer manifest, runs PHPStan, and executes the PHPUnit suite.

## Pull requests

- Add or update tests for behavioural changes.
- Keep public APIs backward compatible unless the change is intentionally documented as breaking.
- Update `CHANGELOG.md` for user-visible changes.
- Do not edit an applied seed; add a new timestamped seed instead.
