ARG drupalversion='10.0.x-dev'
FROM tripalproject/tripaldocker:drupal${drupalversion}-php8.1-pgsql13-noChado

ARG chadoschema='testchado'
COPY . /var/www/drupal/web/modules/contrib/TripalCultivate-Genetics

WORKDIR /var/www/drupal/web/modules/contrib/TripalCultivate-Genetics

RUN service postgresql restart \
  && drush trp-install-chado --schema-name=${chadoschema} \
  && drush trp-prep-chado --schema-name=${chadoschema} \
  && drush tripal:trp-import-types --username=drupaladmin --collection_id=general_chado \
  && drush tripal:trp-import-types --username=drupaladmin --collection_id=germplasm_chado \
  && drush en trpcultivate_genetics trpcultivate_genotypes trpcultivate_genomatrix trpcultivate_qtl trpcultivate_vcf --yes \
  && drush cr
