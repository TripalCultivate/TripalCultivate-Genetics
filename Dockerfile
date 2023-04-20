FROM tripalproject/tripaldocker:latest

COPY . /var/www/drupal9/web/modules/contrib/TripalCultivate-Genetics

WORKDIR /var/www/drupal9/web/modules/contrib/TripalCultivate-Genetics

RUN service postgresql restart \
  && drush en trpcultivate_genetics trpcultivate_genotypes trpcultivate_genomatrix trpcultivate_qtl trpcultivate_vcf --yes
