FROM tripalproject/tripaldocker:latest
MAINTAINER Lacey-Anne Sanderson <lacey.sanderson@usask.ca>

COPY . /var/www/drupal9/web/modules/TripalCultivate-Genetics

WORKDIR /var/www/drupal9/web/modules/TripalCultivate-Genetics

## RUN service postgresql restart \
##  && drush en trpcultivate_genetics --yes
