# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-03-31

### Added

- `Plugin` class for managing WordPress plugin lifecycle (boot, activation, deactivation).
- `Plugin_Builder` for fluent plugin configuration (paths, properties, providers).
- `Provider_Factory` for creating service provider instances from class names.
- `Provider_Registry` for registering, booting, and managing service providers.
- `Service_Provider` abstract base class with `register()` and `boot()` hooks.
- `Requirements_Validator` for validating required files before plugin initialization.
- Contract interfaces: `Plugin`, `Plugin_Activation_Aware`, `Plugin_Deactivation_Aware`, `Service_Provider`.
- Exception classes: `Failed_Initialization_Exception`, `Invalid_Provider_Exception`.
- Brain Monkey integration for testing WordPress functions without WordPress loaded.
- GitHub Actions workflows: Lint, Tests, CodeQL Analysis, Workflow Lint, Stale Monitor, Dependency Review, PR Labeling Automation, Enforce PR Labels, Release, and Packagist.
- Dependabot configuration for GitHub Actions, Composer, and npm dependency updates.
- Husky pre-commit hook with lint-staged for PHPCS on staged PHP files.

[Unreleased]: https://github.com/shervElmi/wp-plugin-foundation/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/shervElmi/wp-plugin-foundation/releases/tag/v1.0.0
