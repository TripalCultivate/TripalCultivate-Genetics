ARG drupalversion='11.3.x-dev'
ARG phpversion='8.5'
ARG postgresqlversion='18'
FROM knowpulse/tripalcultivate-base:drupal${drupalversion}-php${phpversion}-pgsql${postgresqlversion}

COPY . /var/www/drupal/web/modules/contrib/TripalCultivate-Genetics
WORKDIR /var/www/drupal/web/modules/contrib/TripalCultivate-Genetics

RUN service postgresql restart \
  && drush en trpcultivate_genetics trpcultivate_genotypes trpcultivate_genomatrix trpcultivate_qtl trpcultivate_vcf --yes \
  && drush tripal:trp-run-jobs --username=drupaladmin \
  && drush cr
