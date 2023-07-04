ARG drupalversion='10.0.x-dev'
ARG chadoschema='testchado'
FROM tripalproject/tripaldocker:drupal${drupalversion}-php8.1-pgsql13

COPY . /var/www/drupal9/web/modules/contrib/TripalCultivate-Genetics

WORKDIR /var/www/drupal9/web/modules/contrib/TripalCultivate-Genetics

RUN service postgresql restart \
  && drush trp-drop-chado --schema-name='chado' \
  && drush trp-install-chado --schema-name='testchado' \
  && drush en trpcultivate_genetics trpcultivate_genotypes trpcultivate_genomatrix trpcultivate_qtl trpcultivate_vcf --yes
