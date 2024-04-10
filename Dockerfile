ARG drupalversion='10.2.x-dev'
ARG phpversion='8.3'
ARG pgsqlversion='16'
FROM knowpulse/tripalcultivate:baseonly-drupal${drupalversion}-php${phpversion}-pgsql${pgsqlversion}

COPY . /var/www/drupal/web/modules/contrib/TripalCultivate-Genetics
WORKDIR /var/www/drupal/web/modules/contrib/TripalCultivate-Genetics

RUN service postgresql restart \
  && drush en trpcultivate_genetics trpcultivate_genotypes trpcultivate_genomatrix trpcultivate_qtl trpcultivate_vcf --yes \
  && drush cr
