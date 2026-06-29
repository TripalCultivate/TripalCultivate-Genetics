# Tripal Cultivate: Genetics

**Developed by the University of Saskatchewan, Pulse Crop Bioinformatics team.**

**NOTE: This package will replace the following Tripal v3 modules: [nd_genotypes](https://github.com/UofS-Pulse-Binfo/nd_genotypes), [genotypes_loader](https://github.com/UofS-Pulse-Binfo/genotypes_loader), [tripal_qtl](https://github.com/UofS-Pulse-Binfo/tripal_qtl), [vcf_filter](https://github.com/UofS-Pulse-Binfo/vcf_filter).**

<!-- Summarize the main features of this package in point form below. -->

- Provides import and visualization support for genetic maps, markers, sequence variants and QTL
- Large-scale genotypic datasets are supported with both:

    - the power of a relational database for tight integration with germplasm, phenotypic data and with tools across multiple data types
    - the speed/ease of flat file storage and querying via the Variant Call Format (VCF)

- A Genotype Matrix tool for quick visual querying of genotypic differences between germplasm in smaller regions (e.g. QTL or GWAS peak)
- Management of metadata for VCF files including a form for researchers to filter and download the results in multiple formats.

## Citation

If you use this module in your Tripal site, please use this citation to reference our work any place where you described your resulting Tripal site. For example, if you publish your site in a journal then this citation should be in the reference section and anywhere functionality provided by this module is discussed in the above text should reference it.

> Lacey-Anne Sanderson and Carolyn Caron (2023). TripalCultivate Genetics: Large-scale genetic and genotypic data integration for Tripal. Development Version. University of Saskatchewan, Pulse Crop Research Group, Saskatoon, SK, Canada.

## Install

Using composer, add this package to your Drupal site by using the following command in the root of your Drupal site:

```
composer require tripalcultivate/genetics
```

This will download the most recent release in the modules directory. You can see more information in [the Drupal Docs](https://www.drupal.org/docs/develop/using-composer/manage-dependencies).

Then you can install it using Drush or the Extensions page on your Drupal site.

```
drush en trpcultivate_genetics
```

## Technology Stack

*See specific version compatibility in the automated testing section below.*

- Drupal
- Tripal 4.x
- PostgreSQL
- PHP
- Apache2

### Automated Testing

This package is dedicated to a high standard of automated testing. We use
PHPUnit for testing and CodeClimate to ensure good test coverage and maintainability.
There are more details on [our CodeClimate project page] describing our specific
maintainability issues and test coverage.

![MaintainabilityBadge]
![TestCoverageBadge]

The following compatibility is proven via automated testing workflows.

| PHP\Drupal | 10.6.x              | 11.3.x              | 11.4.x              |
|------------|---------------------|---------------------|---------------------|
| **PHP8.2** | ![Grid82-106-Badge] |                     |                     |
| **PHP8.3** | ![Grid83-106-Badge] | ![Grid83-113-Badge] | ![Grid83-114-Badge] |
| **PHP8.4** | ![Grid84-106-Badge] | ![Grid84-113-Badge] | ![Grid84-114-Badge] |
| **PHP8.5** |                     | ![Grid85-113-Badge] | ![Grid85-114-Badge] |

[our CodeClimate project page]: https://codeclimate.com/github/TripalCultivate/TripalCultivate-Genetics
[MaintainabilityBadge]: https://api.codeclimate.com/v1/badges/fddbd06df5e320f09cd9/maintainability
[TestCoverageBadge]: https://api.codeclimate.com/v1/badges/fddbd06df5e320f09cd9/test_coverage

[Grid82-106-Badge]: https://github.com/trpcultivate_genetics/trpcultivate_genetics/actions/workflows/MAIN-phpunit-php8.2_D10_6x.yml/badge.svg
[Grid83-106-Badge]: https://github.com/trpcultivate_genetics/trpcultivate_genetics/actions/workflows/MAIN-phpunit-php8.3_D10_6x.yml/badge.svg
[Grid83-113-Badge]: https://github.com/trpcultivate_genetics/trpcultivate_genetics/actions/workflows/MAIN-phpunit-php8.3_D11_3x.yml/badge.svg
[Grid83-114-Badge]: https://github.com/trpcultivate_genetics/trpcultivate_genetics/actions/workflows/MAIN-phpunit-php8.3_D11_4x.yml/badge.svg
[Grid84-106-Badge]: https://github.com/trpcultivate_genetics/trpcultivate_genetics/actions/workflows/MAIN-phpunit-php8.4_D10_6x.yml/badge.svg
[Grid84-113-Badge]: https://github.com/trpcultivate_genetics/trpcultivate_genetics/actions/workflows/MAIN-phpunit-php8.4_D11_3x.yml/badge.svg
[Grid84-114-Badge]: https://github.com/trpcultivate_genetics/trpcultivate_genetics/actions/workflows/MAIN-phpunit-php8.4_D11_4x.yml/badge.svg
[Grid85-113-Badge]: https://github.com/trpcultivate_genetics/trpcultivate_genetics/actions/workflows/MAIN-phpunit-php8.5_D11_3x.yml/badge.svg
[Grid85-114-Badge]: https://github.com/trpcultivate_genetics/trpcultivate_genetics/actions/workflows/MAIN-phpunit-php8.5_D11_4x.yml/badge.svg
