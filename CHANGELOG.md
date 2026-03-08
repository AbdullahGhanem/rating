# Changelog

All notable changes to this project will be documented in this file.

## [2.0.0] - 2026-03-09

### Added
- Laravel 9, 10, 11 & 12 support
- `CanRate` trait for author models (`rate`, `rateUnique`, `hasRated`, `getRating`, `averageGivenRating`, `totalGivenRatings`)
- Scoped ratings via `type` column (rate different aspects separately)
- Review body support via `body` column
- Weighted ratings via `weight` column and `weightedAvgRating()`
- Configurable validation with `min`/`max` bounds and `allow_negative` option
- Events: `RatingCreated`, `RatingUpdated`, `RatingDeleted`
- Query scopes: `withAvgRating`, `withSumRating`, `withCountRatings`, `orderByAvgRating`, `orderBySumRating`, `orderByCountRatings`, `minAvgRating`, `minSumRating`
- `isRatedBy()` and `countRatings()` helpers
- `InvalidRatingException` for validation errors
- Publishable config file (`config/rating.php`)
- Laravel auto-discovery support
- Test suite (48 tests, 82 assertions)
- GitHub Actions CI

### Changed
- Minimum PHP version is now 8.0
- Migration uses anonymous class and `$table->id()` instead of `increments()`
- `createUniqueRating()` now returns a Rating model instead of an array
- `countNegative()` now returns an integer instead of a negated string
- All methods now have proper type hints and return types
- Migration publishing uses `publishesMigrations()` instead of custom artisan command
- `findOrFail()` used instead of `find()` for safer lookups

### Removed
- `rating:migration` artisan command (replaced by `vendor:publish`)
- PHP 7.4 support
- Laravel 5, 6, 7 support
- Unused `$parent` parameter from trait methods

## [1.x] - Previous releases

Legacy versions supporting Laravel 5-8 and PHP 7.4+.
